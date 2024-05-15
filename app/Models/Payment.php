<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    protected $table = "payment";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'payment_date',
        'amount',
        'token',
        'status'
    ];

    public function customer(): BelongsTo
    {
        return $this->BelongsTo(Payment::class, "customer_id", "id");
    }
    public function order(): HasMany
    {
        return $this->HasMany(Order::class, "payment_id", "id");
    }
}
