<?php
namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;

class Product extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'products';

    protected $fillable = ['name','description','price','stock','category','images'];
    protected $casts = ['price'=>'float','stock'=>'int','images'=>'array'];
}
