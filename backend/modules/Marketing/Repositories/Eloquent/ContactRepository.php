<?php

namespace Modules\Marketing\Repositories\Eloquent;

use Modules\Marketing\Models\Contact;
use Modules\Marketing\Repositories\Contracts\ContactRepositoryInterface;

class ContactRepository implements ContactRepositoryInterface
{
    public function __construct(protected Contact $model) {}

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function getAll(int $perPage = 15)
    {
        return $this->model->latest()->paginate($perPage);
    }

    public function find(int $id)
    {
        return $this->model->findOrFail($id);
    }

    public function delete(int $id)
    {
        $contact = $this->model->findOrFail($id);
        return $contact->delete();
    }

    public function markAsRead(int $id)
    {
        $contact = $this->model->findOrFail($id);
        $contact->update(['read_at' => now()]);
        return $contact;
    }
}
