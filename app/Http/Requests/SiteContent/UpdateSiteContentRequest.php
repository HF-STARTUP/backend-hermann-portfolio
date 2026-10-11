<?php

namespace App\Http\Requests\SiteContent;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #45 — PATCH /api/v1/admin/site-content/{section} :
 * le corps EST le patch (objet libre), cf. exemple spec #45.
 */
class UpdateSiteContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $patch = $this->json()->all();

            if (! is_array($patch) || $patch === []) {
                $validator->errors()->add('data', 'Le corps de la requête doit être un objet JSON non vide.');
            }
        });
    }
}
