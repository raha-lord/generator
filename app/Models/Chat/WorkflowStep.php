<?php

namespace App\Models\Chat;

use App\Models\Pricing\AiProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowStep extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'workflow_steps';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'service_id',
        'order',
        'code',
        'name',
        'model_type',
        'provider_id',
        'requires_confirmation',
        'prompt_template',
        'config',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'requires_confirmation' => 'boolean',
        'config' => 'array',
        'order' => 'integer',
    ];

    /**
     * Get the service that owns this workflow step.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the default AI provider for this step.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    /**
     * Get the messages for this workflow step.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Check if this step requires user confirmation.
     */
    public function requiresConfirmation(): bool
    {
        return $this->requires_confirmation;
    }

    /**
     * Get the prompt template with replaced variables.
     */
    public function getPrompt(array $variables = []): string
    {
        $prompt = $this->prompt_template;

        foreach ($variables as $key => $value) {
            $prompt = str_replace("{{$key}}", $value, $prompt);
        }

        return $prompt;
    }
}
