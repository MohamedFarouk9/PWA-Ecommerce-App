<?php

namespace App\Http\Resources\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug ?? null,
            'description' => $this->description ?? null,
            'icon' => $this->icon ?? null,
            'image' => $this->image ?? null,
            'is_active' => $this->is_active ?? true,
            'order' => $this->order ?? 0,

            // Breadcrumb support
            'parent' => $this->parent ? new self($this->parent) : null,

            // Child count for navigation
            'children_count' => $this->whenLoaded('children', fn() => $this->children->count()),

            // Product count if relation is loaded
            'products_count' => $this->when(isset($this->products_count), $this->products_count),
        ];
    }
}