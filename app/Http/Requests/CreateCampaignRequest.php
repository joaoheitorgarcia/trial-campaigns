<?php

namespace App\Http\Requests;

class CreateCampaignRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'contact_list_id' => ['required', 'integer', 'exists:contact_lists,id'],
            'scheduled_at' => ['nullable', 'date'],
            'reply_to' => ['nullable', 'email'],
        ];
    }
}
