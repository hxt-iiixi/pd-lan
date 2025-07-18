<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\SalesItem;
class Sale extends Model
{
   protected $fillable = ['total_price', 'discount_type', 'discount_amount'];

public function items()
    {
        return $this->hasMany(SalesItem::class);
    }

    public function getFormattedDiscountAttribute()
        {
            return match($this->discount_type) {
                'SC' => 'Senior Citizen (20%)',
                'PWD' => 'PWD (20%)',
                default => 'None',
            };
        }

    public function setDiscountTypeAttribute($value)
    {
        $this->attributes['discount_type'] = strtoupper($value ?? 'NONE');
    }
}
