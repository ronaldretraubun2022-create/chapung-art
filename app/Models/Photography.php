<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photography extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'description',
        'photographer_name',
        'location',
        'camera',
        'price',
        'status',
        'thumbnail',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
    ];
}
