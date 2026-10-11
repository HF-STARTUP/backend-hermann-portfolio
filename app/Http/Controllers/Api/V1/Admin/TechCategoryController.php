<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Categories\StoreTechCategoryRequest;
use App\Http\Requests\Categories\UpdateTechCategoryRequest;
use App\Http\Resources\TechCategoryResource;
use App\Models\TechCategory;

/**
 * Routes #31 à #34 — CRUD des catégories de technologies (même contrat 409).
 */
class TechCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(TechCategoryResource::collection(TechCategory::query()->orderBy('name')->get()));
    }

    public function store(StoreTechCategoryRequest $request)
    {
        $category = TechCategory::create($request->validated());

        return $this->success(new TechCategoryResource($category), null, 201);
    }

    public function update(UpdateTechCategoryRequest $request, TechCategory $techCategory)
    {
        $techCategory->update($request->validated());

        return $this->success(new TechCategoryResource($techCategory->refresh()));
    }

    public function destroy(TechCategory $techCategory)
    {
        $usages = $techCategory->technologies()->count();

        if ($usages > 0) {
            return $this->error(
                "Impossible de supprimer cette catégorie : elle est utilisée par {$usages} technologie(s).",
                409,
                null,
                ['usages' => $usages]
            );
        }

        $techCategory->delete();

        return $this->noContent();
    }
}
