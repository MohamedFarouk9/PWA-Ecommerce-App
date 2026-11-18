<?php

namespace App\Http\Requests\Admin\Contact;

use Illuminate\Foundation\Http\FormRequest;

class ReplyContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reply_message' => 'required|string|max:5000',
        ];
    }
}
