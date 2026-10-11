<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteContent extends Model
{
    protected $table = 'site_content';

    protected $primaryKey = 'section';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'section',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
