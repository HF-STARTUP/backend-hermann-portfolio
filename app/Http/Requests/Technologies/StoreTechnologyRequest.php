<?php

namespace App\Http\Requests\Technologies;

use App\Rules\HostedImageOrMediaPath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTechnologyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('technologies', 'name')],
            'categoryId' => ['required', 'string', 'exists:tech_categories,id'],
            'imageUrl' => ['nullable', new HostedImageOrMediaPath],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'name.unique' => 'Cette technologie existe déjà',
            'categoryId.required' => 'La catégorie est requise',
        ];
    }
}
