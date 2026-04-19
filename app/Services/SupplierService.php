<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Service class for managing supplier business logic.
 *
 * Handles listing, creation, updating, and deletion of suppliers,
 * including search filtering and sorting capabilities.
 */
class SupplierService
{
    /**
     * Get a paginated list of suppliers with optional search and sort.
     *
     * @param  string|null  $search     Search term to filter suppliers by name.
     * @param  string|null  $sort       Sort field: 'name', 'layups', or 'created'.
     * @param  string       $direction  Sort direction: 'asc' or 'desc'.
     * @param  int          $perPage    Number of results per page.
     * @return LengthAwarePaginator
     */
    public function list(?string $search = null, ?string $sort = null, string $direction = 'asc', int $perPage = 15): LengthAwarePaginator
    {
        $query = Supplier::withCount('layups');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($sort) {
            match ($sort) {
                'name' => $query->orderBy('name', $direction),
                'layups' => $query->orderBy('layups_count', $direction),
                'created' => $query->orderBy('created_at', $direction),
                default => $query->latest(),
            };
        } else {
            $query->latest();
        }

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Create a new supplier.
     *
     * @param  array{name: string}  $data  Validated supplier data.
     * @return Supplier
     */
    public function create(array $data): Supplier
    {
        return Supplier::create($data);
    }

    /**
     * Update an existing supplier.
     *
     * @param  Supplier              $supplier  The supplier to update.
     * @param  array{name: string}   $data      Validated supplier data.
     * @return Supplier
     */
    public function update(Supplier $supplier, array $data): Supplier
    {
        $supplier->update($data);

        return $supplier;
    }

    /**
     * Delete a supplier and all its associated layups and layers.
     *
     * @param  Supplier  $supplier  The supplier to delete.
     * @return void
     */
    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    /**
     * Load a supplier with its layups and layers eager-loaded.
     *
     * @param  Supplier  $supplier
     * @return Supplier
     */
    public function loadWithRelations(Supplier $supplier): Supplier
    {
        return $supplier->load('layups.layers');
    }
}
