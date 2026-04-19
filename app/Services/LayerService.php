<?php

namespace App\Services;

use App\Models\Layer;
use App\Models\Layup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service class for managing layer business logic.
 *
 * Handles listing, creation, updating, and deletion of layers
 * within a given layup, including search and pagination.
 */
class LayerService
{
    /**
     * Get a paginated list of layers for a layup with optional search.
     *
     * @param  Layup        $layup    The parent layup.
     * @param  string|null  $search   Search term to filter layers by order, thickness, width, or angle.
     * @param  int          $perPage  Number of results per page.
     * @return LengthAwarePaginator
     */
    public function list(Layup $layup, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $layup->layers();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('layer_order', 'like', "%{$search}%")
                    ->orWhere('thickness', 'like', "%{$search}%")
                    ->orWhere('width', 'like', "%{$search}%")
                    ->orWhere('angle', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('layer_order')->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new layer for a layup.
     *
     * @param  Layup  $layup  The parent layup.
     * @param  array{layer_order: int, thickness: float, width: float, angle: float}  $data  Validated layer data.
     * @return Layer
     */
    public function create(Layup $layup, array $data): Layer
    {
        return $layup->layers()->create($data);
    }

    /**
     * Update an existing layer.
     *
     * @param  Layer  $layer  The layer to update.
     * @param  array{layer_order: int, thickness: float, width: float, angle: float}  $data  Validated layer data.
     * @return Layer
     */
    public function update(Layer $layer, array $data): Layer
    {
        $layer->update($data);

        return $layer;
    }

    /**
     * Delete a layer.
     *
     * @param  Layer  $layer  The layer to delete.
     * @return void
     */
    public function delete(Layer $layer): void
    {
        $layer->delete();
    }
}
