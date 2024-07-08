<?php

namespace App\Models;

use App\Models\Brand;
use App\Models\Categories;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'category_uuid',
        'title',
        'price',
        'description',
        'metadata'
    ];

    protected $casts = [
        'uuid' => 'string',
        'metadata' => 'json',
    ];

    /**
     * Get the category that the product belongs to.
     */
    public function category()
    {
        return $this->belongsTo(Categories::class, 'category_uuid', 'uuid');
    }
}
