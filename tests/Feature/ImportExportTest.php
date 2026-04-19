<?php

namespace Tests\Feature;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ImportExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
    }


    public function test_export_requires_authentication(): void
    {
        $supplier = Supplier::factory()->create();
        $response = $this->get(route('suppliers.export', $supplier));
        $response->assertRedirect('/login');
    }

    public function test_export_returns_json_with_supplier_data(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Test Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup A']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.export', $supplier));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');

        $data = $response->json();
        $this->assertEquals('Test Supplier', $data['supplier']['name']);
        $this->assertCount(1, $data['layups']);
        $this->assertEquals('Layup A', $data['layups'][0]['name']);
        $this->assertCount(1, $data['layups'][0]['layers']);
        $this->assertEquals(1, $data['layups'][0]['layers'][0]['layer_order']);
        $this->assertEquals(10.5, $data['layups'][0]['layers'][0]['thickness']);
        $this->assertEquals(20.0, $data['layups'][0]['layers'][0]['width']);
        $this->assertEquals(45.0, $data['layups'][0]['layers'][0]['angle']);
    }

    public function test_export_with_multiple_layups_and_layers(): void
    {
        $supplier = Supplier::factory()->create();
        $layup1 = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup 1']);
        $layup2 = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup 2']);
        Layer::factory()->create(['layup_id' => $layup1->id, 'layer_order' => 1]);
        Layer::factory()->create(['layup_id' => $layup1->id, 'layer_order' => 2]);
        Layer::factory()->create(['layup_id' => $layup2->id, 'layer_order' => 1]);

        $response = $this->actingAs($this->user)->get(route('suppliers.export', $supplier));

        $data = $response->json();
        $this->assertCount(2, $data['layups']);
        $this->assertCount(2, $data['layups'][0]['layers']);
        $this->assertCount(1, $data['layups'][1]['layers']);
    }

    public function test_export_supplier_with_no_layups(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->user)->get(route('suppliers.export', $supplier));

        $response->assertOk();
        $data = $response->json();
        $this->assertCount(0, $data['layups']);
    }

    public function test_export_csv_format(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'CSV Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup CSV']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.export', [$supplier, 'format' => 'csv']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition');

        $content = $response->streamedContent();
        $this->assertStringContainsString('Supplier', $content);
        $this->assertStringContainsString('Layup', $content);
        $this->assertStringContainsString('CSV Supplier', $content);
        $this->assertStringContainsString('Layup CSV', $content);
    }

    public function test_export_excel_format(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Excel Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup XLS']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 12.0,
            'width' => 25.0,
            'angle' => 90.0,
        ]);

        $response = $this->actingAs($this->user)->get(route('suppliers.export', [$supplier, 'format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $response->assertHeader('Content-Disposition');

        $content = $response->getContent();
        $this->assertStringContainsString('Excel Supplier', $content);
        $this->assertStringContainsString('Layup XLS', $content);
        $this->assertStringContainsString('xml', strtolower($content));
    }

    public function test_export_all_suppliers_json(): void
    {
        Supplier::factory()->create(['name' => 'Supplier A']);
        Supplier::factory()->create(['name' => 'Supplier B']);

        $response = $this->actingAs($this->user)->get(route('suppliers.export.all', ['format' => 'json']));

        $response->assertOk();
        $response->assertHeader('Content-Disposition');

        $data = $response->json();
        $this->assertCount(2, $data['suppliers']);
    }

    public function test_export_all_suppliers_csv(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'All CSV']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'L1']);
        Layer::factory()->create(['layup_id' => $layup->id, 'layer_order' => 1, 'thickness' => 5, 'width' => 10, 'angle' => 0]);

        $response = $this->actingAs($this->user)->get(route('suppliers.export.all', ['format' => 'csv']));

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('All CSV', $content);
    }

    public function test_export_all_suppliers_excel(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'All Excel']);
        Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'XLS Layup']);

        $response = $this->actingAs($this->user)->get(route('suppliers.export.all', ['format' => 'excel']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
        $content = $response->getContent();
        $this->assertStringContainsString('All Excel', $content);
    }


    public function test_import_form_requires_authentication(): void
    {
        $supplier = Supplier::factory()->create();
        $response = $this->get(route('suppliers.import', $supplier));
        $response->assertRedirect('/login');
    }

    public function test_import_form_is_displayed(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->user)->get(route('suppliers.import', $supplier));

        $response->assertOk();
        $response->assertViewIs('suppliers.import');
    }


    public function test_import_rejects_invalid_json(): void
    {
        $supplier = Supplier::factory()->create();
        $file = UploadedFile::fake()->createWithContent('data.json', 'not valid json');

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_rejects_json_without_layups_key(): void
    {
        $supplier = Supplier::factory()->create();
        $file = UploadedFile::fake()->createWithContent('data.json', json_encode(['supplier' => ['name' => 'Test']]));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_creates_new_layups_and_layers_without_conflicts(): void
    {
        $supplier = Supplier::factory()->create();

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'New Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 10.0, 'width' => 20.0, 'angle' => 45.0],
                        ['layer_order' => 2, 'thickness' => 15.0, 'width' => 25.0, 'angle' => 90.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'New Layup']);
        $this->assertEquals(2, Layer::whereHas('layup', fn ($q) => $q->where('supplier_id', $supplier->id))->count());
    }

    public function test_import_detects_conflicts_and_shows_resolution_page(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Existing Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Existing Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
            'strategy' => 'review',
        ]);

        $response->assertOk();
        $response->assertViewIs('suppliers.conflicts');
        $response->assertViewHas('conflicts');
    }

    public function test_import_detects_identical_data_as_conflict(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Same Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Same Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 10.0, 'width' => 20.0, 'angle' => 45.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
            'strategy' => 'review',
        ]);

        $response->assertOk();
        $response->assertViewIs('suppliers.conflicts');
        $response->assertViewHas('conflicts', function ($conflicts) {
            return count($conflicts) === 1 && $conflicts[0]['identical'] === true;
        });
    }

    public function test_import_skip_strategy_skips_conflicts(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Existing Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Existing Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
            'strategy' => 'skip',
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layers', ['layup_id' => $layup->id, 'thickness' => 10.0, 'width' => 20.0, 'angle' => 45.0]);
    }

    public function test_import_overwrite_strategy_overwrites_conflicts(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Existing Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Existing Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
            'strategy' => 'overwrite',
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layers', ['layup_id' => $layup->id, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0]);
    }


    public function test_resolve_conflicts_with_keep_existing(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup A']);
        $layer = Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Layup A',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        session(['import_data' => $importData, 'import_supplier_id' => $supplier->id]);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.resolve', $supplier), [
            'resolutions' => ['0_0' => 'existing'],
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $layer->refresh();
        $this->assertEquals(10.0, (float) $layer->thickness);
        $this->assertEquals(20.0, (float) $layer->width);
        $this->assertEquals(45.0, (float) $layer->angle);
    }

    public function test_resolve_conflicts_with_accept_incoming(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup A']);
        $layer = Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Layup A',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        session(['import_data' => $importData, 'import_supplier_id' => $supplier->id]);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.resolve', $supplier), [
            'resolutions' => ['0_0' => 'incoming'],
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $layer->refresh();
        $this->assertEquals(99.0, (float) $layer->thickness);
        $this->assertEquals(88.0, (float) $layer->width);
        $this->assertEquals(77.0, (float) $layer->angle);
    }

    public function test_resolve_conflicts_with_duplicate(): void
    {
        $supplier = Supplier::factory()->create();
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup A']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'supplier' => ['name' => 'Test'],
            'layups' => [
                [
                    'name' => 'Layup A',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                    ],
                ],
            ],
        ];

        session(['import_data' => $importData, 'import_supplier_id' => $supplier->id]);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.resolve', $supplier), [
            'resolutions' => ['0_0' => 'duplicate'],
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'Layup A']);
        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'Layup A (imported)']);
        $this->assertEquals(2, Layup::where('supplier_id', $supplier->id)->count());
    }

    public function test_resolve_without_session_data_redirects_with_error(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->user)->post(route('suppliers.import.resolve', $supplier), [
            'resolutions' => [],
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHasErrors('import');
    }

    public function test_import_requires_file_upload(): void
    {
        $supplier = Supplier::factory()->create();

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_csv_creates_layups_and_layers(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'CSV Import Supplier']);

        $csv = "Supplier,Layup,Layer Order,Thickness,Width,Angle\n";
        $csv .= "CSV Import Supplier,CSV Layup,1,10.5,20.0,45.0\n";
        $csv .= "CSV Import Supplier,CSV Layup,2,15.0,25.0,90.0\n";

        $file = UploadedFile::fake()->createWithContent('data.csv', $csv);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'CSV Layup']);
        $layup = Layup::where('supplier_id', $supplier->id)->where('name', 'CSV Layup')->first();
        $this->assertNotNull($layup);
        $this->assertEquals(2, $layup->layers()->count());
    }

    public function test_import_excel_creates_layups_and_layers(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Excel Import Supplier']);

        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="Layups"><Table>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Supplier</Data></Cell><Cell><Data ss:Type="String">Layup</Data></Cell><Cell><Data ss:Type="String">Layer Order</Data></Cell><Cell><Data ss:Type="String">Thickness</Data></Cell><Cell><Data ss:Type="String">Width</Data></Cell><Cell><Data ss:Type="String">Angle</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Excel Import Supplier</Data></Cell><Cell><Data ss:Type="String">Excel Layup</Data></Cell><Cell><Data ss:Type="Number">1</Data></Cell><Cell><Data ss:Type="Number">10.5</Data></Cell><Cell><Data ss:Type="Number">20.0</Data></Cell><Cell><Data ss:Type="Number">45.0</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Excel Import Supplier</Data></Cell><Cell><Data ss:Type="String">Excel Layup</Data></Cell><Cell><Data ss:Type="Number">2</Data></Cell><Cell><Data ss:Type="Number">15.0</Data></Cell><Cell><Data ss:Type="Number">25.0</Data></Cell><Cell><Data ss:Type="Number">90.0</Data></Cell></Row>' . "\n";
        $xml .= '</Table></Worksheet></Workbook>';

        $file = UploadedFile::fake()->createWithContent('data.xls', $xml);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.show', $supplier));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $supplier->id, 'name' => 'Excel Layup']);
        $layup = Layup::where('supplier_id', $supplier->id)->where('name', 'Excel Layup')->first();
        $this->assertNotNull($layup);
        $this->assertEquals(2, $layup->layers()->count());
    }

    public function test_import_rejects_unsupported_file_format(): void
    {
        $supplier = Supplier::factory()->create();
        $file = UploadedFile::fake()->createWithContent('data.xml', '<root></root>');

        $response = $this->actingAs($this->user)->post(route('suppliers.import.preview', $supplier), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }


    public function test_export_then_import_round_trip(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Round Trip']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup RT']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 12.5,
            'width' => 30.0,
            'angle' => 60.0,
        ]);

        $exportResponse = $this->actingAs($this->user)->get(route('suppliers.export', $supplier));
        $exportedJson = $exportResponse->getContent();

        $newSupplier = Supplier::factory()->create(['name' => 'Import Target']);
        $file = UploadedFile::fake()->createWithContent('data.json', $exportedJson);

        $importResponse = $this->actingAs($this->user)->post(route('suppliers.import.preview', $newSupplier), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect(route('suppliers.show', $newSupplier));

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $newSupplier->id, 'name' => 'Layup RT']);
        $importedLayup = Layup::where('supplier_id', $newSupplier->id)->where('name', 'Layup RT')->first();
        $this->assertNotNull($importedLayup);
        $importedLayer = $importedLayup->layers()->first();
        $this->assertEquals(1, $importedLayer->layer_order);
        $this->assertEquals(12.5, (float) $importedLayer->thickness);
        $this->assertEquals(30.0, (float) $importedLayer->width);
        $this->assertEquals(60.0, (float) $importedLayer->angle);
    }

    public function test_export_excel_then_import_excel_round_trip(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Excel RT']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Layup ERT']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 12.5,
            'width' => 30.0,
            'angle' => 60.0,
        ]);

        $exportResponse = $this->actingAs($this->user)->get(route('suppliers.export', ['supplier' => $supplier, 'format' => 'excel']));
        $exportedExcel = $exportResponse->getContent();

        $newSupplier = Supplier::factory()->create(['name' => 'Excel Import Target']);
        $file = UploadedFile::fake()->createWithContent('data.xls', $exportedExcel);

        $importResponse = $this->actingAs($this->user)->post(route('suppliers.import.preview', $newSupplier), [
            'file' => $file,
        ]);

        $importResponse->assertRedirect(route('suppliers.show', $newSupplier));

        $this->assertDatabaseHas('clt_layups', ['supplier_id' => $newSupplier->id, 'name' => 'Layup ERT']);
        $importedLayup = Layup::where('supplier_id', $newSupplier->id)->where('name', 'Layup ERT')->first();
        $this->assertNotNull($importedLayup);
        $importedLayer = $importedLayup->layers()->first();
        $this->assertEquals(1, $importedLayer->layer_order);
        $this->assertEquals(12.5, (float) $importedLayer->thickness);
        $this->assertEquals(30.0, (float) $importedLayer->width);
        $this->assertEquals(60.0, (float) $importedLayer->angle);
    }


    public function test_import_all_form_requires_authentication(): void
    {
        $response = $this->get(route('suppliers.import.all'));
        $response->assertRedirect('/login');
    }

    public function test_import_all_form_is_displayed(): void
    {
        $response = $this->actingAs($this->user)->get(route('suppliers.import.all'));
        $response->assertOk();
        $response->assertViewIs('suppliers.import-all');
    }

    public function test_import_all_creates_new_suppliers_with_layups_and_layers(): void
    {
        $importData = [
            'suppliers' => [
                [
                    'name' => 'New Supplier A',
                    'layups' => [
                        [
                            'name' => 'Layup X',
                            'layers' => [
                                ['layer_order' => 1, 'thickness' => 10.0, 'width' => 20.0, 'angle' => 45.0],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'New Supplier B',
                    'layups' => [
                        [
                            'name' => 'Layup Y',
                            'layers' => [
                                ['layer_order' => 1, 'thickness' => 5.0, 'width' => 15.0, 'angle' => 90.0],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('suppliers.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('suppliers', ['name' => 'New Supplier A']);
        $this->assertDatabaseHas('suppliers', ['name' => 'New Supplier B']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'Layup X']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'Layup Y']);
    }

    public function test_import_all_csv_creates_suppliers(): void
    {
        $csv = "Supplier,Layup,Layer Order,Thickness,Width,Angle\n";
        $csv .= "CSV Supplier 1,Layup C1,1,10.0,20.0,45.0\n";
        $csv .= "CSV Supplier 2,Layup C2,1,5.0,15.0,90.0\n";

        $file = UploadedFile::fake()->createWithContent('suppliers.csv', $csv);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'CSV Supplier 1']);
        $this->assertDatabaseHas('suppliers', ['name' => 'CSV Supplier 2']);
    }

    public function test_import_all_excel_creates_suppliers(): void
    {
        $xml = '<?xml version="1.0"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="Layups"><Table>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Supplier</Data></Cell><Cell><Data ss:Type="String">Layup</Data></Cell><Cell><Data ss:Type="String">Layer Order</Data></Cell><Cell><Data ss:Type="String">Thickness</Data></Cell><Cell><Data ss:Type="String">Width</Data></Cell><Cell><Data ss:Type="String">Angle</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Excel Supplier 1</Data></Cell><Cell><Data ss:Type="String">Layup E1</Data></Cell><Cell><Data ss:Type="Number">1</Data></Cell><Cell><Data ss:Type="Number">10.0</Data></Cell><Cell><Data ss:Type="Number">20.0</Data></Cell><Cell><Data ss:Type="Number">45.0</Data></Cell></Row>' . "\n";
        $xml .= '<Row><Cell><Data ss:Type="String">Excel Supplier 2</Data></Cell><Cell><Data ss:Type="String">Layup E2</Data></Cell><Cell><Data ss:Type="Number">1</Data></Cell><Cell><Data ss:Type="Number">5.0</Data></Cell><Cell><Data ss:Type="Number">15.0</Data></Cell><Cell><Data ss:Type="Number">90.0</Data></Cell></Row>' . "\n";
        $xml .= '</Table></Worksheet></Workbook>';

        $file = UploadedFile::fake()->createWithContent('suppliers.xls', $xml);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Excel Supplier 1']);
        $this->assertDatabaseHas('suppliers', ['name' => 'Excel Supplier 2']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'Layup E1']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'Layup E2']);
    }

    public function test_import_all_detects_conflicts(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Conflict Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Conflict Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'suppliers' => [
                [
                    'name' => 'Conflict Supplier',
                    'layups' => [
                        [
                            'name' => 'Conflict Layup',
                            'layers' => [
                                ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('data.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertOk();
        $response->assertViewIs('suppliers.conflicts-all');
        $response->assertViewHas('conflicts');
    }

    public function test_import_all_resolve_conflicts_accept_incoming(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Resolve Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'Resolve Layup']);
        $layer = Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $importData = [
            'suppliers' => [
                [
                    'name' => 'Resolve Supplier',
                    'layups' => [
                        [
                            'name' => 'Resolve Layup',
                            'layers' => [
                                ['layer_order' => 1, 'thickness' => 99.0, 'width' => 88.0, 'angle' => 77.0],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        session(['import_all_data' => $importData]);

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.resolve'), [
            'resolutions' => ['0_0_0' => 'incoming'],
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $layer->refresh();
        $this->assertEquals(99.0, (float) $layer->thickness);
        $this->assertEquals(88.0, (float) $layer->width);
        $this->assertEquals(77.0, (float) $layer->angle);
    }

    public function test_import_all_rejects_invalid_file(): void
    {
        $file = UploadedFile::fake()->createWithContent('data.json', 'not valid json');

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_import_all_accepts_single_supplier_format(): void
    {
        $importData = [
            'supplier' => ['name' => 'Single Format Supplier'],
            'layups' => [
                [
                    'name' => 'Single Layup',
                    'layers' => [
                        ['layer_order' => 1, 'thickness' => 10.0, 'width' => 20.0, 'angle' => 45.0],
                    ],
                ],
            ],
        ];

        $file = UploadedFile::fake()->createWithContent('single.json', json_encode($importData));

        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'Single Format Supplier']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'Single Layup']);
    }

    public function test_export_all_then_import_all_round_trip(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'RT Supplier']);
        $layup = Layup::factory()->create(['supplier_id' => $supplier->id, 'name' => 'RT Layup']);
        Layer::factory()->create([
            'layup_id' => $layup->id,
            'layer_order' => 1,
            'thickness' => 12.5,
            'width' => 30.0,
            'angle' => 60.0,
        ]);

        $exportResponse = $this->actingAs($this->user)->get(route('suppliers.export.all', ['format' => 'json']));
        $exportedJson = $exportResponse->getContent();

        $supplier->layups()->each(fn ($l) => $l->layers()->delete());
        $supplier->layups()->delete();
        $supplier->delete();

        $file = UploadedFile::fake()->createWithContent('data.json', $exportedJson);
        $response = $this->actingAs($this->user)->post(route('suppliers.import.all.preview'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $this->assertDatabaseHas('suppliers', ['name' => 'RT Supplier']);
        $this->assertDatabaseHas('clt_layups', ['name' => 'RT Layup']);
    }
}
