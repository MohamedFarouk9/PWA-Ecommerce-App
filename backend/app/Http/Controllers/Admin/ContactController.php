<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Contact\StoreRequest;
use App\Repositories\Contracts\ContactRepositoryInterface;
use App\Traits\ResponseTrait;

class ContactController extends Controller
{
    use ResponseTrait;

    public function __construct(private ContactRepositoryInterface $contactRepository) {}

    /**
     * Store contact message
     */
    public function postContact(StoreRequest $request)
    {
        $contact = $this->contactRepository->create($request->validated());

        return response()->json([
            'message' => 'Contact details submitted successfully',
            'contact' => $contact,
        ], 201);
    }
}
