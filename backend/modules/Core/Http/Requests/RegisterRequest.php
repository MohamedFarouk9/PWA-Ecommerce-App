<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'The name field is required.',
            'email.required'     => 'The email field is required.',
            'email.email'        => 'The email must be a valid email address.',
            'email.unique'       => 'The email has already been taken.',
            'password.required'  => 'The password field is required.',
            'password.min'       => 'The password must be at least 6 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }
}
