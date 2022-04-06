<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Model representing a barcode stored for a user in the database.
 *
 * @property int id
 * @property int user_id
 * @property string name
 * @property string data
 * @property string generator
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property ?DateTime deleted_at
 */
class Barcode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "data",
        "generator",
    ];

    /**
     * The tags for the barcode.
     */
    public function tags(): HasManyThrough
    {
        return $this->hasManyThrough(Tag::class, BarcodeTag::class, "barcode_id", "tag_id");
    }
}
