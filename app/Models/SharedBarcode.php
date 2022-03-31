<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model class for records in the shared_barcodes table.
 *
 * @property int $original_barcode_id
 * @property string $data
 * @property string $generator
 * @property DateTime $expires_at
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property ?DateTime deleted_at
 */
class SharedBarcode extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "data",
        "generator",
        "expires_at",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "expires_at" => "datetime",
    ];

    /**
     * Determine whether the barcode was shared from an original stored barcode for a user.
     *
     * @return bool true if the shared barcode links to an original barcode stored by a user, false if not.
     */
    protected function hasOriginal(): bool
    {
        return null !== $this->original_barcode_id;
    }
}
