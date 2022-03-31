<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model class for records in the tags table.
 *
 * @property string $tag
 * @property ?DateTime $archived_at
 * @property DateTime created_at
 * @property DateTime updated_at
 * @property ?DateTime deleted_at
 */
class Tag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "tag",
        "archived_at",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "archived_at" => "datetime",
    ];

    /**
     * Determine whether the tag has been archived.
     *
     * @return bool true if the tag is archived, false otherwise.
     */
    public function isArchived(): bool
    {
        return null !== $this->archived_at;
    }
}
