<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Layer model representing a single material layer within a CLT layup.
 *
 * @property int                        $id
 * @property int                        $layup_id
 * @property int                        $layer_order
 * @property float                      $thickness
 * @property float                      $width
 * @property float                      $angle
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Layup                 $layup
 */
class Layer extends Model
{
    use HasFactory;

    /** @var string */
    protected $table = 'clt_layers';

    /** @var array<int, string> */
    protected $fillable = ['layup_id', 'layer_order', 'thickness', 'width', 'angle'];

    /**
     * Get the layup that owns this layer.
     *
     * @return BelongsTo<Layup, Layer>
     */
    public function layup(): BelongsTo
    {
        return $this->belongsTo(Layup::class);
    }
}
