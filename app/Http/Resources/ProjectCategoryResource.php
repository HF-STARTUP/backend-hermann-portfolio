<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS ProjectCategory { id, label }.
 *
 * @mixin \App\Models\ProjectCategory
 */
class ProjectCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
        ];
    }
}
