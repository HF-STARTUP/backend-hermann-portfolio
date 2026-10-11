<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLink extends Model
{
    /** @use HasFactory<\Database\Factories\SocialLinkFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'label',
        'url',
        'icon_name',
        'sort_order',
    ];
}
