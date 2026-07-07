<?php
namespace App\Http\Requests\Admin\Products;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('products.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'product_category_id' => 'required|integer|exists:product_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'product_status_id' => 'nullable|integer|exists:product_statuses,id',
            'status' => 'required|string|in:draft,published,archived',

            'tags' => 'array',
            'tags.*' => 'integer|exists:product_tags,id',
            'sections' => 'array',
            'sections.*' => 'integer|exists:sections,id',

            'variants' => 'nullable|array',
            'variants.*.product_attribute_id' => 'nullable|integer|exists:product_attributes,id',
            'variants.*.value' => 'nullable|string|max:255',
            'variants.*.additional_price' => 'nullable|numeric|min:0',
            'variants.*.quantity' => 'nullable|integer|min:0',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }
}


