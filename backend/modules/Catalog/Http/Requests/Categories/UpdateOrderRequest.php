<?php

namespace Modules\Catalog\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:product_categories,id',
            'categories.*.order' => 'required|integer|min:0',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'categories.required' => 'The categories array is required.',
            'categories.array' => 'The categories must be an array.',
            'categories.*.id.required' => 'Category ID is required.',
            'categories.*.id.exists' => 'One or more category IDs do not exist.',
            'categories.*.order.required' => 'Category order is required.',
            'categories.*.order.integer' => 'Category order must be a number.',
            'categories.*.order.min' => 'Category order must be at least 0.',
        ];
    }
}
