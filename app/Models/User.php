<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * Model class for records in the users table.
 *
 * @property int id
 * @property string name
 * @property string username
 * @property string email
 * @property string password
 * @property string remember_token
 * @property ?DateTime email_verified_at
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property ?DateTime deleted_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "username",
        "email",
        "password",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        "password",
        "remember_token",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    /**
     * The user's stored barcodes.
     *
     * @return HasMany
     */
    public function barcodes(): HasMany
    {
        return $this->hasMany(Barcode::class, "user_id");
    }

    /**
     * The user's tags.
     *
     * @return HasMany
     */
    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class, "user_id");
    }
}
