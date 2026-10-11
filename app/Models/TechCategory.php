<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TechCategory extends Model
{
    /** @use HasFactory<\Database\Factories\TechCategoryFactory> */
    use HasFactory, HasUlids;

    protected $fillable = [
        'name',
    ];

    /** @return HasMany<Technology, $this> */
    public function technologies(): HasMany
    {
        return $this->hasMany(Technology::class);
    }
}
