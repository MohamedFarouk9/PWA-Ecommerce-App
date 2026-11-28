<?php

namespace Modules\Catalog\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // You can add authorization logic here if needed
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // route('category') may be a model instance or an id string.
        $routeCategory = $this->route('category');
        $id = is_object($routeCategory) ? ($routeCategory->id ?? null) : $routeCategory;

        return [
            // use "sometimes" so partial updates (e.g. only is_active) are allowed
            'name' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('product_categories')->ignore($id)],
            'description' => 'sometimes|nullable|string',
            'parent_id' => [
                'sometimes',
                'nullable',
                'exists:product_categories,id',
                function ($attribute, $value, $fail) use ($id) {
                    if ($value == $id) {
                        $fail('A category cannot be its own parent.');
                    }
                },
            ],
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'sometimes|boolean',
            'order' => 'sometimes|integer|min:0',
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
            'name.required' => 'The category name is required.',
            'name.unique' => 'This category name is already taken.',
            'name.max' => 'The category name cannot exceed 255 characters.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'image.max' => 'The image may not be greater than 2MB.',
            'order.integer' => 'The order must be a number.',
            'order.min' => 'The order must be at least 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}
