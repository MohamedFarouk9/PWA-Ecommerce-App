<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'name',
        'description',
        'product_category_id',
        'price',
        'quantity',
        'product_status_id',
        'image',
        // New Amazon/Noon style fields
        'brand',
        'product_code',
        'remark',
        'special_price',
        'sku',
        'short_description',
        'color',
        'size',
        'meta_title',
        'meta_description',
        'view_count',
    ];

    /**
     * Casts for attribute types.
     */
    protected $casts = [
        'color' => 'array',
        'size' => 'array',
        'price' => 'decimal:2',
        'special_price' => 'decimal:2',
        'quantity' => 'integer',
        'view_count' => 'integer',
        'status' => 'integer',
    ];

    /**
     * Get the product's primary image URL.
     */
    public function getImageUrlAttribute()
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return asset('storage/' . $this->images->first()->path);
        }

        if ($this->image) {
            return asset('storage/' . $this->image);
        }

        return null;
    }

    /**
     * Compatibility method used in views: getFirstMediaUrl('cover')
     */
    public function getFirstMediaUrl($collection = 'cover')
    {
        return $this->getImageUrlAttribute();
    }

    /**
     * Stock accessor alias for quantity.
     */
    public function getStockAttribute()
    {
        return $this->attributes['quantity'] ?? $this->quantity ?? 0;
    }

    /**
     * Get product variants (different sizes/colors in stock).
     */
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'id');
    }

    /**
     * Get the product's category.
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id', 'id');
    }

    /**
     * Get the product's status.
     */
    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id', 'id');
    }

    /**
     * Get product tags.
     */
    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag', 'product_id', 'product_tag_id');
    }

    /**
     * Get products assigned to homepage sections.
     */
    public function sections()
    {
        return $this->belongsToMany(Section::class, 'product_section');
    }

    /**
     * Get product reviews.
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id', 'id');
    }

    /**
     * Get product images (gallery).
     */
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->orderBy('position');
    }

    /**
     * Get product specifications.
     */
    public function specifications()
    {
        return $this->belongsToMany(
            ProductAttribute::class,
            'product_specifications',
            'product_id',
            'product_attribute_id'
        )->withPivot(['value', 'unit', 'position'])->withTimestamps();
    }

    /**
     * Get product questions/answers.
     */
    public function questions()
    {
        return $this->hasMany(ProductQuestion::class, 'product_id', 'id');
    }

    /**
     * Get related products (same category, different products).
     */
    public function relatedProducts()
    {
        return $this->belongsToMany(
            Product::class,
            'product_related_products',
            'product_id',
            'related_product_id'
        )->withPivot('score')->orderByDesc('score');
    }

    /**
     * Scope: Get only published products.
     */
    public function scopePublished($query)
    {
        return $query->where('product_status_id', 1); // Assuming 1 = published
    }

    /**
     * Scope: Get products by brand.
     */
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    /**
     * Calculate average rating and review count.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total number of reviews.
     */
    public function getReviewCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Check if product is on sale.
     */
    public function getIsOnSaleAttribute()
    {
        return $this->special_price !== null && $this->special_price < $this->price;
    }

    /**
     * Get the best price (special or regular).
     */
    public function getBestPriceAttribute()
    {
        return $this->special_price ?? $this->price;
    }

    /**
     * Increment view count.
     */
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }
}