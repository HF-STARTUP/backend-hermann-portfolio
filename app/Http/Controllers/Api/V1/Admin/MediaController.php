<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Media\StoreMediaRequest;
use App\Http\Resources\MediaItemResource;
use App\Models\MediaItem;
use App\Services\MediaService;

/**
 * Routes #39 à #41 — médiathèque (upload multipart réservé admin).
 */
class MediaController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(MediaItemResource::collection(MediaItem::query()->latest()->get()));
    }

    public function store(StoreMediaRequest $request, MediaService $service)
    {
        // 413 / 415 levés par le service (taille réelle + MIME réel)
        $media = $service->store($request->file('file'));

        return $this->success(new MediaItemResource($media), null, 201);
    }

    public function destroy(MediaItem $mediaItem, MediaService $service)
    {
        $service->delete($mediaItem);

        return $this->noContent();
    }
}
