<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Generation;
use App\Models\Infographic;
use App\Services\AI\AIServiceFactory;
use App\Services\BalanceService;
use App\Services\StorageService;
use App\Http\Requests\GenerateInfographicRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class InfographicController extends Controller
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
        // Get active AI providers that support image generation
        $providers = \App\Models\Pricing\AiProvider::where('is_active', true)
            ->whereHas('providerPricing', function ($query) {
                $query->where('service_type', 'image')
                      ->where('is_active', true);
            })
            ->get();

        return view('infographic.create', compact('providers'));
    }

    public function store(GenerateInfographicRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            // Get request parameters
            $providerId = $request->input('provider_id', 2); // Default to Gemini
            $slidesCount = $request->input('slides_count', 1);
            $width = $request->input('width', 1024);
            $height = $request->input('height', 1024);
            $style = $request->input('style', 'professional');
            $format = $request->input('format', 'png');

            // Create service with selected provider
            $service = AIServiceFactory::make('infographic', ['provider_id' => $providerId]);

            // Calculate cost: slides_count × cost_per_slide
            $costPerSlide = $service->getCost([
                'width' => $width,
                'height' => $height,
                'style' => $style,
            ]);
            $totalCost = $slidesCount * $costPerSlide;

            if (!$user->balance->hasEnoughCredits($totalCost)) {
                return redirect()->back()
                    ->with('error', "Insufficient credits. Required: {$totalCost}, Available: {$user->balance->available_credits}")
                    ->withInput();
            }

            $generation = DB::transaction(function () use ($user, $request, $service, $totalCost, $providerId, $slidesCount, $width, $height, $style, $format) {
                // Deduct credits first
                if (!$this->balanceService->deduct($user, $totalCost, "Infographic generation ({$slidesCount} slides)")) {
                    throw new \Exception('Failed to deduct credits');
                }

                // Create infographic record
                $infographic = new Infographic();
                $infographic->image_path = 'pending';
                $infographic->format = $format;
                $infographic->width = $width;
                $infographic->height = $height;
                $infographic->file_size = 0;
                $infographic->provider_id = $providerId;
                $infographic->slides_count = $slidesCount;
                $infographic->slides = [];
                $infographic->save();

                // Create generation record
                $generation = new Generation();
                $generation->user_id = $user->id;
                $generation->generatable_type = Infographic::class;
                $generation->generatable_id = $infographic->id;
                $generation->status = 'processing';
                $generation->cost = $totalCost;
                $generation->prompt = $request->input('prompt');
                $generation->save();

                // Generate slides (batch or single)
                if ($slidesCount > 1) {
                    $results = $service->generateBatch($request->input('prompt'), $slidesCount, [
                        'style' => $style,
                        'format' => $format,
                        'width' => $width,
                        'height' => $height,
                    ]);
                } else {
                    $singleResult = $service->generate($request->input('prompt'), [
                        'style' => $style,
                        'format' => $format,
                        'width' => $width,
                        'height' => $height,
                    ]);
                    $results = [$singleResult];
                }

                // Check if any generation failed
                $failedCount = 0;
                foreach ($results as $result) {
                    if (!$result['success']) {
                        $failedCount++;
                    }
                }

                if ($failedCount === count($results)) {
                    // All generations failed
                    $this->balanceService->refund($user, $totalCost, 'Refund for failed generation', Generation::class, $generation->id);
                    $generation->markAsFailed();
                    throw new \Exception('All slide generations failed');
                }

                // Store all generated slides
                $storedSlides = [];
                $totalSize = 0;

                foreach ($results as $index => $result) {
                    if (!$result['success']) {
                        Log::warning("Slide {$index} generation failed", ['error' => $result['error'] ?? 'Unknown']);
                        continue;
                    }

                    $imageData = $result['data']['image_data'] ?? null;
                    if (empty($imageData)) {
                        Log::warning("Slide {$index} has no image data");
                        continue;
                    }

                    // Store slide
                    $storedFile = $this->storageService->storeInfographic($imageData, $format);
                    $storedSlides[] = $storedFile['path'];
                    $totalSize += $this->storageService->size($storedFile['path']);
                }

                if (empty($storedSlides)) {
                    $this->balanceService->refund($user, $totalCost, 'Refund for failed generation', Generation::class, $generation->id);
                    $generation->markAsFailed();
                    throw new \Exception('No slides were generated successfully');
                }

                // Partial refund if some slides failed
                if ($failedCount > 0) {
                    $refundAmount = $failedCount * ($totalCost / $slidesCount);
                    $this->balanceService->refund($user, (int)$refundAmount, 'Partial refund for failed slides', Generation::class, $generation->id);
                }

                // Update infographic record
                $infographic->image_path = $storedSlides[0]; // First slide as main image
                $infographic->slides = $slidesCount > 1 ? array_slice($storedSlides, 1) : []; // Rest in slides array
                $infographic->file_size = $totalSize;
                $infographic->save();

                // Mark generation as completed
                $generation->result_path = $storedSlides[0];
                $generation->markAsCompleted();

                return $generation;
            });

            return redirect()->route('infographic.show', $generation->uuid)
                ->with('success', 'Infographic generated successfully!');

        } catch (\Exception $e) {
            Log::error('Infographic generation error', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to generate: ' . $e->getMessage())->withInput();
        }
    }

    public function show(string $uuid): View
    {
        $generation = Generation::where('uuid', $uuid)->with('generatable', 'user')->firstOrFail();

        if ($generation->user_id !== auth()->id() && !$generation->is_public) {
            abort(403);
        }

        return view('infographic.show', compact('generation'));
    }
}
