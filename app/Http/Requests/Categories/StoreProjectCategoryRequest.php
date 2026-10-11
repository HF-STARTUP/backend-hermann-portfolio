<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:100', Rule::unique('project_categories', 'label')],
        ];
    }

    public function messages(): array
    {
        return [
            'label.required' => 'Le libellé est requis',
            'label.unique' => 'Cette catégorie existe déjà',
        ];
    }
}
