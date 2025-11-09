<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'served_by',
        'table_id',
        'order_type',
        'subtotal',
        'discount',
        'tax',
        'total',
        'due',
        'customer_id',
        'status',
        'payment_status',
        'payment_method',
    ];

    protected $casts = [
        'payment_status' => 'string',
    ];

    // Relationships
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Accessor for total items
    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function table(){
        return $this->belongsTo(Table::class);
    }

}
