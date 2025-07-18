<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class SalesItem extends Model
{
    protected $fillable = [
        'sale_id',     // FK to sales table
        'product_id',  // FK to products table
        'quantity',
        'total_price'
    ];
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
