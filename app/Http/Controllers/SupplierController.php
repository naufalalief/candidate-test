<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Services\NotificationService;
use App\Services\SupplierService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for supplier CRUD operations.
 *
 * Delegates business logic to {@see SupplierService} and notification
 * dispatch to {@see NotificationService}, keeping actions thin.
 */
class SupplierController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  SupplierService      $supplierService
     * @param  NotificationService  $notificationService
     */
    public function __construct(
        private readonly SupplierService $supplierService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Display a paginated listing of suppliers.
     *
     * @return View
     */
    public function index(): View
    {
        $suppliers = $this->supplierService->list(
            search: request('search'),
            sort: request('sort'),
            direction: request('direction', 'asc'),
        );

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new supplier.
     *
     * @return View
     */
    public function create(): View
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created supplier.
     *
     * @param  StoreSupplierRequest  $request
     * @return RedirectResponse
     */
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        $supplier = $this->supplierService->create($request->validated());

        $this->notificationService->notifyUser(
            'created', 'Supplier', $supplier->name, route('suppliers.show', $supplier)
        );

        return redirect()->route('suppliers.index')->with('success', 'Supplier created successfully.');
    }

    /**
     * Display the specified supplier with its layups and layers.
     *
     * @param  Supplier  $supplier
     * @return View
     */
    public function show(Supplier $supplier): View
    {
        $this->supplierService->loadWithRelations($supplier);

        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified supplier.
     *
     * @param  Supplier  $supplier
     * @return View
     */
    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified supplier.
     *
     * @param  StoreSupplierRequest  $request
     * @param  Supplier              $supplier
     * @return RedirectResponse
     */
    public function update(StoreSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $this->supplierService->update($supplier, $request->validated());

        $this->notificationService->notifyUser(
            'updated', 'Supplier', $supplier->name, route('suppliers.show', $supplier)
        );

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified supplier.
     *
     * @param  Supplier  $supplier
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        $name = $supplier->name;
        $this->supplierService->delete($supplier);

        $this->notificationService->notifyUser('deleted', 'Supplier', $name);

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
}
