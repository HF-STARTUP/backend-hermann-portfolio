<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaItem extends Model
{
    /** @use HasFactory<\Database\Factories\MediaItemFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
        'disk',
        'path',
        'mime_type',
        'size',
    ];
}
