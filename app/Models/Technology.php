<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technology extends Model
{
    /** @use HasFactory<\Database\Factories\TechnologyFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'tech_category_id',
        'name',
        'image_url',
    ];

    /** @return BelongsTo<TechCategory, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(TechCategory::class, 'tech_category_id');
    }
}
