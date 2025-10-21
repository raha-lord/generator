<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Image extends Model
{
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
        'model',
        'seed',
        'enhanced',
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
            'enhanced' => 'boolean',
        ];
    }

    /**
     * Get the generation record for this image.
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
        return asset('storage/' . $this->image_path);
    }

    /**
     * Get full URL to the thumbnail.
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path
            ? asset('storage/' . $this->thumbnail_path)
            : null;
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

    /**
     * Get display name for the AI model.
     */
    public function getModelDisplayNameAttribute(): string
    {
        return match ($this->model) {
            'flux' => 'Flux',
            'flux-realism' => 'Flux Realism',
            'turbo' => 'Turbo',
            default => ucfirst($this->model),
        };
    }
}
