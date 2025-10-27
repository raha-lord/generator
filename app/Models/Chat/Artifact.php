<?php

namespace App\Models\Chat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Artifact extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'artifacts';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'chat_id',
        'message_id',
        'type',
        'file_path',
        'file_size',
        'mime_type',
        'metadata',
        'is_public',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($artifact) {
            if (empty($artifact->uuid)) {
                $artifact->uuid = (string) Str::uuid();
            }
            if (empty($artifact->created_at)) {
                $artifact->created_at = now();
            }
        });

        static::deleting(function ($artifact) {
            // Delete the actual file when artifact is deleted
            if (Storage::exists($artifact->file_path)) {
                Storage::delete($artifact->file_path);
            }
        });
    }

    /**
     * Get the chat that owns the artifact.
     */
    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    /**
     * Get the message that owns the artifact.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the full URL to the artifact.
     */
    public function getUrl(): string
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if artifact is an image.
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if artifact is audio.
     */
    public function isAudio(): bool
    {
        return $this->type === 'audio';
    }

    /**
     * Check if artifact is video.
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Check if artifact is a document.
     */
    public function isDocument(): bool
    {
        return $this->type === 'document';
    }

    /**
     * Get human-readable file size.
     */
    public function getHumanFileSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, 2) . ' ' . $units[$i];
    }

    /**
     * Scope a query to only include public artifacts.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include images.
     */
    public function scopeImages($query)
    {
        return $query->where('type', 'image');
    }

    /**
     * Get route key name for route model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
