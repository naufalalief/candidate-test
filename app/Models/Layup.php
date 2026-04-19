<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Layup model representing a CLT layup configuration within a supplier.
 *
 * @property int                              $id
 * @property int                              $supplier_id
 * @property string                           $name
 * @property \Illuminate\Support\Carbon       $created_at
 * @property \Illuminate\Support\Carbon       $updated_at
 * @property-read Supplier                    $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<Layer> $layers
 */
class Layup extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'clt_layups';

    /** @var array<int, string> */
    protected $fillable = ['supplier_id', 'name'];

    /**
     * Get the supplier that owns this layup.
     *
     * @return BelongsTo<Supplier, Layup>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the layers belonging to this layup.
     *
     * @return HasMany<Layer>
     */
    public function layers(): HasMany
    {
        return $this->hasMany(Layer::class);
    }
}
