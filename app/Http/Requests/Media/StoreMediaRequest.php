<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #40 — POST /api/v1/admin/media (spec §6.6).
 * La taille reelle et le MIME sont verifies dans MediaService (413/415).
 */
class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Taille et MIME vérifiées au contenu par MediaService → 413 / 415
            'file' => ['required', 'file'],
        ];
    }
}
