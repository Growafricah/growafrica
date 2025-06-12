<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\UUID;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable,UUID;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [

        'first_name',
        'last_name',
        'store_category',
        'pic',
        'gender',
        'country',
        'address',
        'store_link',
        'store_status',
        'email',
        'phone',
        'role',
        'business_name',
        'business_state',
        'business_city',
        'id_doc',
        'status',
        'password',
        'kyc_status',
        'verification_code',
        'verification_expiry',
        'bank_name',
        'account_name',
        'account_number',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'verification_expiry',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'boolean',
    ];


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
