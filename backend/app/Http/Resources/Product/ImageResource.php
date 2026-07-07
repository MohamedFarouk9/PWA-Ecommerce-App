<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => $this->url ?? ($this->path ? asset('storage/' . $this->path) : null),
            'position' => $this->position,
            'is_cover' => $this->is_cover,
        ];
    }
}
