<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\SocialLinks\UpdateSocialLinksRequest;
use App\Http\Resources\SocialLinkResource;
use App\Models\SocialLink;
use Illuminate\Support\Facades\DB;

/**
 * Route #46 — PUT /api/v1/admin/social-links :
 * remplacement complet du tableau (transaction + nouveaux ULID).
 */
class SocialLinkController extends Controller
{
    use ApiResponse;

    public function update(UpdateSocialLinksRequest $request)
    {
        $links = DB::transaction(function () use ($request) {
            SocialLink::query()->delete();

            $rows = [];
            foreach ($request->validated('links') as $index => $link) {
                $rows[] = [
                    'label' => $link['label'],
                    'url' => $link['url'],
                    'icon_name' => $link['iconName'],
                    'sort_order' => $index,
                ];
            }

            foreach ($rows as $row) {
                SocialLink::create($row);
            }

            return SocialLink::query()->orderBy('sort_order')->get();
        });

        return $this->success(SocialLinkResource::collection($links));
    }
}
