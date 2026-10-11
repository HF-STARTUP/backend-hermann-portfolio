<?php

namespace App\Http\Requests\Timeline;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #47 — PUT /api/v1/admin/experiences/{experience} (spec §6.9).
 */
class UpdateExperienceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company' => ['required', 'string', 'max:150'],
            'position' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'location' => ['nullable', 'string', 'max:150'],
            'startDate' => ['required', 'date', 'date_format:Y-m-d'],
            'endDate' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'sortOrder' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
