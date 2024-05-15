<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $table = "product";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock'
    ];

    public function category(): BelongsTo
    {
        return $this->BelongsTo(Product::class, "category_id", "id");
    }
    public function cart(): HasMany
    {
        return $this->HasMany(Cart::class, "product_id", "id");
    }
    public function order(): HasMany
    {
        return $this->HasMany(Order::class, "product_id", "id");
    }
}
