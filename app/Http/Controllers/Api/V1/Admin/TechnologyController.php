<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Technologies\StoreTechnologyRequest;
use App\Http\Requests\Technologies\UpdateTechnologyRequest;
use App\Http\Resources\TechnologyResource;
use App\Models\Technology;
use Illuminate\Http\Request;

/**
 * Routes #35 à #38 — CRUD des technologies (sans show).
 */
class TechnologyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        // Filtre optionnel ?categoryId= (spec #35)
        $request->validate([
            'categoryId' => ['sometimes', 'exists:tech_categories,id'],
        ]);

        return $this->success(
            TechnologyResource::collection(
                Technology::query()
                    ->when($request->filled('categoryId'), fn ($q) => $q->where('tech_category_id', $request->input('categoryId')))
                    ->orderBy('name')
                    ->get()
            )
        );
    }

    public function store(StoreTechnologyRequest $request)
    {
        $technology = Technology::create([
            'name' => $request->validated('name'),
            'image_url' => $request->validated('imageUrl'),
            'tech_category_id' => $request->validated('categoryId'),
        ]);

        return $this->success(new TechnologyResource($technology), null, 201);
    }

    public function update(UpdateTechnologyRequest $request, Technology $technology)
    {
        $validated = $request->validated();

        if (array_key_exists('categoryId', $validated)) {
            $validated['tech_category_id'] = $validated['categoryId'];
            unset($validated['categoryId']);
        }
        if (array_key_exists('imageUrl', $validated)) {
            $validated['image_url'] = $validated['imageUrl'];
            unset($validated['imageUrl']);
        }

        $technology->update($validated);

        return $this->success(new TechnologyResource($technology->refresh()));
    }

    public function destroy(Technology $technology)
    {
        $technology->delete();

        return $this->noContent();
    }
}
