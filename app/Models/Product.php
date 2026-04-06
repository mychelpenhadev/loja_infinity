<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'external_id',
        'name',
        'description',
        'price',
        'original_price',
        'discount_percent',
        'category',
        'brand',
        'image',
        'video',
        'rating',
        'stock_quantity',
        'sold_quantity',
    ];

    protected $appends = ['image_url'];
    
    /**
     * Get the product image URL or base64 data.
     *
     * @return string
     */
    public function getImageUrlAttribute()
    {
        if (!$this->image) {
            return asset('assets/img/no-image.png'); // Fallback if no image
        }

        if (str_starts_with($this->image, 'data:image/')) {
            return $this->image;
        }

        return asset($this->image);
    }
}
