<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Contrat TS ContactMessage — sentAt ISO-8601 UTC, read = read_at !== null.
 *
 * @mixin \App\Models\ContactMessage
 */
class ContactMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'subject' => $this->subject,
            'message' => $this->message,
            'sentAt' => $this->created_at?->toISOString(),
            'read' => $this->read_at !== null,
        ];
    }
}
