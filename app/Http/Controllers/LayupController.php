<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayupRequest;
use App\Models\Layup;
use App\Models\Supplier;
use App\Services\LayupService;
use App\Services\NotificationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for layup CRUD operations within a supplier.
 *
 * Delegates business logic to {@see LayupService} and notification
 * dispatch to {@see NotificationService}.
 */
class LayupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  LayupService         $layupService
     * @param  NotificationService  $notificationService
     */
    public function __construct(
        private readonly LayupService $layupService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Display a paginated listing of layups for the supplier.
     *
     * @param  Supplier  $supplier
     * @return View
     */
    public function index(Supplier $supplier): View
    {
        $layups = $this->layupService->list($supplier, request('search'));

        return view('layups.index', compact('supplier', 'layups'));
    }

    /**
     * Show the form for creating a new layup.
     *
     * @param  Supplier  $supplier
     * @return View
     */
    public function create(Supplier $supplier): View
    {
        return view('layups.create', compact('supplier'));
    }

    /**
     * Store a newly created layup.
     *
     * @param  StoreLayupRequest  $request
     * @param  Supplier           $supplier
     * @return RedirectResponse
     */
    public function store(StoreLayupRequest $request, Supplier $supplier): RedirectResponse
    {
        $layup = $this->layupService->create($supplier, $request->validated());

        $this->notificationService->notifyUser(
            'created', 'Layup', $layup->name, route('suppliers.layups.show', [$supplier, $layup])
        );

        return redirect()->route('suppliers.layups.index', $supplier)->with('success', 'Layup created successfully.');
    }

    /**
     * Display the specified layup with its layers.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @return View
     */
    public function show(Supplier $supplier, Layup $layup): View
    {
        $layup->load('layers');

        return view('layups.show', compact('supplier', 'layup'));
    }

    /**
     * Show the form for editing the specified layup.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @return View
     */
    public function edit(Supplier $supplier, Layup $layup): View
    {
        return view('layups.edit', compact('supplier', 'layup'));
    }

    /**
     * Update the specified layup.
     *
     * @param  StoreLayupRequest  $request
     * @param  Supplier           $supplier
     * @param  Layup              $layup
     * @return RedirectResponse
     */
    public function update(StoreLayupRequest $request, Supplier $supplier, Layup $layup): RedirectResponse
    {
        $this->layupService->update($layup, $request->validated());

        $this->notificationService->notifyUser(
            'updated', 'Layup', $layup->name, route('suppliers.layups.show', [$supplier, $layup])
        );

        return redirect()->route('suppliers.layups.index', $supplier)->with('success', 'Layup updated successfully.');
    }

    /**
     * Remove the specified layup.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier, Layup $layup): RedirectResponse
    {
        $name = $layup->name;
        $this->layupService->delete($layup);

        $this->notificationService->notifyUser('deleted', 'Layup', $name);

        return redirect()->route('suppliers.layups.index', $supplier)->with('success', 'Layup deleted successfully.');
    }
}
