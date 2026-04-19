<?php

namespace App\Services;

use App\Models\Layup;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service class for managing layup business logic.
 *
 * Handles listing, creation, updating, and deletion of layups
 * within a given supplier, including search and pagination.
 */
class LayupService
{
    /**
     * Get a paginated list of layups for a supplier with optional search.
     *
     * @param  Supplier     $supplier  The parent supplier.
     * @param  string|null  $search    Search term to filter layups by name.
     * @param  int          $perPage   Number of results per page.
     * @return LengthAwarePaginator
     */
    public function list(Supplier $supplier, ?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = $supplier->layups()->withCount('layers');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new layup for a supplier.
     *
     * @param  Supplier            $supplier  The parent supplier.
     * @param  array{name: string} $data      Validated layup data.
     * @return Layup
     */
    public function create(Supplier $supplier, array $data): Layup
    {
        return $supplier->layups()->create($data);
    }

    /**
     * Update an existing layup.
     *
     * @param  Layup               $layup  The layup to update.
     * @param  array{name: string} $data   Validated layup data.
     * @return Layup
     */
    public function update(Layup $layup, array $data): Layup
    {
        $layup->update($data);

        return $layup;
    }

    /**
     * Delete a layup and all its associated layers.
     *
     * @param  Layup  $layup  The layup to delete.
     * @return void
     */
    public function delete(Layup $layup): void
    {
        $layup->delete();
    }
}
