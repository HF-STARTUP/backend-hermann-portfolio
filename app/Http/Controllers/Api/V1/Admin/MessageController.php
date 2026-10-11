<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Concerns\ApiResponse;
use App\Http\Requests\Messages\UpdateMessageReadRequest;
use App\Http\Resources\ContactMessageResource;
use App\Models\ContactMessage;

/**
 * Routes #42 à #44 — messages de contact (seule liste paginée : 15/page).
 */
class MessageController extends Controller
{
    use ApiResponse;

    public const PER_PAGE = 15;

    public function index()
    {
        $paginator = ContactMessage::query()
            // Filtres ?read=true|false et ?q= sur nom / sujet / email
            ->when(request()->filled('read'), fn ($q) => request()->boolean('read')
                ? $q->whereNotNull('read_at')
                : $q->whereNull('read_at'))
            ->when(request()->filled('q'), function ($q) {
                $term = '%'.request()->string('q').'%';
                $q->where(fn ($w) => $w->where('name', 'like', $term)
                    ->orWhere('subject', 'like', $term)
                    ->orWhere('email', 'like', $term));
            })
            ->orderByDesc('created_at')
            ->paginate(self::PER_PAGE);

        return response()->json([
            'success' => true,
            'message' => null,
            'data' => [
                'items' => ContactMessageResource::collection($paginator->items()),
                'pagination' => [
                    'currentPage' => $paginator->currentPage(),
                    'lastPage' => $paginator->lastPage(),
                    'perPage' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
            ],
        ]);
    }

    public function setRead(UpdateMessageReadRequest $request, ContactMessage $message)
    {
        $message->update([
            'read_at' => $request->boolean('read') ? now() : null,
        ]);

        return $this->success(new ContactMessageResource($message->refresh()));
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return $this->noContent();
    }
}
