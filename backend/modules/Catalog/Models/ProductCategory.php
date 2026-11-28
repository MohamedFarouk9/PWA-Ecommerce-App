<?php

namespace Modules\Catalog\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory, UploadTrait;

    protected $table = 'product_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'image',
        'is_active',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    public function getImageAttribute()
    {
        $raw = $this->attributes['image'] ?? null;
        if ($raw && (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://'))) {
            return $raw;
        }
        if ($raw && File::exists(public_path('storage/images/categories/' . $raw))) {
            return $this->getimage($raw, 'categories');
        }
        if ($raw && File::exists(storage_path('app/public/' . $raw))) {
            return asset('storage/' . $raw);
        }

        $slug = strtolower($this->attributes['slug'] ?? $this->attributes['name'] ?? '');
        $categoryImages = [
            'electronics'         => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?auto=format&fit=crop&w=600&q=80',
            'smartphones'         => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=600&q=80',
            'laptops'             => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=600&q=80',
            'audio'               => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=600&q=80',
            'gaming'              => 'https://images.unsplash.com/photo-1606813907291-d86efa9b94db?auto=format&fit=crop&w=600&q=80',
            'clothing'            => 'https://images.unsplash.com/photo-1445205170230-053b83016050?auto=format&fit=crop&w=600&q=80',
            'mens-fashion'        => 'https://images.unsplash.com/photo-1490578474895-699cd4e2cf59?auto=format&fit=crop&w=600&q=80',
            'womens-fashion'      => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=600&q=80',
            'kids-fashion'        => 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?auto=format&fit=crop&w=600&q=80',
            'footwear'            => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=600&q=80',
            'home-kitchen'        => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=600&q=80',
            'furniture'           => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?auto=format&fit=crop&w=600&q=80',
            'kitchen-appliances'  => 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=600&q=80',
            'home-decor'          => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=600&q=80',
            'bedding'             => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=600&q=80',
            'books'               => 'https://images.unsplash.com/photo-1495446815901-a7297e633e8d?auto=format&fit=crop&w=600&q=80',
            'sports-outdoors'     => 'https://images.unsplash.com/photo-1517649763962-0c623066013b?auto=format&fit=crop&w=600&q=80',
            'fitness'             => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?auto=format&fit=crop&w=600&q=80',
        ];

        foreach ($categoryImages as $key => $url) {
            if (str_contains($slug, $key)) {
                return $url;
            }
        }

        $pool = array_values($categoryImages);
        return $pool[($this->id ?? 0) % count($pool)];
    }

    public function setImageAttribute($value)
    {
        if ($value != null && is_file($value)) {
            if (isset($this->attributes['image'])) {
                $this->deleteFile($this->attributes['image'], 'categories');
            }
            $this->attributes['image'] = $this->uploadAllTypes($value, 'categories');
        }
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function parent()
    {
        return $this->belongsTo(ProductCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ProductCategory::class, 'parent_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'product_category_id');
    }

    public function ancestors()
    {
        $ancestors = collect();
        $category  = $this;

        while ($category->parent) {
            $category = $category->parent;
            $ancestors->push($category);
        }
        return $ancestors;
    }

    public function descendants()
    {
        $descendants = collect();

        foreach ($this->children()->get() as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }
        return $descendants;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getFullPathAttribute()
    {
        $path     = collect([$this->name]);
        $category = $this;

        while ($category->parent) {
            $category = $category->parent;
            $path->prepend($category->name);
        }
        return $path->implode(' > ');
    }
}
