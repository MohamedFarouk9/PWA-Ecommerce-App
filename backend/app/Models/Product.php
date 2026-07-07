<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'product_category_id',
        'price',
        'quantity',
        'product_status_id',
    ];

    public function reviews()
    {
        return $this->hasMany(ProductReview::class , 'product_id' , 'id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->orderBy('position');
    }

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

    // This allows each product to have multiple versions/variants.
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class , 'product_id' , 'id');
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id' , 'id');
    }

    // pivot table
    public function tags()
    {
        return $this->belongsToMany(ProductTag::class, 'product_tag', 'product_id', 'product_tag_id');
    }

    public function sections()
    {
        return $this->belongsToMany(Section::class, 'product_section');
    }

    public function status()
    {
        return $this->belongsTo(ProductStatus::class, 'product_status_id');
    }

    public function registerMediaCollections(): void
    {
        // No-op: media collections removed. Images are stored in `image` column.
    }
}
