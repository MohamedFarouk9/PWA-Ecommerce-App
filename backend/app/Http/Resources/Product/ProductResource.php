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
        // Load relationships if not already loaded
        $this->load([
            'category',
            'status',
            'images',
            'productVariants',
            'tags',
            'reviews',
            'sections',
            'specifications',
        ]);

        // Primary image URL
        $mediaUrl = null;
        if (isset($this->image_url)) {
            $mediaUrl = $this->image_url;
        } elseif (method_exists($this, 'getFirstMediaUrl')) {
            $mediaUrl = $this->getFirstMediaUrl('cover');
        }

        // Process color and size options
        $colors = $this->color ? array_map(function($color) {
            return [
                'name' => $color,
                'active' => true,
            ];
        }, (array) $this->color) : [];

        $sizes = $this->size ? array_map(function($size) {
            return [
                'name' => $size,
                'active' => true,
            ];
        }, (array) $this->size) : [];

        // Collect all product images for gallery
        $allImages = collect();
        // Add main image first
        if ($mediaUrl) {
            $allImages->push([
                'id' => null,
                'url' => $mediaUrl,
                'path' => $mediaUrl,
                'position' => 0,
            ]);
        }
        // Add gallery images using ImageResource for optimized URLs
        if ($this->relationLoaded('images')) {
            $allImages = $allImages->merge(ImageResource::collection($this->images)->resolve());
        }

        // Calculate average rating and review count
        $reviewCount = $this->reviews->count();
        $averageRating = $reviewCount > 0
            ? round($this->reviews->avg('rating'), 1)
            : 0;

        // Build rating breakdown (1-5 stars)
        $ratingBreakdown = [
            '5_star' => 0,
            '4_star' => 0,
            '3_star' => 0,
            '2_star' => 0,
            '1_star' => 0,
        ];
        if ($this->relationLoaded('reviews')) {
            foreach ($this->reviews as $review) {
                $star = (int) $review->rating;
                if (isset($ratingBreakdown[$star . '_star'])) {
                    $ratingBreakdown[$star . '_star']++;
                }
            }
        }

        // Collect specifications
        $specifications = [];
        if ($this->relationLoaded('specifications')) {
            foreach ($this->specifications as $spec) {
                $specifications[] = [
                    'attribute_id' => $spec->pivot->product_attribute_id,
                    'attribute_name' => $spec->name,
                    'value' => $spec->pivot->value,
                    'unit' => $spec->pivot->unit,
                    'position' => $spec->pivot->position,
                ];
            }
        }

        // Sort specifications by position
        usort($specifications, fn($a, $b) => $a['position'] <=> $b['position']);

        return [
            // Basic product info
            'id' => $this->id,
            'product_id' => $this->id,
            'title' => $this->name,
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug ?? $this->getRouteKeyName(),

            // Pricing
            'price' => $this->price !== null ? (float) $this->price : null,
            'special_price' => $this->special_price !== null ? (float) $this->special_price : null,
            'is_on_sale' => $this->isOnSale ?? ($this->special_price !== null && $this->special_price < $this->price),
            'savings' => $this->isOnSale ? round($this->price - $this->special_price, 2) : 0,
            'sku' => $this->sku,

            // Inventory
            'quantity' => $this->quantity !== null ? (int) $this->quantity : null,
            'in_stock' => $this->quantity > 0,

            // Categorization
            'status' => $this->whenLoaded('status') ? $this->status->name : '',
            'category' => new CategoryResource($this->category),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'sections' => SectionResource::collection($this->whenLoaded('sections')),

            // Brand & Product Identification
            'brand' => $this->brand,
            'product_code' => $this->product_code,
            'remark' => $this->remark,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'view_count' => $this->view_count,

            // Product Details (short/long descriptions)
            'short_description' => $this->short_description,

            // Image Gallery (Amazon-style multi-image display)
            'image' => $mediaUrl,
            'images' => $allImages->values()->all(),
            'image_count' => $allImages->count(),

            // Gallery options (Color/Size selectors like Amazon)
            'product_details' => [
                'color' => $colors,
                'size' => $sizes,
            ],

            // Specifications (Technical features)
            'specifications' => $specifications,

            // Variants (Different product configurations in stock)
            'variants' => VariantResource::collection($this->whenLoaded('productVariants')),

            // Reviews & Ratings (Amazon star rating system)
            'rating' => [
                'average' => $averageRating,
                'count' => $reviewCount,
                'breakdown' => $ratingBreakdown,
                'percent_5' => $reviewCount > 0 ? round(($ratingBreakdown['5_star'] / $reviewCount) * 100) : 0,
                'percent_4' => $reviewCount > 0 ? round(($ratingBreakdown['4_star'] / $reviewCount) * 100) : 0,
                'percent_3' => $reviewCount > 0 ? round(($ratingBreakdown['3_star'] / $reviewCount) * 100) : 0,
                'percent_2' => $reviewCount > 0 ? round(($ratingBreakdown['2_star'] / $reviewCount) * 100) : 0,
                'percent_1' => $reviewCount > 0 ? round(($ratingBreakdown['1_star'] / $reviewCount) * 100) : 0,
            ],

            // Reviews (detailed customer feedback)
            'reviews' => ReviewsResource::collection($this->whenLoaded('reviews')),

            // Timestamps
            'created_at' => $this->created_at?->diffForHumans(),
            'updated_at' => $this->updated_at?->diffForHumans(),
        ];
    }
}