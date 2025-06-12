<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UUID;

class Product extends Model
{
    use HasFactory,UUID;

    protected $fillable = [

        'category_id',
        'seller_id',
        'name',
        'unit_price',
        'weight',
        'inventory',
        'description',
        'specification',
        'color',
        'sales_number',
        'discount',
        'vat',
        'rating',
        'status',
        'images',
        'thumbnail',
    ];


    protected $casts = [

        'status' => 'boolean',
        'images' => 'array',

    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems() :HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id');
    }

    public function carts() :HasMany
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function reviews() :HasMany
    {
        return $this->hasMany(Review::class, 'product_id');
    }

    public function seller() :BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }


}
