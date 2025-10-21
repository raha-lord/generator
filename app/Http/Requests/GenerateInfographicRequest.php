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
        ];
    }
}
