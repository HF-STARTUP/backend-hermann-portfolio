<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Timeline\UpdateExperienceRequest;
use App\Http\Resources\ExperienceResource;
use App\Models\Experience;

/**
 * Route #47 — PUT /api/v1/admin/experiences/{experience}.
 */
class ExperienceController extends Controller
{
    use ApiResponse;

    public function update(UpdateExperienceRequest $request, Experience $experience)
    {
        $validated = $request->validated();

        if (array_key_exists('startDate', $validated)) {
            $validated['start_date'] = $validated['startDate'];
            unset($validated['startDate']);
        }
        if (array_key_exists('endDate', $validated)) {
            $validated['end_date'] = $validated['endDate'];
            unset($validated['endDate']);
        }
        if (array_key_exists('sortOrder', $validated)) {
            $validated['sort_order'] = $validated['sortOrder'];
            unset($validated['sortOrder']);
        }

        $experience->update($validated);

        return $this->success(new ExperienceResource($experience->refresh()));
    }
}
