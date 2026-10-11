<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS SocialLink { id, label, url, iconName } — nom lucide résolu côté frontend.
 *
 * @mixin \App\Models\SocialLink
 */
class SocialLinkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'url' => $this->url,
            'iconName' => $this->icon_name,
        ];
    }
}
