<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Timeline\UpdateEducationRequest;
use App\Http\Resources\EducationResource;
use App\Models\Education;

/**
 * Route #48 — PUT /api/v1/admin/education/{education}.
 */
class EducationController extends Controller
{
    use ApiResponse;

    public function update(UpdateEducationRequest $request, Education $education)
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

        $education->update($validated);

        return $this->success(new EducationResource($education->refresh()));
    }
}
