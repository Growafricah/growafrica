<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\UUID;

class OrderItem extends Model
{
    use HasFactory,UUID;

    protected $fillable = [

        'order_id',
        'seller_id',
        'buyer_id',
        'product_id',
        'quantity',
        'unit_price',
        'total',
        'status'

    ];

    public function seller():BelongsTo
    {
        return $this->belongsTo(User::class,'seller_id');
    }

    public function buyer():BelongsTo
    {
        return $this->belongsTo(User::class,'buyer_id');
    }

    public function product():BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function order():BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    
}
