<?php

namespace App\Http\Requests\Technologies;

use App\Rules\HostedImageOrMediaPath;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTechnologyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $technologyId = $this->route('technology')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:100', Rule::unique('technologies', 'name')->ignore($technologyId)],
            'categoryId' => ['sometimes', 'string', 'exists:tech_categories,id'],
            'imageUrl' => ['sometimes', 'nullable', new HostedImageOrMediaPath],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est requis',
            'name.unique' => 'Cette technologie existe déjà',
        ];
    }
}
