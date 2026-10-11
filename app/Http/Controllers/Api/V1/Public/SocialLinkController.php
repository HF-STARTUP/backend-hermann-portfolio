<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\SocialLinkResource;
use App\Models\SocialLink;

/**
 * Route #11 — GET /api/v1/social-links.
 */
class SocialLinkController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(
            SocialLinkResource::collection(SocialLink::query()->orderBy('sort_order')->get())
        );
    }
}
