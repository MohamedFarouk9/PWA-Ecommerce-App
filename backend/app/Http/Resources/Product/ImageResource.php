<?php

namespace App\Http\Resources\Product;

use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $service = app(ImageOptimizationService::class);
        $urls = [];

        if ($this->path) {
            $productId = $this->product_id;
            $filename = basename($this->path);

            $urls = [
                'original' => $service->getImageUrl($productId, $filename, 'original'),
                'thumbnail' => $service->getImageUrl($productId, $filename, 'thumbnail'),
                'small' => $service->getImageUrl($productId, $filename, 'small'),
                'medium' => $service->getImageUrl($productId, $filename, 'medium'),
                'large' => $service->getImageUrl($productId, $filename, 'large'),
                'webp_medium' => $service->getImageUrl($productId, $filename, 'webp_medium'),
                'blur' => $service->getImageUrl($productId, $filename, 'blur'),
            ];
        }

        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => $this->url ?? ($this->path ? asset('storage/' . $this->path) : null),
            'position' => $this->position,
            'is_cover' => $this->is_cover,
            'urls' => $urls,
        ];
    }
}
