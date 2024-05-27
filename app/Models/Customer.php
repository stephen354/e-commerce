<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


/**
 * Class Customer.
 *
 * @author  Donii Sergii <doniysa@gmail.com>
 *
 * @OA\Schema(
 *     title="User model",
 *     description="User model",
 * )
 */
class Customer extends Model implements Authenticatable
{
    protected $table = "customer";
    protected $primaryKey = "id";
    protected $keyType = "int";
    public $timestamps = true;
    public $incrementing = true;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="ID",
     *     title="ID",
     *     example=1,
     * )
     *
     * @var int
     */
    private $id;


    /**
     * @OA\Property(
     *     description="First name",
     *     title="First name",
     *     maximum=100,
     *      example="Stephen"
     * )
     *
     * @var string
     */
    private $firstName;

    /**
     * @OA\Property(
     *     description="Last name",
     *     title="Last name",
     *     maximum=100,
     *      example="Malik"
     * )
     *
     * @var string
     */
    private $lastName;

    /**
     * @OA\Property(
     *     format="email",
     *     description="Email",
     *     title="Email",
     *     maximum=100,
     *     example="example@gmail.com"
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     format="int64",
     *     description="Password",
     *     title="Password",
     *     maximum=255
     * )
     *
     * @var string
     */
    private $password;

    /**
     * @OA\Property(
     *     format="msisdn",
     *     description="Phone",
     *     title="Phone",
     *     example="0895620108861"
     * )
     *
     * @var string
     */
    private $phone;


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
