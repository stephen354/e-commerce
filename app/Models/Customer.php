<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $table = "customer";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'address',
        'phone'
    ];

    public function cart(): HasMany
    {
        return $this->hasMany(Cart::class, "customer_id", "id");
    }
    public function rating(): HasMany
    {
        return $this->hasMany(Rating::class, "customer_id", "id");
    }
    public function payment(): HasMany
    {
        return $this->hasMany(Payment::class, "customer_id", "id");
    }
}
