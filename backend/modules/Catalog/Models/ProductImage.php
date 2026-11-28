<?php

namespace Modules\Catalog\Models;

use App\Services\ImageOptimizationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

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
        if (!$this->path) {
            return null;
        }
        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://')) {
            return $this->path;
        }
        if (file_exists(storage_path('app/public/' . $this->path))) {
            return asset('storage/' . $this->path);
        }
        return $this->product ? $this->product->getTestingImageUrl((int) $this->position) : $this->path;
    }

    public function getUrlsAttribute(): array
    {
        if (!$this->path) {
            return [
                'original'    => null,
                'thumbnail'   => null,
                'small'       => null,
                'medium'      => null,
                'large'       => null,
                'webp_medium' => null,
                'blur'        => null,
            ];
        }

        if (str_starts_with($this->path, 'http://') || str_starts_with($this->path, 'https://') || !file_exists(storage_path('app/public/' . $this->path))) {
            $directUrl = $this->url;
            return [
                'original'    => $directUrl,
                'thumbnail'   => $directUrl,
                'small'       => $directUrl,
                'medium'      => $directUrl,
                'large'       => $directUrl,
                'webp_medium' => $directUrl,
                'blur'        => $directUrl,
            ];
        }

        $service   = app(ImageOptimizationService::class);
        $productId = $this->product_id;
        $filename  = basename($this->path);

        return [
            'original'    => $service->getImageUrl($productId, $filename, 'original'),
            'thumbnail'   => $service->getImageUrl($productId, $filename, 'thumbnail'),
            'small'       => $service->getImageUrl($productId, $filename, 'small'),
            'medium'      => $service->getImageUrl($productId, $filename, 'medium'),
            'large'       => $service->getImageUrl($productId, $filename, 'large'),
            'webp_medium' => $service->getImageUrl($productId, $filename, 'webp_medium'),
            'blur'        => $service->getImageUrl($productId, $filename, 'blur'),
        ];
    }
}
