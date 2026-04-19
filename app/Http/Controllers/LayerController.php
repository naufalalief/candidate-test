<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLayerRequest;
use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use App\Services\LayerService;
use App\Services\NotificationService;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller for layer CRUD operations within a supplier's layup.
 *
 * Delegates business logic to {@see LayerService} and notification
 * dispatch to {@see NotificationService}.
 */
class LayerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  LayerService         $layerService
     * @param  NotificationService  $notificationService
     */
    public function __construct(
        private readonly LayerService $layerService,
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Display a paginated listing of layers for the layup.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @return View
     */
    public function index(Supplier $supplier, Layup $layup): View
    {
        $layers = $this->layerService->list($layup, request('search'));

        return view('layers.index', compact('supplier', 'layup', 'layers'));
    }

    /**
     * Show the form for creating a new layer.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @return View
     */
    public function create(Supplier $supplier, Layup $layup): View
    {
        return view('layers.create', compact('supplier', 'layup'));
    }

    /**
     * Store a newly created layer.
     *
     * @param  StoreLayerRequest  $request
     * @param  Supplier           $supplier
     * @param  Layup              $layup
     * @return RedirectResponse
     */
    public function store(StoreLayerRequest $request, Supplier $supplier, Layup $layup): RedirectResponse
    {
        $layer = $this->layerService->create($layup, $request->validated());

        $this->notificationService->notifyUser(
            'created', 'Layer', "Layer #{$layer->layer_order}",
            route('suppliers.layups.layers.index', [$supplier, $layup])
        );

        return redirect()->route('suppliers.layups.layers.index', [$supplier, $layup])->with('success', 'Layer created successfully.');
    }

    /**
     * Display the specified layer.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @param  Layer     $layer
     * @return View
     */
    public function show(Supplier $supplier, Layup $layup, Layer $layer): View
    {
        return view('layers.show', compact('supplier', 'layup', 'layer'));
    }

    /**
     * Show the form for editing the specified layer.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @param  Layer     $layer
     * @return View
     */
    public function edit(Supplier $supplier, Layup $layup, Layer $layer): View
    {
        return view('layers.edit', compact('supplier', 'layup', 'layer'));
    }

    /**
     * Update the specified layer.
     *
     * @param  StoreLayerRequest  $request
     * @param  Supplier           $supplier
     * @param  Layup              $layup
     * @param  Layer              $layer
     * @return RedirectResponse
     */
    public function update(StoreLayerRequest $request, Supplier $supplier, Layup $layup, Layer $layer): RedirectResponse
    {
        $this->layerService->update($layer, $request->validated());

        $this->notificationService->notifyUser(
            'updated', 'Layer', "Layer #{$layer->layer_order}",
            route('suppliers.layups.layers.index', [$supplier, $layup])
        );

        return redirect()->route('suppliers.layups.layers.index', [$supplier, $layup])->with('success', 'Layer updated successfully.');
    }

    /**
     * Remove the specified layer.
     *
     * @param  Supplier  $supplier
     * @param  Layup     $layup
     * @param  Layer     $layer
     * @return RedirectResponse
     */
    public function destroy(Supplier $supplier, Layup $layup, Layer $layer): RedirectResponse
    {
        $order = $layer->layer_order;
        $this->layerService->delete($layer);

        $this->notificationService->notifyUser('deleted', 'Layer', "Layer #{$order}");

        return redirect()->route('suppliers.layups.layers.index', [$supplier, $layup])->with('success', 'Layer deleted successfully.');
    }
}
