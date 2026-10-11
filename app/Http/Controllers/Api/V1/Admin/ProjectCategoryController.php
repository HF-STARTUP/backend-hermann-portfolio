<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Categories\StoreProjectCategoryRequest;
use App\Http\Requests\Categories\UpdateProjectCategoryRequest;
use App\Http\Resources\ProjectCategoryResource;
use App\Models\ProjectCategory;

/**
 * Routes #27 à #30 — CRUD des catégories de projets.
 * Garde-fou suppression : 409 + { usages } si au moins un projet l'utilise.
 */
class ProjectCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(ProjectCategoryResource::collection(ProjectCategory::query()->orderBy('label')->get()));
    }

    public function store(StoreProjectCategoryRequest $request)
    {
        $category = ProjectCategory::create($request->validated());

        return $this->success(new ProjectCategoryResource($category), null, 201);
    }

    public function update(UpdateProjectCategoryRequest $request, ProjectCategory $projectCategory)
    {
        $projectCategory->update($request->validated());

        return $this->success(new ProjectCategoryResource($projectCategory->refresh()));
    }

    public function destroy(ProjectCategory $projectCategory)
    {
        $usages = $projectCategory->projects()->count();

        if ($usages > 0) {
            return $this->error(
                "Impossible de supprimer cette catégorie : elle est utilisée par {$usages} projet(s).",
                409,
                null,
                ['usages' => $usages]
            );
        }

        $projectCategory->delete();

        return $this->noContent();
    }
}
