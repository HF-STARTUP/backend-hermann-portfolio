<?php

namespace App\Http\Requests\BlogPosts;

use App\Rules\HostedImageOrMediaPath;
use App\Rules\YoutubeUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBlogPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['sometimes', 'nullable', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'max:255', Rule::unique('blog_posts', 'slug')],
            'excerpt' => ['required', 'string', 'min:10', 'max:500'],
            'coverType' => ['required', Rule::in(['image', 'video', 'youtube'])],
            'coverImageUrl' => ['nullable', new HostedImageOrMediaPath],
            'coverVideoUrl' => ['nullable', 'url', 'max:500', 'required_if:coverType,video'],
            'coverYoutubeUrl' => ['nullable', 'required_if:coverType,youtube', new YoutubeUrl],
            'publishedAt' => ['required', 'date', 'date_format:Y-m-d'],
            'tags' => ['required', 'array', 'min:1', 'max:15'],
            'tags.*' => ['required', 'string', 'max:50', 'distinct'],
            'readTimeMinutes' => ['required', 'integer', 'min:1', 'max:120'],
            'content' => ['required', 'array', 'min:1', 'max:300'],
            'content.*.type' => ['required', Rule::in(['paragraph', 'heading', 'quote', 'code'])],
            'content.*.text' => ['required', 'string', 'max:10000'],
            'content.*.author' => ['sometimes', 'nullable', 'string', 'max:120'],
            'content.*.language' => ['sometimes', 'nullable', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis',
            'slug.regex' => 'Slug invalide : minuscules et tirets uniquement (ex: mon-article)',
            'slug.unique' => 'Ce slug est déjà utilisé',
            'excerpt.required' => "L'extrait est requis",
            'excerpt.min' => "L'extrait doit contenir au moins 10 caractères",
            'coverType.required' => 'Le type de couverture est requis',
            'coverType.in' => 'Type de couverture invalide',
            'coverVideoUrl.required_if' => 'Le lien de la vidéo est requis pour une couverture vidéo',
            'coverVideoUrl.url' => "L'URL de la vidéo doit être valide (http/https)",
            'coverYoutubeUrl.required_if' => 'Le lien YouTube est requis pour une couverture YouTube',
            'content.required' => 'Le contenu est requis',
            'tags.required' => 'Indiquez au moins un tag',
            'publishedAt.required' => 'La date de publication est requise',
            'publishedAt.date_format' => 'La date de publication doit être au format AAAA-MM-JJ',
            'tags.min' => 'Indiquez au moins un tag',
            'readTimeMinutes.required' => 'Temps de lecture requis',
            'readTimeMinutes.min' => 'Minimum 1 minute',
            'readTimeMinutes.max' => 'Maximum 120 minutes',
        ];
    }
}
