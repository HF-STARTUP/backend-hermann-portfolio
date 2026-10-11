<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS Project — mapping exact de la spec §4.3.
 *
 * @mixin \App\Models\Project
 */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'role' => $this->role,
            'description' => $this->description,
            'longDescription' => $this->long_description,
            'imageUrl' => $this->image_url
                ? MediaService::absoluteUrl($this->image_url)
                : '',
            'techStack' => $this->tech_stack,
            'category' => $this->project_category_id,
            'links' => [
                'demoUrl' => $this->demo_url,
                'githubUrl' => $this->github_url,
            ],
            'featured' => $this->featured,
            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),
        ];
    }
}
