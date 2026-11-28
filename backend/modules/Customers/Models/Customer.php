<?php

namespace Modules\Customers\Models;

use App\Models\User;

class Customer extends User
{
    protected $table = 'users';

    public function getMorphClass(): string
    {
        return User::class;
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'user');
    }
}
