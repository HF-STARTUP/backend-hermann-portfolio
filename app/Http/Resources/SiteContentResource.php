<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Arbre complet du contenu éditable, structure identique à
 * l'interface TS SiteContent (spec §4.3).
 */
class SiteContentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // $this->resource = arbre produit par SiteContentService::tree()
        return $this->resource;
    }
}
