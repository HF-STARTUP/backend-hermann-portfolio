<?php

namespace App\Enums;

enum CoverType: string
{
    case Image = 'image';
    case Video = 'video';
    case Youtube = 'youtube';
}
