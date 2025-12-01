<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected string $collection = 'products';

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category',
        'images',
        'creationDate',
    ];

    protected $casts = [
        'price' => 'float',
        'stock' => 'integer',
        // Use array (not json) because Mongo returns native arrays
        'images' => 'array',
        'creationDate' => 'datetime',
    ];
}
