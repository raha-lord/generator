<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prompt' => [
                'required',
                'string',
                'min:10',
                'max:1000',
            ],
            'width' => [
                'nullable',
                'integer',
                'in:512,768,1024,1536,2048',
            ],
            'height' => [
                'nullable',
                'integer',
                'in:512,768,1024,1536,2048',
            ],
            'model' => [
                'nullable',
                'string',
                'in:flux,flux-realism,turbo',
            ],
            'enhance' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prompt.required' => 'Prompt is required for image generation.',
            'prompt.min' => 'Prompt must be at least :min characters.',
            'prompt.max' => 'Prompt cannot exceed :max characters.',
            'width.in' => 'Width must be one of: 512, 768, 1024, 1536, 2048.',
            'height.in' => 'Height must be one of: 512, 768, 1024, 1536, 2048.',
            'model.in' => 'Invalid model selected. Choose from: flux, flux-realism, turbo.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'prompt' => 'image prompt',
            'width' => 'image width',
            'height' => 'image height',
            'model' => 'AI model',
            'enhance' => 'image enhancement',
        ];
    }
}
