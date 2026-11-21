<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array {
        return [
            'name'     => 'required|string',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];
    }

    /**
     * Custom error messages for validation.
     */
    public function messages(): array {
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
