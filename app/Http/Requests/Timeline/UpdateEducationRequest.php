<?php

namespace App\Http\Requests\Timeline;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #48 — PUT /api/v1/admin/education/{education} (spec §6.9).
 */
class UpdateEducationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'school' => ['required', 'string', 'max:150'],
            'degree' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:5000'],
            'startDate' => ['required', 'date', 'date_format:Y-m-d'],
            'endDate' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:startDate'],
            'sortOrder' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
