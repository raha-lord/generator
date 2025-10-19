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
        return view('infographic.create');
    }

    public function store(GenerateInfographicRequest $request): RedirectResponse
    {
        $user = $request->user();

        try {
            $service = AIServiceFactory::make('infographic');
            $cost = $service->getCost();

            if (!$user->balance->hasEnoughCredits($cost)) {
                return redirect()->back()
                    ->with('error', "Insufficient credits. Required: {$cost}, Available: {$user->balance->available_credits}")
                    ->withInput();
            }

            $generation = DB::transaction(function () use ($user, $request, $service, $cost) {
                if (!$this->balanceService->deduct($user, $cost, 'Infographic generation')) {
                    throw new \Exception('Failed to deduct credits');
                }

                $infographic = new Infographic();
                $infographic->image_path = 'pending';
                $infographic->format = 'png';
                $infographic->save();

                $generation = new Generation();
                $generation->user_id = $user->id;
                $generation->generatable_type = Infographic::class;
                $generation->generatable_id = $infographic->id;
                $generation->status = 'processing';
                $generation->cost = $cost;
                $generation->prompt = $request->input('prompt');
                $generation->save();

                $result = $service->generate($request->input('prompt'));

                if (!$result['success']) {
                    $this->balanceService->refund($user, $cost, 'Refund for failed generation', Generation::class, $generation->id);
                    $generation->markAsFailed();
                    throw new \Exception($result['error'] ?? 'Generation failed');
                }

                $generatedContent = $result['data']['content'] ?? 'Generated content';
                $storedFile = $this->storageService->storeInfographic($generatedContent, 'txt');

                $infographic->image_path = $storedFile['path'];
                $infographic->file_size = strlen($generatedContent);
                $infographic->save();

                $generation->result_path = $storedFile['path'];
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
