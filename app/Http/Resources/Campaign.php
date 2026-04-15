<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class Campaign extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'body' => $this->body,
            'reply_to' => $this->reply_to,
            'contact_list_id' => $this->contact_list_id,
            'status' => $this->status,
            'scheduled_at' => $this->scheduled_at?->toISOString(),
            'stats' => $this->stats,
            'contact_list' => $this->whenLoaded('contactList', fn () => new ContactList($this->contactList)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
