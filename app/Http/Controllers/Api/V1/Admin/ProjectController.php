<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Support\Str;

/**
 * Routes #16 à #20 — CRUD admin des projets.
 * Le champ TS « category » (id ULID) alimente la colonne project_category_id.
 */
class ProjectController extends Controller
{
    use ApiResponse;

    private const MAPPING = [
        'slug' => 'slug',
        'title' => 'title',
        'role' => 'role',
        'description' => 'description',
        'longDescription' => 'long_description',
        'imageUrl' => 'image_url',
        'techStack' => 'tech_stack',
        'category' => 'project_category_id',
        'demoUrl' => 'demo_url',
        'githubUrl' => 'github_url',
        'featured' => 'featured',
        'startDate' => 'start_date',
        'endDate' => 'end_date',
    ];

    public function index()
    {
        return $this->success(ProjectResource::collection(Project::query()->orderByDesc('start_date')->get()));
    }

    public function store(StoreProjectRequest $request)
    {
        $project = Project::create($this->payload($request));

        return $this->success(new ProjectResource($project), null, 201);
    }

    public function show(Project $project)
    {
        return $this->success(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($this->payload($request));

        return $this->success(new ProjectResource($project->refresh()), null, 200);
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return $this->noContent();
    }

    /**
     * Traduit le payload camelCase en colonnes snake_case + slug serveur.
     */
    private function payload(StoreProjectRequest|UpdateProjectRequest $request): array
    {
        $data = [];

        foreach ($request->validated() as $camel => $value) {
            if (isset(self::MAPPING[$camel])) {
                $data[self::MAPPING[$camel]] = $value;
            }
        }

        // Slug généré côté serveur si absent
        if (! isset($data['slug']) && ! empty($data['title'])) {
            $base = Str::slug((string) $data['title']);
            $slug = $base;
            $i = 2;
            while (Project::where('slug', $slug)->where('id', '!=', $request->route('project')?->id ?? '')->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }
}
