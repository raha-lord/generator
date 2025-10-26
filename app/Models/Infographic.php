<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Infographic extends Model
{
    /**
     * The table associated with the model.
     */
    protected $table = 'generations.infographics';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_path',
        'thumbnail_path',
        'width',
        'height',
        'format',
        'file_size',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'width' => 'integer',
            'height' => 'integer',
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the generation record for this infographic.
     */
    public function generation(): MorphOne
    {
        return $this->morphOne(Generation::class, 'generatable');
    }

    /**
     * Get full URL to the image.
     */
    public function getImageUrlAttribute(): string
    {
        // Get generation UUID through the morphOne relationship
        $generation = $this->generation;

        if (!$generation) {
            return '';
        }

        return route('file.show', $generation->uuid);
    }

    /**
     * Get download URL for the image.
     */
    public function getDownloadUrlAttribute(): string
    {
        $generation = $this->generation;

        if (!$generation) {
            return '';
        }

        return route('file.download', $generation->uuid);
    }

    /**
     * Get full URL to the thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        // For now, thumbnails use the same route as full images
        // In the future, could add separate thumbnail route
        return $this->image_url;
    }

    /**
     * Get file size in human readable format.
     */
    public function getHumanFileSizeAttribute(): string
    {
        if ($this->file_size === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
