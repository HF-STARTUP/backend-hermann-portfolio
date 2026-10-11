<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS MediaItem { id, name, src } — src = URL absolue.
 *
 * @mixin \App\Models\MediaItem
 */
class MediaItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'src' => MediaService::absoluteUrl($this->path),
        ];
    }
}
