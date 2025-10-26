<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Generation;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    /**
     * Display file (for viewing in browser).
     *
     * @param string $uuid Generation UUID
     * @return Response|StreamedResponse
     */
    public function show(string $uuid): Response|StreamedResponse
    {
        $generation = Generation::where('uuid', $uuid)
            ->with('generatable')
            ->firstOrFail();

        // Check access rights: owner OR public generation
        if (!$this->canAccess($generation)) {
            abort(403, 'Access denied to this file');
        }

        // Check if file exists
        if (!$generation->generatable || !$generation->generatable->image_path) {
            abort(404, 'File not found');
        }

        $filePath = $generation->generatable->image_path;

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        // Get file contents and mime type
        $file = Storage::disk('public')->get($filePath);
        $mimeType = Storage::disk('public')->mimeType($filePath);

        // Return file with appropriate headers for inline display
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"')
            ->header('Cache-Control', 'public, max-age=31536000'); // Cache for 1 year
    }

    /**
     * Download file.
     *
     * @param string $uuid Generation UUID
     * @return Response|StreamedResponse
     */
    public function download(string $uuid): Response|StreamedResponse
    {
        $generation = Generation::where('uuid', $uuid)
            ->with('generatable')
            ->firstOrFail();

        // Check access rights: owner OR public generation
        if (!$this->canAccess($generation)) {
            abort(403, 'Access denied to this file');
        }

        // Check if file exists
        if (!$generation->generatable || !$generation->generatable->image_path) {
            abort(404, 'File not found');
        }

        $filePath = $generation->generatable->image_path;

        // Check if file exists in storage
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found in storage');
        }

        // Generate filename for download
        $type = class_basename($generation->generatable_type);
        $filename = strtolower($type) . '_' . $generation->uuid . '.' . $generation->generatable->format;

        // Return file as download
        return Storage::disk('public')->download($filePath, $filename);
    }

    /**
     * Check if user can access the file.
     *
     * @param Generation $generation
     * @return bool
     */
    private function canAccess(Generation $generation): bool
    {
        // If generation is public, anyone can access
        if ($generation->is_public && $generation->status === 'completed') {
            return true;
        }

        // If user is authenticated and owns the generation
        if (auth()->check() && $generation->user_id === auth()->id()) {
            return true;
        }

        return false;
    }
}
