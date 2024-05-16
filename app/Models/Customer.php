<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model implements Authenticatable
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

    public function getAuthIdentifierName()
    {
        return "email";
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->email;
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->token_login;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param  string  $value
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->token_login = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return "token_login";
    }
}
