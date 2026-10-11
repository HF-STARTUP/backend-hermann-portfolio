<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\EducationResource;
use App\Http\Resources\ExperienceResource;
use App\Models\Education;
use App\Models\Experience;

/**
 * Routes #9 et #10 — expériences et formations, triées par sort_order asc.
 */
class TimelineController extends Controller
{
    use ApiResponse;

    public function experiences()
    {
        return $this->success(
            ExperienceResource::collection(Experience::query()->orderBy('sort_order')->get())
        );
    }

    public function education()
    {
        return $this->success(
            EducationResource::collection(Education::query()->orderBy('sort_order')->get())
        );
    }
}
