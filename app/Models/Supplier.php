<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Supplier model representing a CLT material supplier.
 *
 * @property int                              $id
 * @property string                           $name
 * @property \Illuminate\Support\Carbon       $created_at
 * @property \Illuminate\Support\Carbon       $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<Layup> $layups
 */
class Supplier extends Model
{
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = ['name'];

    /**
     * Get the layups belonging to this supplier.
     *
     * @return HasMany<Layup>
     */
    public function layups(): HasMany
    {
        return $this->hasMany(Layup::class);
    }
}
