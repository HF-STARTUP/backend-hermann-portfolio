<?php

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTechCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('techCategory')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('tech_categories', 'name')->ignore($categoryId)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'name.unique' => 'Cette catégorie existe déjà',
        ];
    }
}
