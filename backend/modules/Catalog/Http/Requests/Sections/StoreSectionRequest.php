<?php

namespace Modules\Catalog\Http\Requests\Sections;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => 'required|string|max:100|unique:sections,name',
            'label' => 'required|string|max:255',
        ];
    }
}
