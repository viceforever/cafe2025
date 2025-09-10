<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;
    protected $fillable = ['name_category'];
    public function products()
       {
           return $this->hasMany(Product::class,'id_category');
       }
}
