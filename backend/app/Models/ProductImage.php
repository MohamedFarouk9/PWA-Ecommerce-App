<?php

namespace App\Models;

use App\Services\ImageOptimizationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'path',
        'position',
        'is_cover',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'position' => 'integer',
    ];

    protected $appends = ['urls'];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? asset('storage/' . $this->path) : null;
    }

    /**
     * Get optimized image URLs for different sizes
     */
    public function getUrlsAttribute(): array
    {
        if (!$this->path) {
            return [
                'original' => null,
                'thumbnail' => null,
                'small' => null,
                'medium' => null,
                'large' => null,
                'webp_medium' => null,
                'blur' => null,
            ];
        }

        // Use ImageOptimizationService to generate URLs
        $service = app(ImageOptimizationService::class);
        $productId = $this->product_id;

        // Extract filename from path
        $filename = basename($this->path);

        return [
            'original' => $service->getImageUrl($productId, $filename, 'original'),
            'thumbnail' => $service->getImageUrl($productId, $filename, 'thumbnail'),
            'small' => $service->getImageUrl($productId, $filename, 'small'),
            'medium' => $service->getImageUrl($productId, $filename, 'medium'),
            'large' => $service->getImageUrl($productId, $filename, 'large'),
            'webp_medium' => $service->getImageUrl($productId, $filename, 'webp_medium'),
            'blur' => $service->getImageUrl($productId, $filename, 'blur'),
        ];
    }
}
