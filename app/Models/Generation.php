<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Generation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'generatable_type',
        'generatable_id',
        'status',
        'cost',
        'prompt',
        'result_path',
        'public_url',
        'is_public',
        'moderation_status',
        'moderation_reason',
        'completed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'completed_at' => 'datetime',
            'cost' => 'integer',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Generation $generation) {
            if (empty($generation->uuid)) {
                $generation->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the user that owns the generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent generatable model (Infographic, etc.).
     */
    public function generatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if generation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if generation is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if generation is processing.
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if generation failed.
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark generation as completed.
     */
    public function markAsCompleted(): bool
    {
        $this->status = 'completed';
        $this->completed_at = now();
        return $this->save();
    }

    /**
     * Mark generation as failed.
     */
    public function markAsFailed(): bool
    {
        $this->status = 'failed';
        return $this->save();
    }

    /**
     * Mark generation as processing.
     */
    public function markAsProcessing(): bool
    {
        $this->status = 'processing';
        return $this->save();
    }

    /**
     * Generate public URL.
     */
    public function generatePublicUrl(): string
    {
        $this->public_url = Str::random(32);
        $this->is_public = true;
        $this->save();

        return $this->public_url;
    }
}
