<?php

namespace App\Http\Requests\Projects;

use App\Rules\HostedImageOrMediaPath;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
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
        $projectId = $this->route('project')?->id;

        return [
            'title' => ['sometimes', 'string', 'max:150'],
            'role' => ['sometimes', 'string', 'max:120'],
            'description' => ['sometimes', 'string', 'min:10'],
            'longDescription' => ['sometimes', 'nullable', 'string', 'max:10000'],
            'slug' => ['sometimes', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255', Rule::unique('projects', 'slug')->ignore($projectId)],
            'category' => ['sometimes', 'string', 'size:26', Rule::exists('project_categories', 'id')],
            'imageUrl' => ['sometimes', 'nullable', new HostedImageOrMediaPath],
            'demoUrl' => ['sometimes', 'nullable', 'url', 'max:500'],
            'githubUrl' => ['sometimes', 'nullable', 'url', 'max:500'],
            'techStack' => ['sometimes', 'array', 'min:1', 'max:30'],
            'techStack.*' => ['required', 'string', 'max:100', 'distinct'],
            'featured' => ['sometimes', 'boolean'],
            'startDate' => ['sometimes', 'date', 'date_format:Y-m-d'],
            'endDate' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:startDate'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug invalide : minuscules et tirets uniquement (ex: mon-article)',
            'slug.unique' => 'Ce slug est déjà utilisé',
        ];
    }
}
