<?php

namespace App\Http\Requests\Projects;

use App\Rules\HostedImageOrMediaPath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
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
        return [
            'title' => ['required', 'string', 'max:150'],
            'role' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'min:10'],
            'longDescription' => ['nullable', 'string', 'max:10000'],
            'category' => ['required', 'string', 'size:26', Rule::exists('project_categories', 'id')],
            'imageUrl' => ['nullable', new HostedImageOrMediaPath],
            'demoUrl' => ['nullable', 'url', 'max:500'],
            'githubUrl' => ['nullable', 'url', 'max:500'],
            'techStack' => ['required', 'array', 'min:1', 'max:30'],
            'techStack.*' => ['required', 'string', 'max:100', 'distinct'],
            'featured' => ['sometimes', 'boolean'],
            'startDate' => ['required', 'date', 'date_format:Y-m-d'],
            'endDate' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:startDate'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis',
            'role.required' => 'Le rôle est requis',
            'description.required' => 'La description est requise',
            'description.min' => 'La description doit contenir au moins 10 caractères',
            'category.required' => 'La catégorie est requise',
            'demoUrl.url' => "L'URL doit être valide (http/https)",
            'githubUrl.url' => "L'URL doit être valide (http/https)",
            'techStack.required' => 'Sélectionnez au moins une technologie',
            'techStack.min' => 'Sélectionnez au moins une technologie',
        ];
    }
}
