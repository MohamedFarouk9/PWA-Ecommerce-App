<?php

namespace Modules\Dashboard\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'    => 'required|string',
            'email'   => 'required|email|unique:users,email,' . $this->user()->id,
            'phone'   => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city'    => 'nullable|string|max:100',
            'state'   => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'image'   => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'The name field is required.',
            'email.required' => 'The email field is required.',
            'email.email'    => 'The email must be a valid email address.',
            'email.unique'   => 'The email has already been taken.',
            'image.image'    => 'The image must be a valid image file.',
            'image.mimes'    => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max'      => 'The image may not be greater than 2MB.',
            'phone.max'      => 'The phone may not be greater than 20 characters.',
            'address.max'    => 'The address may not be greater than 255 characters.',
            'city.max'       => 'The city may not be greater than 100 characters.',
            'state.max'      => 'The state may not be greater than 100 characters.',
            'country.max'    => 'The country may not be greater than 100 characters.',
        ];
    }
}
