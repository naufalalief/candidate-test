<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Services\ImportExportService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Controller for importing and exporting supplier/layup/layer data.
 *
 * Delegates all parsing, conflict detection, and data manipulation
 * to {@see ImportExportService}, keeping controller actions thin.
 */
class ImportExportController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  ImportExportService  $importExportService
     */
    public function __construct(
        private readonly ImportExportService $importExportService,
    ) {}

    // ── Export ───────────────────────────────────────────────────────

    /**
     * Export a single supplier's data in the requested format.
     *
     * @param  Request   $request
     * @param  Supplier  $supplier
     * @return mixed
     */
    public function export(Request $request, Supplier $supplier): mixed
    {
        return $this->importExportService->exportSupplier($supplier, $request->query('format', 'json'));
    }

    /**
     * Export all suppliers' data in the requested format.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function exportAll(Request $request): mixed
    {
        return $this->importExportService->exportAll($request->query('format', 'json'));
    }

    // ── Import: Single Supplier ─────────────────────────────────────

    /**
     * Show the import form for a single supplier.
     *
     * @param  Supplier  $supplier
     * @return View
     */
    public function importForm(Supplier $supplier): View
    {
        return view('suppliers.import', compact('supplier'));
    }

    /**
     * Parse the uploaded file, detect conflicts, and either complete the
     * import immediately or show the conflict-resolution page.
     *
     * @param  Request   $request
     * @param  Supplier  $supplier
     * @return RedirectResponse|View
     */
    public function importPreview(Request $request, Supplier $supplier)
    {
        $request->validate(['file' => 'required|file|max:2048']);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!$this->importExportService->isSupportedExtension($extension)) {
            return back()->withErrors(['file' => 'Unsupported file format. Please upload JSON, CSV, or Excel files.']);
        }

        $data = $this->importExportService->parseFile($file, $supplier);

        if ($data === null) {
            return back()->withErrors(['file' => 'Could not parse the file. Please check the format.']);
        }

        if (!isset($data['layups']) || !is_array($data['layups'])) {
            return back()->withErrors(['file' => 'Invalid file structure. Expected layup data.']);
        }

        $conflicts = $this->importExportService->detectConflicts($supplier, $data['layups']);
        $strategy = $request->input('strategy', 'skip');

        if (empty($conflicts)) {
            $this->importExportService->performImport($supplier, $data['layups'], []);

            return redirect()->route('suppliers.show', $supplier)
                ->with('success', 'Import completed successfully. No conflicts detected.');
        }

        if ($strategy !== 'review') {
            $resolutions = $this->importExportService->buildResolutions($conflicts, $strategy);
            $this->importExportService->performImport($supplier, $data['layups'], $resolutions);
            $label = $this->importExportService->strategyLabel($strategy);

            return redirect()->route('suppliers.show', $supplier)
                ->with('success', "Import completed. {$label}");
        }

        session(['import_data' => $data, 'import_supplier_id' => $supplier->id]);

        return view('suppliers.conflicts', compact('supplier', 'conflicts', 'data'));
    }

    /**
     * Apply user-selected conflict resolutions and complete the import.
     *
     * @param  Request   $request
     * @param  Supplier  $supplier
     * @return RedirectResponse
     */
    public function resolveConflicts(Request $request, Supplier $supplier): RedirectResponse
    {
        $data = session('import_data');
        $storedSupplierId = session('import_supplier_id');

        if (!$data || $storedSupplierId !== $supplier->id) {
            return redirect()->route('suppliers.show', $supplier)
                ->withErrors(['import' => 'No pending import found. Please upload the file again.']);
        }

        $this->importExportService->performImport($supplier, $data['layups'], $request->input('resolutions', []));

        session()->forget(['import_data', 'import_supplier_id']);

        return redirect()->route('suppliers.show', $supplier)
            ->with('success', 'Import completed with conflict resolutions applied.');
    }

    // ── Import: All Suppliers ───────────────────────────────────────

    /**
     * Show the bulk import form for all suppliers.
     *
     * @return View
     */
    public function importAllForm(): View
    {
        return view('suppliers.import-all');
    }

    /**
     * Parse the uploaded file for bulk import, detect conflicts, and either
     * complete the import immediately or show the conflict-resolution page.
     *
     * @param  Request  $request
     * @return RedirectResponse|View
     */
    public function importAllPreview(Request $request)
    {
        $request->validate(['file' => 'required|file|max:2048']);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        if (!$this->importExportService->isSupportedExtension($extension)) {
            return back()->withErrors(['file' => 'Unsupported file format. Please upload JSON, CSV, or Excel files.']);
        }

        $data = $this->importExportService->parseAllFile($file);

        if ($data === null) {
            return back()->withErrors(['file' => 'Could not parse the file. Please check the format.']);
        }

        if (!isset($data['suppliers']) || !is_array($data['suppliers']) || empty($data['suppliers'])) {
            return back()->withErrors(['file' => 'Invalid file structure. Expected supplier data.']);
        }

        $allConflicts = $this->importExportService->detectAllConflicts($data['suppliers']);

        if (empty($allConflicts)) {
            $this->importExportService->performAllImport($data['suppliers'], []);

            return redirect()->route('suppliers.index')
                ->with('success', 'Import completed successfully. ' . count($data['suppliers']) . ' supplier(s) processed.');
        }

        session(['import_all_data' => $data]);

        return view('suppliers.conflicts-all', [
            'conflicts' => $allConflicts,
            'data'      => $data,
        ]);
    }

    /**
     * Apply user-selected conflict resolutions and complete the bulk import.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function resolveAllConflicts(Request $request): RedirectResponse
    {
        $data = session('import_all_data');

        if (!$data) {
            return redirect()->route('suppliers.index')
                ->withErrors(['import' => 'No pending import found. Please upload the file again.']);
        }

        $this->importExportService->performAllImport($data['suppliers'], $request->input('resolutions', []));

        session()->forget('import_all_data');

        return redirect()->route('suppliers.index')
            ->with('success', 'Import completed with conflict resolutions applied.');
    }
}
