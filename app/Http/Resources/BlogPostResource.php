<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS BlogPost — mapping exact de la spec §4.3.
 *
 * @mixin \App\Models\BlogPost
 */
class BlogPostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'coverImageUrl' => $this->cover_image_url
                ? MediaService::absoluteUrl($this->cover_image_url)
                : '',
            'coverType' => $this->cover_type->value,
            'coverVideoUrl' => $this->when($this->cover_video_url, fn () => MediaService::absoluteUrl($this->cover_video_url)),
            'coverYoutubeUrl' => $this->when($this->cover_youtube_url, fn () => $this->cover_youtube_url),
            'tags' => $this->tags,
            'publishedAt' => $this->published_at?->format('Y-m-d'),
            'readTimeMinutes' => $this->read_time_minutes,
            'content' => $this->content,
        ];
    }
}
