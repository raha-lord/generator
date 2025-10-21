<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GenerationHistoryController extends Controller
{
    /**
     * Display user's generation history.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Generation::with(['generatable', 'user'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('generatable_type', 'like', '%' . ucfirst($request->type));
        }

        // Search by prompt
        if ($request->filled('search')) {
            $query->where('prompt', 'ilike', '%' . $request->search . '%');
        }

        $generations = $query->paginate(20)->withQueryString();

        return view('history.index', [
            'generations' => $generations,
            'filters' => [
                'status' => $request->status ?? 'all',
                'type' => $request->type ?? 'all',
                'search' => $request->search ?? '',
            ],
        ]);
    }

    /**
     * Display specific generation.
     *
     * @param string $uuid
     * @return View|RedirectResponse
     */
    public function show(string $uuid): View|RedirectResponse
    {
        $generation = Generation::with('generatable')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Verify user owns this generation or it's public
        if ($generation->user_id !== auth()->id() && !$generation->is_public) {
            abort(403, 'Access denied');
        }

        $generatableType = class_basename($generation->generatable_type);

        return view("history.show.{$generatableType}", [
            'generation' => $generation,
        ]);
    }

    /**
     * Delete generation from history.
     *
     * @param string $uuid
     * @return RedirectResponse
     */
    public function destroy(string $uuid): RedirectResponse
    {
        $generation = Generation::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        try {
            DB::transaction(function () use ($generation) {
                // Delete associated files if they exist
                if ($generation->result_path) {
                    $storageService = new \App\Services\StorageService();
                    if ($storageService->exists($generation->result_path)) {
                        $storageService->delete($generation->result_path);
                    }
                }

                // Delete the generation (will cascade to generatable)
                $generation->delete();
            });

            return redirect()->route('history.index')
                ->with('success', 'Generation deleted successfully');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Failed to delete generation: ' . $e->getMessage());
        }
    }

    /**
     * Toggle public visibility of generation.
     *
     * @param string $uuid
     * @return RedirectResponse
     */
    public function togglePublic(string $uuid): RedirectResponse
    {
        $generation = Generation::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        // Only completed and approved generations can be made public
        if ($generation->status !== 'completed') {
            return redirect()->back()
                ->with('error', 'Only completed generations can be made public');
        }

        if ($generation->moderation_status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Only approved generations can be made public');
        }

        $generation->is_public = !$generation->is_public;
        $generation->save();

        $message = $generation->is_public
            ? 'Generation is now public'
            : 'Generation is now private';

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Retry failed generation.
     *
     * @param string $uuid
     * @return RedirectResponse
     */
    public function retry(string $uuid): RedirectResponse
    {
        $generation = Generation::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($generation->status !== 'failed') {
            return redirect()->back()
                ->with('error', 'Only failed generations can be retried');
        }

        $user = auth()->user();
        
        // Check if user has enough credits
        if (!$user->balance->hasEnoughCredits($generation->cost)) {
            return redirect()->back()
                ->with('error', "Insufficient credits. Required: {$generation->cost}");
        }

        // Redirect to appropriate generation form with original prompt
        $generatableType = strtolower(class_basename($generation->generatable_type));
        
        return redirect()->route("{$generatableType}.create")
            ->with('prompt', $generation->prompt)
            ->with('info', 'Retrying previous generation');
    }
}
