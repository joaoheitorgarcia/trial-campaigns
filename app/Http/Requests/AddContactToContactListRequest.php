<?php

namespace App\Http\Requests;

class AddContactToContactListRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'contact_id' => ['required', 'integer', 'exists:contacts,id'],
        ];
    }
}
