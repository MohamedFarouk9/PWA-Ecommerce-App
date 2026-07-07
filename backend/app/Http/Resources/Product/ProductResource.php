<?php
namespace App\Http\Resources\Product;

use App\Http\Resources\Product\CategoryResource;
use App\Http\Resources\Product\ImageResource;
use App\Http\Resources\Product\SectionResource;
use App\Http\Resources\Product\TagResource;
use App\Http\Resources\Product\VariantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request)
    {

        // Provide fields expected by the frontend. Use sensible fallbacks when attributes or relations are missing.
        $mediaUrl = null;
        if (isset($this->image_url)) {
            $mediaUrl = $this->image_url;
        } elseif (method_exists($this, 'getFirstMediaUrl')) {
            $mediaUrl = $this->getFirstMediaUrl('cover');
        }

        // product_details may be a relation or stored attributes; normalize into an object
        $productDetails = null;
        if (isset($this->product_details) && is_object($this->product_details)) {
            $productDetails = $this->product_details;
        } else {
            $productDetails = (object) [
                'color' => isset($this->color) ? json_decode($this->color, true) : null,
                'size' => isset($this->size) ? json_decode($this->size, true) : null,
                'short_description' => $this->short_description ?? null,
                'long_description' => $this->long_description ?? null,
            ];
        }

        return [
            // keep original ids and names
            'id' => $this->id,
            'product_id' => $this->id,
            'title' => $this->name,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price !== null ? (float) $this->price : null,
            'special_price' => $this->special_price ?? null,
            'quantity' => $this->quantity !== null ? (int) $this->quantity : null,
            'status' => $this->whenLoaded('status') ? $this->status->name : '',
            'category' => new CategoryResource($this->category),
            'brand' => $this->brand ?? null,
            'product_code' => $this->product_code ?? null,
            'remark' => $this->remark ?? null,
            'image' => $mediaUrl,
            'images' => ImageResource::collection($this->whenLoaded('images') ? $this->images : collect()),
            'product_details' => [
                'color' => $productDetails->color ?? null,
                'size' => $productDetails->size ?? null,
                'short_description' => $productDetails->short_description ?? null,
                'long_description' => $productDetails->long_description ?? null,
            ],
            // collections use the corresponding resource collections
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'variants' => VariantResource::collection($this->whenLoaded('productVariants')),
            'rating' => ReviewsResource::collection($this->whenLoaded('reviews')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),
            'created_at' => $this->created_at ? $this->created_at->diffForHumans() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->diffForHumans() : null,
        ];
    }
}
