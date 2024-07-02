<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'slug',
        'content',
        'metadata'
    ];

    protected $casts = [
        'uuid' => 'string',
        'metadata' => 'array',
    ];
}
