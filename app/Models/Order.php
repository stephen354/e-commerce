<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = "order";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'quantity',
        'price',
        'product_id',
        'payment_id'
    ];

    public function payment(): BelongsTo
    {
        return $this->BelongsTo(Order::class, "payment_id", "id");
    }

    public function rating(): BelongsTo
    {
        return $this->BelongsTo(Rating::class, "order_id", "id");
    }
    public function product(): BelongsTo
    {
        return $this->BelongsTo(Order::class, "product_id", "id");
    }
}
