<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS Education — camelCase direct.
 *
 * @mixin \App\Models\Education
 */
class EducationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school' => $this->school,
            'degree' => $this->degree,
            'description' => $this->description,
            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),
            'sortOrder' => $this->sort_order,
        ];
    }
}
