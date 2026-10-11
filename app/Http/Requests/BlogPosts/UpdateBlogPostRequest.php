<?php

namespace App\Http\Requests\BlogPosts;

use App\Rules\HostedImageOrMediaPath;
use App\Rules\YoutubeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $postId = $this->route('blogPost')?->id;

        return [
            'title' => ['sometimes', 'string', 'max:200'],
            'slug' => ['sometimes', 'nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255', Rule::unique('blog_posts', 'slug')->ignore($postId)],
            'excerpt' => ['sometimes', 'string', 'min:10', 'max:500'],
            'coverType' => ['sometimes', Rule::in(['image', 'video', 'youtube'])],
            'coverImageUrl' => ['sometimes', 'nullable', new HostedImageOrMediaPath],
            'coverVideoUrl' => ['sometimes', 'nullable', 'url', 'max:500', 'required_if:coverType,video'],
            'coverYoutubeUrl' => ['sometimes', 'nullable', 'required_if:coverType,youtube', new YoutubeUrl],
            'publishedAt' => ['sometimes', 'nullable', 'date', 'date_format:Y-m-d'],
            'tags' => ['sometimes', 'array', 'min:1', 'max:15'],
            'tags.*' => ['required', 'string', 'max:50', 'distinct'],
            'readTimeMinutes' => ['sometimes', 'integer', 'min:1', 'max:120'],
            'content' => ['sometimes', 'array', 'min:1', 'max:300'],
            'content.*.type' => ['required_with:content', Rule::in(['paragraph', 'heading', 'quote', 'code'])],
            'content.*.text' => ['required_with:content', 'string', 'max:10000'],
            'content.*.author' => ['sometimes', 'nullable', 'string', 'max:120'],
            'content.*.language' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.regex' => 'Slug invalide : minuscules et tirets uniquement (ex: mon-article)',
            'slug.unique' => 'Ce slug est déjà utilisé',
        ];
    }
}
