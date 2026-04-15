<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddContactToContactListRequest;
use App\Http\Requests\CreateContactListRequest;
use App\Http\Resources\ContactList as ContactListResource;
use App\Models\ContactList;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiContactListController extends Controller
{
    public function list(Request $request)
    {
        $contactLists = ContactList::query()
            ->withCount(['contacts', 'campaigns'])
            ->latest()
            ->paginate($this->perPage($request));

        return ContactListResource::collection($contactLists);
    }

    public function create(CreateContactListRequest $request)
    {
        $contactList = ContactList::create($request->validated())
            ->loadCount(['contacts', 'campaigns']);

        return (new ContactListResource($contactList))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function addContact(AddContactToContactListRequest $request, ContactList $contactList)
    {
        $contactList->contacts()->syncWithoutDetaching([$request->validated('contact_id')]);
        $contactList->loadCount(['contacts', 'campaigns']);

        return new ContactListResource($contactList);
    }
}
