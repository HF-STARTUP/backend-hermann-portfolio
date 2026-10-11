<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Models\ProjectCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Routes #2 et #3 — lecture publique des projets.
 */
class ProjectController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        // Filtres optionnels ?featured=1&category=<id> (spec #2)
        try {
            $request->validate([
                'featured' => ['sometimes', 'boolean'],
                'category' => ['sometimes', 'exists:project_categories,id'],
            ]);
        } catch (ValidationException $e) {
            return $this->error('Paramètres invalides.', 422, $e->errors());
        }

        $projects = Project::query()
            ->when($request->boolean('featured'), fn ($q) => $q->where('featured', true))
            ->when($request->input('category'), fn ($q, $id) => $q->where('project_category_id', $id))
            ->orderByDesc('start_date')
            ->get();

        return $this->success(
            ProjectResource::collection($projects)
        );
    }

    public function show(string $slug)
    {
        $project = Project::query()->where('slug', $slug)->first();

        abort_if($project === null, 404);

        return $this->success(new ProjectResource($project));
    }
}
