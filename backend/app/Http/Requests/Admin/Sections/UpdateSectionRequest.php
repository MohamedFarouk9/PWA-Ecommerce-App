<?php

namespace App\Http\Requests\Admin\Sections;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('section') ?? $this->route('id');

        return [
            'name'  => 'required|string|max:100|unique:sections,name,' . $id,
            'label' => 'required|string|max:255',
        ];
    }
}
