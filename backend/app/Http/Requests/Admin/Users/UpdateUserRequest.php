<?php

namespace App\Http\Requests\Admin\Users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id') ?? $this->route('user');

        return [
            'name'        => 'required|string|max:255',
            'email'       => ['required', 'email', Rule::unique('users')->ignore($userId)],
            'role'        => 'sometimes|string|in:admin,user',
            'status'      => 'sometimes|string|in:active,inactive,banned',
            'phone'       => 'nullable|string|max:30',
            'address'     => 'nullable|string|max:500',
            'city'        => 'nullable|string|max:100',
            'state'       => 'nullable|string|max:100',
            'country'     => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'password'    => 'nullable|string|min:8',
        ];
    }
}
