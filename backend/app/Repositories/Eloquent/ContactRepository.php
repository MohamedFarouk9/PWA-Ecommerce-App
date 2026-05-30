<?php

namespace App\Repositories\Eloquent;

use App\Models\Contact;
use App\Repositories\Contracts\ContactRepositoryInterface;

class ContactRepository implements ContactRepositoryInterface
{
    protected $model;

    public function __construct(Contact $model)
    {
        $this->model = $model;
    }

    /**
     * Create a new contact
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Get all contacts paginated
     */
    public function getAll(int $perPage = 15)
    {
        return $this->model
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find contact by ID
     */
    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Delete contact
     */
    public function delete(int $id)
    {
        $contact = $this->model->findOrFail($id);
        return $contact->delete();
    }

    /**
     * Mark contact as read
     */
    public function markAsRead(int $id)
    {
        $contact = $this->model->findOrFail($id);
        $contact->update(['read_at' => now()]);
        return $contact;
    }
}
