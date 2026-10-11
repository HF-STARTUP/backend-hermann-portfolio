<?php

namespace App\Models;

use App\Enums\CoverType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    /** @use HasFactory<\Database\Factories\BlogPostFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'cover_image_url',
        'cover_type',
        'cover_video_url',
        'cover_youtube_url',
        'tags',
        'published_at',
        'read_time_minutes',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'cover_type' => CoverType::class,
            'tags' => 'array',
            'content' => 'array',
            'published_at' => 'date:Y-m-d',
        ];
    }
}
