<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model representing a link record joining barcode to tags stored for a user in the database.
 *
 * @property int barcode_id
 * @property int tag_id
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property ?DateTime deleted_at
 */
class BarcodeTag extends Model
{
    use HasFactory;

    /**
     * The relationship between BarcodeTags and Barcodes.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function barcode(): HasOne
    {
        return $this->hasOne(Barcode::class);
    }

    /**
     * The relationship between BarcodeTags and Tags.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function tag(): HasOne
    {
        return $this->hasOne(Tag::class);
    }
}
