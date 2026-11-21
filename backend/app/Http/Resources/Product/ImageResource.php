<?php

namespace App\Http\Resources\Product;

use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $directUrl = $this->url;
        $urls = $this->urls;

        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => $directUrl,
            'position' => $this->position,
            'is_cover' => $this->is_cover,
            'urls' => $urls,
        ];
    }
}
