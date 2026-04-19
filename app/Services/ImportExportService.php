<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Service class for all import and export business logic.
 *
 * Handles file parsing (JSON, CSV, Excel), data export in multiple formats,
 * conflict detection between imported and existing data, and transactional
 * import execution with configurable conflict-resolution strategies.
 */
class ImportExportService
{
    // ── Export: Single Supplier ──────────────────────────────────────

    /**
     * Export a single supplier's data in the requested format.
     *
     * @param  Supplier  $supplier  The supplier (with layups.layers eager-loaded).
     * @param  string    $format    Export format: 'json', 'csv', or 'excel'.
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function exportSupplier(Supplier $supplier, string $format): mixed
    {
        $supplier->load('layups.layers');
        $baseName = 'supplier_' . $supplier->id . '_' . str_replace(' ', '_', strtolower($supplier->name));

        return match ($format) {
            'csv'   => $this->exportCsv($supplier, $baseName),
            'excel' => $this->exportExcel($supplier, $baseName),
            default => $this->exportJson($supplier, $baseName),
        };
    }

    /**
     * Export all suppliers' data in the requested format.
     *
     * @param  string  $format  Export format: 'json', 'csv', or 'excel'.
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\Response
     */
    public function exportAll(string $format): mixed
    {
        $suppliers = Supplier::with('layups.layers')->get();
        $baseName = 'all_suppliers';

        return match ($format) {
            'csv'   => $this->exportAllCsv($suppliers, $baseName),
            'excel' => $this->exportAllExcel($suppliers, $baseName),
            default => $this->exportAllJson($suppliers, $baseName),
        };
    }

    // ── Import: File Parsing ────────────────────────────────────────

    /**
     * Parse an uploaded file for single-supplier import.
     *
     * @param  UploadedFile  $file      The uploaded file.
     * @param  Supplier      $supplier  The target supplier (used for CSV/Excel context).
     * @return array|null  Parsed data array with 'layups' key, or null on failure.
     */
    public function parseFile(UploadedFile $file, Supplier $supplier): ?array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match (true) {
            in_array($extension, ['json', 'txt']) => $this->parseJson($file),
            $extension === 'csv'                  => $this->parseCsv($file, $supplier),
            in_array($extension, ['xls', 'xlsx']) => $this->parseExcel($file, $supplier),
            default                               => null,
        };
    }

    /**
     * Parse an uploaded file for bulk (all-suppliers) import.
     *
     * @param  UploadedFile  $file  The uploaded file.
     * @return array|null  Parsed data array with 'suppliers' key, or null on failure.
     */
    public function parseAllFile(UploadedFile $file): ?array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        return match (true) {
            in_array($extension, ['json', 'txt']) => $this->parseAllJson($file),
            $extension === 'csv'                  => $this->parseAllCsv($file),
            in_array($extension, ['xls', 'xlsx']) => $this->parseAllExcel($file),
            default                               => null,
        };
    }

    /**
     * Check whether a file extension is supported for import.
     *
     * @param  string  $extension  Lowercase file extension.
     * @return bool
     */
    public function isSupportedExtension(string $extension): bool
    {
        return in_array($extension, ['json', 'txt', 'csv', 'xls', 'xlsx']);
    }

    // ── Import: Conflict Detection ──────────────────────────────────

    /**
     * Detect conflicts between imported layups/layers and existing data.
     *
     * @param  Supplier  $supplier      The target supplier.
     * @param  array     $importLayups  Array of layup data from the imported file.
     * @return array  List of conflict descriptors.
     */
    public function detectConflicts(Supplier $supplier, array $importLayups): array
    {
        $conflicts = [];

        foreach ($importLayups as $layupIndex => $importLayup) {
            $existingLayup = $supplier->layups()->where('name', $importLayup['name'])->first();

            if (!$existingLayup || !isset($importLayup['layers']) || empty($importLayup['layers'])) {
                continue;
            }

            foreach ($importLayup['layers'] as $layerIndex => $importLayer) {
                $existingLayer = $existingLayup->layers()
                    ->where('layer_order', $importLayer['layer_order'])
                    ->first();

                if (!$existingLayer) {
                    continue;
                }

                $diffs = [];
                $identical = true;

                foreach (['thickness', 'width', 'angle'] as $field) {
                    if ((float) $existingLayer->$field !== (float) $importLayer[$field]) {
                        $diffs[$field] = [
                            'existing' => (float) $existingLayer->$field,
                            'incoming' => (float) $importLayer[$field],
                        ];
                        $identical = false;
                    }
                }

                $conflicts[] = [
                    'layup_index'       => $layupIndex,
                    'layer_index'       => $layerIndex,
                    'layup_name'        => $importLayup['name'],
                    'layer_order'       => $importLayer['layer_order'],
                    'existing_layer_id' => $existingLayer->id,
                    'identical'         => $identical,
                    'diffs'             => $diffs,
                    'existing'          => [
                        'thickness' => (float) $existingLayer->thickness,
                        'width'     => (float) $existingLayer->width,
                        'angle'     => (float) $existingLayer->angle,
                    ],
                    'incoming' => [
                        'thickness' => (float) $importLayer['thickness'],
                        'width'     => (float) $importLayer['width'],
                        'angle'     => (float) $importLayer['angle'],
                    ],
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Detect conflicts across all suppliers in a bulk import.
     *
     * @param  array  $importSuppliers  Array of supplier data from the imported file.
     * @return array  List of conflict descriptors (with supplier_index/supplier_name added).
     */
    public function detectAllConflicts(array $importSuppliers): array
    {
        $allConflicts = [];

        foreach ($importSuppliers as $supplierIndex => $supplierData) {
            $existingSupplier = Supplier::where('name', $supplierData['name'])->first();

            if (!$existingSupplier || !isset($supplierData['layups'])) {
                continue;
            }

            $conflicts = $this->detectConflicts($existingSupplier, $supplierData['layups']);

            foreach ($conflicts as &$conflict) {
                $conflict['supplier_index'] = $supplierIndex;
                $conflict['supplier_name'] = $supplierData['name'];
            }

            $allConflicts = array_merge($allConflicts, $conflicts);
        }

        return $allConflicts;
    }

    // ── Import: Execution ───────────────────────────────────────────

    /**
     * Build resolution map from a list of conflicts using a bulk strategy.
     *
     * @param  array   $conflicts  Conflict descriptors.
     * @param  string  $strategy   Strategy: 'overwrite', 'skip', or 'keep'.
     * @return array  Keyed resolutions (e.g. ['0_1' => 'incoming']).
     */
    public function buildResolutions(array $conflicts, string $strategy): array
    {
        $resolutions = [];

        foreach ($conflicts as $conflict) {
            $key = $conflict['layup_index'] . '_' . $conflict['layer_index'];
            $resolutions[$key] = match ($strategy) {
                'overwrite' => 'incoming',
                default     => 'existing',
            };
        }

        return $resolutions;
    }

    /**
     * Get a human-readable label for the applied import strategy.
     *
     * @param  string  $strategy  The strategy that was applied.
     * @return string
     */
    public function strategyLabel(string $strategy): string
    {
        return match ($strategy) {
            'overwrite' => 'All conflicts overwritten with imported data.',
            'keep'      => 'Existing data kept for all conflicts.',
            default     => 'Conflicting layers skipped.',
        };
    }

    /**
     * Perform the import for a single supplier inside a DB transaction.
     *
     * @param  Supplier  $supplier      The target supplier.
     * @param  array     $importLayups  Layup data from the imported file.
     * @param  array     $resolutions   Conflict resolution map.
     * @return void
     */
    public function performImport(Supplier $supplier, array $importLayups, array $resolutions): void
    {
        DB::transaction(function () use ($supplier, $importLayups, $resolutions) {
            foreach ($importLayups as $layupIndex => $importLayup) {
                $existingLayup = $supplier->layups()->where('name', $importLayup['name'])->first();

                if ($existingLayup && $this->shouldDuplicate($resolutions, $layupIndex)) {
                    $this->createDuplicateLayup($supplier, $importLayup);
                    continue;
                }

                if (!$existingLayup) {
                    $existingLayup = $supplier->layups()->create(['name' => $importLayup['name']]);
                }

                if (!isset($importLayup['layers'])) {
                    continue;
                }

                $this->importLayers($existingLayup, $importLayup['layers'], $resolutions, $layupIndex);
            }
        });
    }

    /**
     * Perform the bulk import for multiple suppliers inside a DB transaction.
     *
     * @param  array  $importSuppliers  Array of supplier data from the imported file.
     * @param  array  $resolutions      Global conflict resolution map.
     * @return void
     */
    public function performAllImport(array $importSuppliers, array $resolutions): void
    {
        DB::transaction(function () use ($importSuppliers, $resolutions) {
            foreach ($importSuppliers as $supplierIndex => $supplierData) {
                $supplier = Supplier::firstOrCreate(['name' => $supplierData['name']]);

                if (!isset($supplierData['layups'])) {
                    continue;
                }

                $supplierResolutions = $this->extractSupplierResolutions($resolutions, $supplierIndex);
                $this->performImport($supplier, $supplierData['layups'], $supplierResolutions);
            }
        });
    }

    // ── Private: Export Helpers ──────────────────────────────────────

    /**
     * Export a single supplier as JSON.
     *
     * @param  Supplier  $supplier
     * @param  string    $baseName
     * @return \Illuminate\Http\JsonResponse
     */
    private function exportJson(Supplier $supplier, string $baseName)
    {
        $data = [
            'supplier' => ['name' => $supplier->name],
            'layups'   => $supplier->layups->map(fn($layup) => [
                'name'   => $layup->name,
                'layers' => $layup->layers->map(fn($layer) => [
                    'layer_order' => $layer->layer_order,
                    'thickness'   => (float) $layer->thickness,
                    'width'       => (float) $layer->width,
                    'angle'       => (float) $layer->angle,
                ])->toArray(),
            ])->toArray(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $baseName . '.json"');
    }

    /**
     * Export a single supplier as CSV.
     *
     * @param  Supplier  $supplier
     * @param  string    $baseName
     * @return StreamedResponse
     */
    private function exportCsv(Supplier $supplier, string $baseName): StreamedResponse
    {
        $rows = $this->buildCsvRows($supplier->layups, $supplier->name);

        return $this->streamCsv($rows, $baseName);
    }

    /**
     * Export a single supplier as Excel XML.
     *
     * @param  Supplier  $supplier
     * @param  string    $baseName
     * @return \Illuminate\Http\Response
     */
    private function exportExcel(Supplier $supplier, string $baseName)
    {
        $rows = [];

        foreach ($supplier->layups as $layup) {
            if ($layup->layers->isEmpty()) {
                $rows[] = [$supplier->name, $layup->name, '', '', '', ''];
            } else {
                foreach ($layup->layers as $layer) {
                    $rows[] = [$supplier->name, $layup->name, $layer->layer_order, $layer->thickness, $layer->width, $layer->angle];
                }
            }
        }

        return $this->buildExcelResponse($rows, 'Layups', $baseName);
    }

    /**
     * Export all suppliers as JSON.
     *
     * @param  Collection  $suppliers
     * @param  string      $baseName
     * @return \Illuminate\Http\JsonResponse
     */
    private function exportAllJson(Collection $suppliers, string $baseName)
    {
        $data = [
            'suppliers' => $suppliers->map(fn($supplier) => [
                'name'   => $supplier->name,
                'layups' => $supplier->layups->map(fn($layup) => [
                    'name'   => $layup->name,
                    'layers' => $layup->layers->map(fn($layer) => [
                        'layer_order' => $layer->layer_order,
                        'thickness'   => (float) $layer->thickness,
                        'width'       => (float) $layer->width,
                        'angle'       => (float) $layer->angle,
                    ])->toArray(),
                ])->toArray(),
            ])->toArray(),
        ];

        return response()->json($data)
            ->header('Content-Disposition', 'attachment; filename="' . $baseName . '.json"');
    }

    /**
     * Export all suppliers as CSV.
     *
     * @param  Collection  $suppliers
     * @param  string      $baseName
     * @return StreamedResponse
     */
    private function exportAllCsv(Collection $suppliers, string $baseName): StreamedResponse
    {
        $rows = [['Supplier', 'Layup', 'Layer Order', 'Thickness', 'Width', 'Angle']];

        foreach ($suppliers as $supplier) {
            $rows = array_merge($rows, $this->buildCsvDataRows($supplier->layups, $supplier->name));
        }

        return $this->streamCsvRaw($rows, $baseName);
    }

    /**
     * Export all suppliers as Excel XML.
     *
     * @param  Collection  $suppliers
     * @param  string      $baseName
     * @return \Illuminate\Http\Response
     */
    private function exportAllExcel(Collection $suppliers, string $baseName)
    {
        $rows = [];

        foreach ($suppliers as $supplier) {
            foreach ($supplier->layups as $layup) {
                if ($layup->layers->isEmpty()) {
                    $rows[] = [$supplier->name, $layup->name, '', '', '', ''];
                } else {
                    foreach ($layup->layers as $layer) {
                        $rows[] = [$supplier->name, $layup->name, $layer->layer_order, $layer->thickness, $layer->width, $layer->angle];
                    }
                }
            }
        }

        return $this->buildExcelResponse($rows, 'Suppliers', $baseName);
    }

    /**
     * Build CSV rows for a supplier's layups (with header).
     *
     * @param  mixed   $layups
     * @param  string  $supplierName
     * @return array
     */
    private function buildCsvRows($layups, string $supplierName): array
    {
        $rows = [['Supplier', 'Layup', 'Layer Order', 'Thickness', 'Width', 'Angle']];

        return array_merge($rows, $this->buildCsvDataRows($layups, $supplierName));
    }

    /**
     * Build CSV data rows (without header) for a set of layups.
     *
     * @param  mixed   $layups
     * @param  string  $supplierName
     * @return array
     */
    private function buildCsvDataRows($layups, string $supplierName): array
    {
        $rows = [];

        foreach ($layups as $layup) {
            if ($layup->layers->isEmpty()) {
                $rows[] = [$supplierName, $layup->name, '', '', '', ''];
            } else {
                foreach ($layup->layers as $layer) {
                    $rows[] = [$supplierName, $layup->name, $layer->layer_order, $layer->thickness, $layer->width, $layer->angle];
                }
            }
        }

        return $rows;
    }

    /**
     * Stream rows as a CSV download response.
     *
     * @param  array   $rows
     * @param  string  $baseName
     * @return StreamedResponse
     */
    private function streamCsv(array $rows, string $baseName): StreamedResponse
    {
        return $this->streamCsvRaw($rows, $baseName);
    }

    /**
     * Stream raw CSV rows as a download response.
     *
     * @param  array   $rows
     * @param  string  $baseName
     * @return StreamedResponse
     */
    private function streamCsvRaw(array $rows, string $baseName): StreamedResponse
    {
        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $baseName . '.csv"',
        ]);
    }

    /**
     * Build an Excel XML Spreadsheet response from data rows.
     *
     * @param  array   $dataRows       Row data (without header).
     * @param  string  $worksheetName  Name for the worksheet tab.
     * @param  string  $baseName       Base filename (without extension).
     * @return \Illuminate\Http\Response
     */
    private function buildExcelResponse(array $dataRows, string $worksheetName, string $baseName)
    {
        $headers = ['Supplier', 'Layup', 'Layer Order', 'Thickness', 'Width', 'Angle'];

        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Styles><Style ss:ID="header"><Font ss:Bold="1"/></Style></Styles>' . "\n";
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($worksheetName) . '"><Table>' . "\n";

        $xml .= '<Row ss:StyleID="header">';
        foreach ($headers as $h) {
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($h) . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";

        foreach ($dataRows as $row) {
            $xml .= '<Row>';
            foreach ($row as $i => $value) {
                $type = ($i >= 2 && $value !== '') ? 'Number' : 'String';
                $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars((string) $value) . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return response($xml, 200, [
            'Content-Type'        => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $baseName . '.xls"',
        ]);
    }

    // ── Private: File Parsing Helpers ────────────────────────────────

    /**
     * Parse a JSON file for single-supplier import.
     *
     * @param  UploadedFile  $file
     * @return array|null
     */
    private function parseJson(UploadedFile $file): ?array
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($data['layups'])) {
            return null;
        }

        return $data;
    }

    /**
     * Parse a CSV file for single-supplier import.
     *
     * @param  UploadedFile  $file
     * @param  Supplier      $supplier
     * @return array|null
     */
    private function parseCsv(UploadedFile $file, Supplier $supplier): ?array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return null;
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 4) {
            fclose($handle);
            return null;
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $layupCol     = $this->findColumn($header, ['layup', 'layup_name', 'layup name']);
        $orderCol     = $this->findColumn($header, ['layer_order', 'layer order', 'order']);
        $thicknessCol = $this->findColumn($header, ['thickness']);
        $widthCol     = $this->findColumn($header, ['width']);
        $angleCol     = $this->findColumn($header, ['angle']);

        if ($layupCol === null || $orderCol === null) {
            fclose($handle);
            return null;
        }

        $layups = [];

        while (($row = fgetcsv($handle)) !== false) {
            $layupName = trim($row[$layupCol] ?? '');
            if (empty($layupName)) {
                continue;
            }

            if (!isset($layups[$layupName])) {
                $layups[$layupName] = ['name' => $layupName, 'layers' => []];
            }

            $order = trim($row[$orderCol] ?? '');
            if ($order !== '') {
                $layups[$layupName]['layers'][] = $this->buildLayerData($row, $orderCol, $thicknessCol, $widthCol, $angleCol);
            }
        }

        fclose($handle);

        return [
            'supplier' => ['name' => $supplier->name],
            'layups'   => array_values($layups),
        ];
    }

    /**
     * Parse an Excel file for single-supplier import.
     *
     * @param  UploadedFile  $file
     * @param  Supplier      $supplier
     * @return array|null
     */
    private function parseExcel(UploadedFile $file, Supplier $supplier): ?array
    {
        $rows = $this->parseExcelRows($file);
        if (!$rows) {
            return null;
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);

        $layupCol     = $this->findColumn($header, ['layup', 'layup_name', 'layup name']);
        $orderCol     = $this->findColumn($header, ['layer_order', 'layer order', 'order']);
        $thicknessCol = $this->findColumn($header, ['thickness']);
        $widthCol     = $this->findColumn($header, ['width']);
        $angleCol     = $this->findColumn($header, ['angle']);

        if ($layupCol === null || $orderCol === null) {
            return null;
        }

        $layups = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $layupName = trim($row[$layupCol] ?? '');
            if (empty($layupName)) {
                continue;
            }

            if (!isset($layups[$layupName])) {
                $layups[$layupName] = ['name' => $layupName, 'layers' => []];
            }

            $order = trim($row[$orderCol] ?? '');
            if ($order !== '') {
                $layups[$layupName]['layers'][] = $this->buildLayerData($row, $orderCol, $thicknessCol, $widthCol, $angleCol);
            }
        }

        return [
            'supplier' => ['name' => $supplier->name],
            'layups'   => array_values($layups),
        ];
    }

    /**
     * Parse a JSON file for bulk (all-suppliers) import.
     *
     * @param  UploadedFile  $file
     * @return array|null
     */
    private function parseAllJson(UploadedFile $file): ?array
    {
        $content = file_get_contents($file->getRealPath());
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        if (isset($data['suppliers'])) {
            return $data;
        }

        if (isset($data['layups']) && isset($data['supplier']['name'])) {
            return [
                'suppliers' => [[
                    'name'   => $data['supplier']['name'],
                    'layups' => $data['layups'],
                ]],
            ];
        }

        return null;
    }

    /**
     * Parse a CSV file for bulk (all-suppliers) import.
     *
     * @param  UploadedFile  $file
     * @return array|null
     */
    private function parseAllCsv(UploadedFile $file): ?array
    {
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return null;
        }

        $header = fgetcsv($handle);
        if (!$header || count($header) < 4) {
            fclose($handle);
            return null;
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $header);

        $supplierCol  = $this->findColumn($header, ['supplier', 'supplier_name', 'supplier name']);
        $layupCol     = $this->findColumn($header, ['layup', 'layup_name', 'layup name']);
        $orderCol     = $this->findColumn($header, ['layer_order', 'layer order', 'order']);
        $thicknessCol = $this->findColumn($header, ['thickness']);
        $widthCol     = $this->findColumn($header, ['width']);
        $angleCol     = $this->findColumn($header, ['angle']);

        if ($supplierCol === null || $layupCol === null || $orderCol === null) {
            fclose($handle);
            return null;
        }

        $suppliers = [];

        while (($row = fgetcsv($handle)) !== false) {
            $supplierName = trim($row[$supplierCol] ?? '');
            $layupName = trim($row[$layupCol] ?? '');

            if (empty($supplierName) || empty($layupName)) {
                continue;
            }

            if (!isset($suppliers[$supplierName])) {
                $suppliers[$supplierName] = ['name' => $supplierName, 'layups' => []];
            }

            if (!isset($suppliers[$supplierName]['layups'][$layupName])) {
                $suppliers[$supplierName]['layups'][$layupName] = ['name' => $layupName, 'layers' => []];
            }

            $order = trim($row[$orderCol] ?? '');
            if ($order !== '') {
                $suppliers[$supplierName]['layups'][$layupName]['layers'][] = $this->buildLayerData($row, $orderCol, $thicknessCol, $widthCol, $angleCol);
            }
        }

        fclose($handle);

        foreach ($suppliers as &$s) {
            $s['layups'] = array_values($s['layups']);
        }

        return ['suppliers' => array_values($suppliers)];
    }

    /**
     * Parse an Excel file for bulk (all-suppliers) import.
     *
     * @param  UploadedFile  $file
     * @return array|null
     */
    private function parseAllExcel(UploadedFile $file): ?array
    {
        $rows = $this->parseExcelRows($file);
        if (!$rows) {
            return null;
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $rows[0]);

        $supplierCol  = $this->findColumn($header, ['supplier', 'supplier_name', 'supplier name']);
        $layupCol     = $this->findColumn($header, ['layup', 'layup_name', 'layup name']);
        $orderCol     = $this->findColumn($header, ['layer_order', 'layer order', 'order']);
        $thicknessCol = $this->findColumn($header, ['thickness']);
        $widthCol     = $this->findColumn($header, ['width']);
        $angleCol     = $this->findColumn($header, ['angle']);

        if ($supplierCol === null || $layupCol === null || $orderCol === null) {
            return null;
        }

        $suppliers = [];

        for ($i = 1; $i < count($rows); $i++) {
            $row = $rows[$i];
            $supplierName = trim($row[$supplierCol] ?? '');
            $layupName = trim($row[$layupCol] ?? '');

            if (empty($supplierName) || empty($layupName)) {
                continue;
            }

            if (!isset($suppliers[$supplierName])) {
                $suppliers[$supplierName] = ['name' => $supplierName, 'layups' => []];
            }

            if (!isset($suppliers[$supplierName]['layups'][$layupName])) {
                $suppliers[$supplierName]['layups'][$layupName] = ['name' => $layupName, 'layers' => []];
            }

            $order = trim($row[$orderCol] ?? '');
            if ($order !== '') {
                $suppliers[$supplierName]['layups'][$layupName]['layers'][] = $this->buildLayerData($row, $orderCol, $thicknessCol, $widthCol, $angleCol);
            }
        }

        foreach ($suppliers as &$s) {
            $s['layups'] = array_values($s['layups']);
        }

        return ['suppliers' => array_values($suppliers)];
    }

    /**
     * Parse raw rows from an Excel XML Spreadsheet file.
     *
     * @param  UploadedFile  $file
     * @return array|null  Two-dimensional array of cell values, or null on failure.
     */
    private function parseExcelRows(UploadedFile $file): ?array
    {
        $content = file_get_contents($file->getRealPath());
        if (!$content) {
            return null;
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();

        if (!$xml) {
            return null;
        }

        $namespaces = $xml->getNamespaces(true);
        $ssNs = $namespaces['ss'] ?? 'urn:schemas-microsoft-com:office:spreadsheet';

        $rows = [];

        foreach ($xml->children($ssNs)->Worksheet as $worksheet) {
            foreach ($worksheet->children($ssNs)->Table->children($ssNs)->Row as $row) {
                $cells = [];
                foreach ($row->children($ssNs)->Cell as $cell) {
                    $data = $cell->children($ssNs)->Data;
                    $cells[] = (string) $data;
                }
                $rows[] = $cells;
            }
            break; // Only process first worksheet
        }

        if (count($rows) < 2) {
            return null;
        }

        return $rows;
    }

    /**
     * Find a column index by matching against a list of possible header names.
     *
     * @param  array  $header  Normalised header row.
     * @param  array  $names   Possible column names.
     * @return int|null  Column index or null if not found.
     */
    private function findColumn(array $header, array $names): ?int
    {
        foreach ($names as $name) {
            $index = array_search($name, $header);
            if ($index !== false) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Build a layer data array from a CSV/Excel row.
     *
     * @param  array     $row
     * @param  int       $orderCol
     * @param  int|null  $thicknessCol
     * @param  int|null  $widthCol
     * @param  int|null  $angleCol
     * @return array{layer_order: int, thickness: float, width: float, angle: float}
     */
    private function buildLayerData(array $row, int $orderCol, ?int $thicknessCol, ?int $widthCol, ?int $angleCol): array
    {
        return [
            'layer_order' => (int) ($row[$orderCol] ?? 0),
            'thickness'   => $thicknessCol !== null ? (float) ($row[$thicknessCol] ?? 0) : 0,
            'width'       => $widthCol !== null ? (float) ($row[$widthCol] ?? 0) : 0,
            'angle'       => $angleCol !== null ? (float) ($row[$angleCol] ?? 0) : 0,
        ];
    }

    // ── Private: Import Helpers ─────────────────────────────────────

    /**
     * Check whether any resolution for a layup index requests duplication.
     *
     * @param  array  $resolutions
     * @param  int    $layupIndex
     * @return bool
     */
    private function shouldDuplicate(array $resolutions, int $layupIndex): bool
    {
        foreach ($resolutions as $key => $resolution) {
            if ($resolution === 'duplicate' && str_starts_with($key, $layupIndex . '_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create a duplicate layup with " (imported)" suffix and all its layers.
     *
     * @param  Supplier  $supplier
     * @param  array     $importLayup
     * @return void
     */
    private function createDuplicateLayup(Supplier $supplier, array $importLayup): void
    {
        $newLayup = $supplier->layups()->create([
            'name' => $importLayup['name'] . ' (imported)',
        ]);

        if (isset($importLayup['layers'])) {
            foreach ($importLayup['layers'] as $importLayer) {
                $newLayup->layers()->create([
                    'layer_order' => $importLayer['layer_order'],
                    'thickness'   => $importLayer['thickness'],
                    'width'       => $importLayer['width'],
                    'angle'       => $importLayer['angle'],
                ]);
            }
        }
    }

    /**
     * Import layers into an existing layup, respecting conflict resolutions.
     *
     * @param  \App\Models\Layup  $layup
     * @param  array              $importLayers
     * @param  array              $resolutions
     * @param  int                $layupIndex
     * @return void
     */
    private function importLayers($layup, array $importLayers, array $resolutions, int $layupIndex): void
    {
        foreach ($importLayers as $layerIndex => $importLayer) {
            $existingLayer = $layup->layers()
                ->where('layer_order', $importLayer['layer_order'])
                ->first();

            if (!$existingLayer) {
                $layup->layers()->create([
                    'layer_order' => $importLayer['layer_order'],
                    'thickness'   => $importLayer['thickness'],
                    'width'       => $importLayer['width'],
                    'angle'       => $importLayer['angle'],
                ]);
                continue;
            }

            $resolutionKey = $layupIndex . '_' . $layerIndex;
            $resolution = $resolutions[$resolutionKey] ?? 'skip';

            if ($resolution === 'overwrite' || $resolution === 'incoming') {
                $existingLayer->update([
                    'thickness' => $importLayer['thickness'],
                    'width'     => $importLayer['width'],
                    'angle'     => $importLayer['angle'],
                ]);
            }
        }
    }

    /**
     * Extract per-supplier resolutions from the global resolution map.
     *
     * @param  array  $resolutions    Global resolution map keyed as "supplierIndex_layupIndex_layerIndex".
     * @param  int    $supplierIndex  The supplier index to extract.
     * @return array  Per-supplier resolution map.
     */
    private function extractSupplierResolutions(array $resolutions, int $supplierIndex): array
    {
        $result = [];

        foreach ($resolutions as $key => $value) {
            if (str_starts_with($key, $supplierIndex . '_')) {
                $subKey = substr($key, strlen($supplierIndex . '_'));
                $result[$subKey] = $value;
            }
        }

        return $result;
    }
}
