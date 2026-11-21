<?php

namespace App\Models;

use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;


class Slider extends Model
{
    use HasFactory , UploadTrait;
    protected $fillable = ['image', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Scope to get only active sliders
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getImageAttribute()
    {
        $raw = $this->attributes['image'] ?? null;
        if ($raw && (str_starts_with($raw, 'http://') || str_starts_with($raw, 'https://'))) {
            return $raw;
        }
        if ($raw && File::exists(public_path('storage/images/sliders/' . $raw))) {
            return $this->getimage($raw, 'sliders');
        }

        $banners = [
            'https://images.unsplash.com/photo-1607082348824-0a96f2a4b9da?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?auto=format&fit=crop&w=1600&q=80',
        ];

        return $banners[(($this->id ?? 1) - 1) % count($banners)];
    }


    public function setImageAttribute($value) {
        if ($value != null && is_file($value)) {
            // Delete the old image if it exists
            if (isset($this->attributes['image'])) {
                $this->deleteFile($this->attributes['image'], 'sliders');
            }
            // Upload the new image and set the attribute
            $this->attributes['image'] = $this->uploadAllTypes($value, 'sliders');

        }

    }

}

