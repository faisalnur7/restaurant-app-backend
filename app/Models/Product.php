<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model {
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'sub_category_id',
        'price',
        'image',
        'description',
        'status',
        'type',
    ];

    public function category() {
        return $this->belongsTo(Category::class);
    }

    public function subcategory() {
        return $this->belongsTo(SubCategory::class, 'sub_category_id');
    }
}
