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

    protected $appends = [
        'title',
        'image_url',
    ];

    /**
     * Get the product's title alias for name.
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    /**
     * Ensure $product->image always returns a full valid URL (external link, storage file, or category/product image link).
     */
    public function getImageAttribute($value)
    {
        if ($value && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
            return $value;
        }

        if ($value && file_exists(storage_path('app/public/' . $value))) {
            return asset('storage/' . $value);
        }

        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            $firstPath = $this->images->first()->path;
            if (str_starts_with($firstPath, 'http://') || str_starts_with($firstPath, 'https://')) {
                return $firstPath;
            }
            if (file_exists(storage_path('app/public/' . $firstPath))) {
                return asset('storage/' . $firstPath);
            }
        }

        return $this->getTestingImageUrl();
    }

    /**
     * Get the product's primary image URL.
     */
    public function getImageUrlAttribute()
    {
        return $this->image;
    }

    /**
     * Curated realistic e-commerce testing image URLs based on product name/category.
     */
    public function getTestingImageUrl(int $index = 0): string
    {
        $name = strtolower(($this->name ?? '') . ' ' . ($this->brand ?? ''));

        $productSpecific = [
            'macbook'    => [
                'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=800&q=80',
            ],
            'galaxy'     => [
                'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1598327105666-5b89351aff97?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80',
            ],
            'iphone'     => [
                'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=800&q=80',
            ],
            'sony'       => [
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=800&q=80',
            ],
            'headphone'  => [
                'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1546435770-a3e426bf472b?auto=format&fit=crop&w=800&q=80',
            ],
            'nike'       => [
                'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1608231387042-66d1773070a5?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80',
            ],
            'dell'       => [
                'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=800&q=80',
            ],
            'ipad'       => [
                'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1585790050230-5dd28404ccb9?auto=format&fit=crop&w=800&q=80',
            ],
            'dyson'      => [
                'https://images.unsplash.com/photo-1558317374-067fb5f30001?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1527515637462-cff94eecc1ac?auto=format&fit=crop&w=800&q=80',
            ],
            'watch'      => [
                'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=800&q=80',
            ],
            'canon'      => [
                'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80',
            ],
            'camera'     => [
                'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=800&q=80',
            ],
            'playstation'=> [
                'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?auto=format&fit=crop&w=800&q=80',
            ],
            'gaming'     => [
                'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=800&q=80',
            ],
            'monitor'    => [
                'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
                'https://images.unsplash.com/photo-1547119957-637f8679db1e?auto=format&fit=crop&w=800&q=80',
            ],
            'lg'         => [
                'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        foreach ($productSpecific as $keyword => $urls) {
            if (str_contains($name, $keyword)) {
                return $urls[$index % count($urls)];
            }
        }

        // General e-commerce product pool (20 distinct high-res product images)
        $pool = [
            'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=800&q=80', // Smart Watch
            'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80', // Wireless Headphones
            'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=800&q=80', // Red Nike Sneakers
            'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=800&q=80', // Polaroid Camera
            'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?auto=format&fit=crop&w=800&q=80', // Perfume Bottle
            'https://images.unsplash.com/photo-1572635196237-14b3f281503f?auto=format&fit=crop&w=800&q=80', // Sunglasses
            'https://images.unsplash.com/photo-1546868871-7041f2a55e12?auto=format&fit=crop&w=800&q=80', // Apple Watch
            'https://images.unsplash.com/photo-1560343090-f0409e92791a?auto=format&fit=crop&w=800&q=80', // Leather Shoes
            'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80', // Backpack
            'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80', // MacBook Pro
            'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=800&q=80', // Smartphone
            'https://images.unsplash.com/photo-1583394838336-acd977736f90?auto=format&fit=crop&w=800&q=80', // Studio Headset
            'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?auto=format&fit=crop&w=800&q=80', // Running Shoes
            'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80', // Monitor
            'https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=800&q=80', // Handbag
            'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=800&q=80', // Tablet
            'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80', // Mechanical Keyboard
            'https://images.unsplash.com/photo-1615663245857-ac93bb7c39e7?auto=format&fit=crop&w=800&q=80', // Wireless Mouse
            'https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=800&q=80', // Graphic T-Shirt
            'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=800&q=80', // Modern Sofa
        ];

        $seed = (($this->id ?? 1) + $index) % count($pool);
        return $pool[$seed];
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