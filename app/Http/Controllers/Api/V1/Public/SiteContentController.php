<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\SiteContentResource;
use App\Services\SiteContentService;

/**
 * Route #1 — GET /api/v1/site-content : arbre complet du contenu éditable.
 */
class SiteContentController extends Controller
{
    use ApiResponse;

    public function index(SiteContentService $service)
    {
        return $this->success(
            new SiteContentResource($service->tree())
        );
    }
}
