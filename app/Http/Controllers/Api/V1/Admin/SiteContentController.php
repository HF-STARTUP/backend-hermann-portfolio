<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\SiteContent\UpdateSiteContentRequest;
use App\Services\SiteContentService;

/**
 * Route #45 — PATCH /api/v1/admin/site-content/{section} :
 * fusion du patch (premier niveau), section inconnue → 404.
 */
class SiteContentController extends Controller
{
    use ApiResponse;

    public function update(UpdateSiteContentRequest $request, string $section, SiteContentService $service)
    {
        $merged = $service->patchSection($section, $request->json()->all());

        return $this->success($merged, 'Contenu mis à jour.');
    }
}
