<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\ProjectCategoryResource;
use App\Http\Resources\TechCategoryResource;
use App\Http\Resources\TechnologyResource;
use App\Models\ProjectCategory;
use App\Models\TechCategory;
use App\Models\Technology;
use Illuminate\Http\Request;

/**
 * Routes #6 à #8 — catégories (projets + technos) et technologies.
 */
class TaxonomyController extends Controller
{
    use ApiResponse;

    public function projectCategories()
    {
        return $this->success(
            ProjectCategoryResource::collection(ProjectCategory::query()->orderBy('id')->get())
        );
    }

    public function techCategories()
    {
        return $this->success(
            TechCategoryResource::collection(TechCategory::query()->orderBy('id')->get())
        );
    }

    public function technologies(Request $request)
    {
        // Filtre optionnel ?categoryId=<ulid> (spec #8)
        $request->validate([
            'categoryId' => ['sometimes', 'exists:tech_categories,id'],
        ]);

        return $this->success(
            TechnologyResource::collection(
                Technology::query()
                    ->when($request->filled('categoryId'), fn ($q) => $q->where('tech_category_id', $request->input('categoryId')))
                    ->orderBy('id')
                    ->get()
            )
        );
    }
}
