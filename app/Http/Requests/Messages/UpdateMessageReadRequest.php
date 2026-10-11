<?php

namespace App\Http\Requests\Messages;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #43 — PATCH /api/v1/admin/messages/{message}/read.
 */
class UpdateMessageReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'read' => ['required', 'boolean'],
        ];
    }
}
