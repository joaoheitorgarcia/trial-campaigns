<?php

namespace App\Http\Requests;

use App\Models\Contact;
use Illuminate\Validation\Rule;

class CreateContactRequest extends ApiFormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('contacts', 'email')],
            'status' => ['required', 'string', Rule::in(Contact::STATUSES)],
        ];
    }
}
