<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\BlogPosts\StoreBlogPostRequest;
use App\Http\Requests\BlogPosts\UpdateBlogPostRequest;
use App\Http\Resources\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Support\Str;

/**
 * Routes #21 à #26 — CRUD admin des articles de blog.
 * Si coverType = video → coverVideoUrl requis ; youtube → coverYoutubeUrl
 * requis et validé (FormRequests). L'URL brute YouTube est stockée telle quelle.
 */
class BlogPostController extends Controller
{
    use ApiResponse;

    private const MAPPING = [
        'title' => 'title',
        'slug' => 'slug',
        'excerpt' => 'excerpt',
        'coverImageUrl' => 'cover_image_url',
        'coverType' => 'cover_type',
        'coverVideoUrl' => 'cover_video_url',
        'coverYoutubeUrl' => 'cover_youtube_url',
        'tags' => 'tags',
        'publishedAt' => 'published_at',
        'readTimeMinutes' => 'read_time_minutes',
        'content' => 'content',
    ];

    public function index()
    {
        return $this->success(BlogPostResource::collection(BlogPost::query()->orderByDesc('published_at')->get()));
    }

    public function store(StoreBlogPostRequest $request)
    {
        $post = BlogPost::create($this->payload($request));

        return $this->success(new BlogPostResource($post), null, 201);
    }

    public function show(BlogPost $blogPost)
    {
        return $this->success(new BlogPostResource($blogPost));
    }

    public function update(UpdateBlogPostRequest $request, BlogPost $blogPost)
    {
        $blogPost->update($this->payload($request));

        return $this->success(new BlogPostResource($blogPost->refresh()));
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return $this->noContent();
    }

    private function payload(StoreBlogPostRequest|UpdateBlogPostRequest $request): array
    {
        $data = [];

        foreach ($request->validated() as $camel => $value) {
            if (isset(self::MAPPING[$camel])) {
                $data[self::MAPPING[$camel]] = $value;
            }
        }

        // Slug serveur si absent ; unicité garantie par suffixation
        if (! isset($data['slug']) && ! empty($data['title'])) {
            $base = Str::slug((string) $data['title']);
            $slug = $base;
            $i = 2;
            while (BlogPost::where('slug', $slug)->where('id', '!=', $request->route('blogPost')?->id ?? '')->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
        }

        return $data;
    }
}
