<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\UUID;

class Category extends Model
{
    use HasFactory,UUID;

    protected $fillable = [
        'name',
        'image'
    ];


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
