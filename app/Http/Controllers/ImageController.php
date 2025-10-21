<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Models\Image;
use App\Services\AI\AIServiceFactory;
use App\Services\BalanceService;
use App\Services\StorageService;
use App\Http\Requests\GenerateImageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ImageController extends Controller
{
    private BalanceService $balanceService;
    private StorageService $storageService;

    public function __construct(BalanceService $balanceService, StorageService $storageService)
    {
        $this->balanceService = $balanceService;
        $this->storageService = $storageService;
    }

    public function create(): View
    {
        return view('image.create');
    }

    public function store(GenerateImageRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $service = AIServiceFactory::make('image');
            $cost = $service->getCost();

            if (!$user->balance->hasEnoughCredits($cost)) {
                return redirect()->back()
                    ->with('error', "Insufficient credits. Required: {$cost}, Available: {$user->balance->available_credits}")
                    ->withInput();
            }

            $generation = DB::transaction(function () use ($user, $request, $service, $cost) {
                // Deduct credits first
                if (!$this->balanceService->deduct($user, $cost, 'Image generation')) {
                    throw new \Exception('Failed to deduct credits');
                }

                // Get options from request
                $width = (int) ($request->input('width', 1024));
                $height = (int) ($request->input('height', 1024));
                $model = $request->input('model', 'flux');
                $enhance = (bool) $request->input('enhance', false);

                // Create image record
                $image = new Image();
                $image->image_path = 'pending';
                $image->format = 'png';
                $image->width = $width;
                $image->height = $height;
                $image->file_size = 0;
                $image->model = $model;
                $image->enhanced = $enhance;
                $image->save();

                // Create generation record
                $generation = new Generation();
                $generation->user_id = $user->id;
                $generation->generatable_type = Image::class;
                $generation->generatable_id = $image->id;
                $generation->status = 'processing';
                $generation->cost = $cost;
                $generation->prompt = $request->input('prompt');
                $generation->save();

                // Generate image with options
                $result = $service->generate($request->input('prompt'), [
                    'width' => $width,
                    'height' => $height,
                    'model' => $model,
                    'enhance' => $enhance,
                ]);

                if (!$result['success']) {
                    $this->balanceService->refund($user, $cost, 'Refund for failed generation', Generation::class, $generation->id);
                    $generation->markAsFailed();
                    throw new \Exception($result['error'] ?? 'Generation failed');
                }

                // Store generated image
                $imageData = $result['data']['image_data'] ?? null;
                $imageFormat = $result['data']['format'] ?? 'png';

                if (empty($imageData)) {
                    throw new \Exception('No image data received');
                }

                // Decode base64 and store image
                $storedFile = $this->storageService->storeImage($imageData, $imageFormat);

                // Update image record
                $image->image_path = $storedFile['path'];
                $image->file_size = $this->storageService->size($storedFile['path']);
                $image->format = $imageFormat;

                // Store seed if generated
                if (!empty($result['data']['metadata']['seed'])) {
                    $image->seed = (string) $result['data']['metadata']['seed'];
                }

                $image->save();

                // Mark generation as completed
                $generation->result_path = $storedFile['path'];
                $generation->markAsCompleted();

                return $generation;
            });

            return redirect()->route('image.show', $generation->uuid)
                ->with('success', 'Image generated successfully!');

        } catch (\Exception $e) {
            Log::error('Image generation error', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to generate: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $uuid): View
    {
        $generation = Generation::where('uuid', $uuid)->with('generatable', 'user')->firstOrFail();

        if ($generation->user_id !== auth()->id() && !$generation->is_public) {
            abort(403);
        }

        return view('image.show', compact('generation'));
    }
}
