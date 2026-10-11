<?php

namespace App\Http\Requests\SocialLinks;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Route #46 — PUT /api/v1/admin/social-links : remplacement complet du tableau.
 */
class UpdateSocialLinksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'links' => ['required', 'array', 'min:1', 'max:20'],
            'links.*.label' => ['required', 'string', 'max:100'],
            'links.*.url' => ['required', 'url', 'max:500'],
            'links.*.iconName' => ['required', 'string', 'max:50'],
        ];
    }
}
