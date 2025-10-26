<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateInfographicRequest extends FormRequest
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
            'style' => [
                'nullable',
                'string',
                'in:modern,classic,minimalist,colorful,professional',
            ],
            'format' => [
                'nullable',
                'string',
                'in:png,jpg,svg',
            ],
            'provider_id' => [
                'nullable',
                'integer',
                'in:1,2', // 1=Pollinations, 2=Gemini
            ],
            'slides_count' => [
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'width' => [
                'nullable',
                'integer',
                'min:256',
                'max:4096',
            ],
            'height' => [
                'nullable',
                'integer',
                'min:256',
                'max:4096',
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
            'prompt.required' => 'Prompt is required for infographic generation.',
            'prompt.min' => 'Prompt must be at least :min characters.',
            'prompt.max' => 'Prompt cannot exceed :max characters.',
            'style.in' => 'Invalid style selected. Choose from: modern, classic, minimalist, colorful, professional.',
            'format.in' => 'Invalid format selected. Choose from: png, jpg, svg.',
            'provider_id.in' => 'Invalid AI provider selected.',
            'slides_count.min' => 'Slides count must be at least :min.',
            'slides_count.max' => 'Slides count cannot exceed :max.',
            'width.min' => 'Width must be at least :min pixels.',
            'width.max' => 'Width cannot exceed :max pixels.',
            'height.min' => 'Height must be at least :min pixels.',
            'height.max' => 'Height cannot exceed :max pixels.',
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
            'prompt' => 'infographic prompt',
            'style' => 'visual style',
            'format' => 'output format',
            'provider_id' => 'AI provider',
            'slides_count' => 'number of slides',
            'width' => 'image width',
            'height' => 'image height',
        ];
    }
}
