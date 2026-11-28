<?php

namespace Modules\Customers\Actions;

use App\Models\User;

class ToggleCustomerStatusAction
{
    public function execute(User $user): string
    {
        $newStatus = ($user->status === 'active') ? 'banned' : 'active';
        $user->update(['status' => $newStatus]);
        return $newStatus;
    }
}
