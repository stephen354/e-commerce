<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    protected $table = "rating";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'rate'
    ];

    public function customer(): BelongsTo
    {
        return $this->BelongsTo(Rating::class, "customer_id", "id");
    }
    public function order(): BelongsTo
    {
        return $this->BelongsTo(Rating::class, "order_id", "id");
    }
}
