<?php

namespace Modules\Marketing\Http\Requests\Contact;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreRequest extends FormRequest {
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
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:contacts,email',
            'message' => 'required|string',
        ];
    }

    public function messages(): array {
        return [
            'email.unique' => 'The email has already been taken. Please use a different email address.', // Custom message
        ];
    }

    protected function failedValidation(Validator $validator) {
        // Throw an exception with a custom response containing validation errors
        throw new HttpResponseException(response()->json([
            'message' => $validator->errors()->first(), // Return the first validation error
        ], 422)); // Unprocessable Entity
    }
}
