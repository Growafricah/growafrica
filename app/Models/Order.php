<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UUID;

class Order extends Model
{
    use HasFactory,UUID;

    protected $fillable = [

        'user_id',
        'txn_id',
        'address',
        'status',
        'items_count',
        'delivery_fee',
        'sub_total',
        'total_amount'

    ];

    public function user() :BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems() :HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions() :HasMany
    {
        return $this->hasMany(Transaction::class);
    }

}
