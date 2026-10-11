<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\Request;

/**
 * Routes #4 et #5 — lecture publique du blog.
 */
class BlogPostController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $request->validate([
            'tag' => ['sometimes', 'string', 'max:50'],
        ]);

        $posts = BlogPost::query()
            ->when(
                $request->filled('tag'),
                fn ($q) => $q->whereJsonContains('tags', $request->string('tag')->toString())
            )
            ->orderByDesc('published_at')
            ->get();

        return $this->success(
            BlogPostResource::collection($posts)
        );
    }

    public function show(string $slug)
    {
        $post = BlogPost::query()->where('slug', $slug)->first();

        abort_if($post === null, 404);

        return $this->success(new BlogPostResource($post));
    }
}
