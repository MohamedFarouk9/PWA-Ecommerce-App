<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $table = 'sections';

    protected $fillable = ['label', 'name'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_section');
    }
}
