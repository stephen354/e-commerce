<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $table = "cart";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'quantity',
        'product_id',
        'customer_id'
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Cart::class, "customer_id", "id");
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Cart::class, "product_id", "id");
    }
}
