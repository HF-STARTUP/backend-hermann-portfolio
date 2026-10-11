<?php

namespace App\Http\Requests\Contact;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #12 — POST /api/v1/contact (source : contact.schema.ts, spec §6.1).
 */
class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'subject' => ['required', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:10', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Votre nom est requis',
            'subject.required' => 'Le sujet est requis',
            'message.required' => 'Votre message est requis',
            'message.min' => 'Votre message doit contenir au moins 10 caractères',
            'message.max' => 'Votre message ne doit pas dépasser 2000 caractères',
            'email.required' => "L'email est requis",
            'email.email' => "Format d'email invalide",
        ];
    }
}
