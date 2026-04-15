<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateContactRequest;
use App\Http\Resources\Contact as ContactResource;
use App\Models\Contact;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiContactController extends Controller
{
    public function list(Request $request)
    {
        $contacts = Contact::query()
            ->latest()
            ->paginate($this->perPage($request));

        return ContactResource::collection($contacts);
    }

    public function create(CreateContactRequest $request)
    {
        $contact = Contact::create($request->validated());

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function unsubscribe(Contact $contact)
    {
        $contact->update(['status' => Contact::STATUS_UNSUBSCRIBED]);

        return new ContactResource($contact->refresh());
    }
}
