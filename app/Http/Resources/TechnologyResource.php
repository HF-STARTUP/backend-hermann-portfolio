<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS Technology { id, name, categoryId, imageUrl? }.
 *
 * @mixin \App\Models\Technology
 */
class TechnologyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'categoryId' => $this->tech_category_id,
            'imageUrl' => MediaService::absoluteUrl($this->image_url),
        ];
    }
}
