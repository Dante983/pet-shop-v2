<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'title', 'slug'];

    // Ensure uuid is casted to string
    protected $casts = [
        'uuid' => 'string',
    ];
}
