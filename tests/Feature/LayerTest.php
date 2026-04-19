<?php

namespace Tests\Feature;

use App\Models\Layer;
use App\Models\Layup;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LayerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;
    private Layup $layup;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->supplier = Supplier::create(['name' => 'Test Supplier']);
        $this->layup = $this->supplier->layups()->create(['name' => 'Test Layup']);
    }

    public function test_guests_cannot_access_layers(): void
    {
        Auth::logout();

        $this->get(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]))->assertRedirect('/login');
        $this->get(route('suppliers.layups.layers.create', [$this->supplier, $this->layup]))->assertRedirect('/login');
        $this->post(route('suppliers.layups.layers.store', [$this->supplier, $this->layup]))->assertRedirect('/login');
    }

    public function test_layers_index_page_is_displayed(): void
    {
        $response = $this->get(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]));

        $response->assertOk();
        $response->assertViewIs('layers.index');
    }

    public function test_layers_index_shows_layers_for_layup(): void
    {
        $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.5,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->get(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]));

        $response->assertOk();
        $response->assertSee('10.5');
        $response->assertSee('20');
        $response->assertSee('45');
    }

    public function test_layer_create_page_is_displayed(): void
    {
        $response = $this->get(route('suppliers.layups.layers.create', [$this->supplier, $this->layup]));

        $response->assertOk();
        $response->assertViewIs('layers.create');
    }

    public function test_layer_can_be_created(): void
    {
        $response = $this->post(route('suppliers.layups.layers.store', [$this->supplier, $this->layup]), [
            'layer_order' => 1,
            'thickness' => 15.5,
            'width' => 25.0,
            'angle' => 90.0,
        ]);

        $response->assertRedirect(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layers', [
            'layup_id' => $this->layup->id,
            'layer_order' => 1,
            'thickness' => 15.5,
            'width' => 25.0,
            'angle' => 90.0,
        ]);
    }

    public function test_layer_creation_requires_all_fields(): void
    {
        $response = $this->post(route('suppliers.layups.layers.store', [$this->supplier, $this->layup]), []);

        $response->assertSessionHasErrors(['layer_order', 'thickness', 'width', 'angle']);
    }

    public function test_layer_order_must_be_positive_integer(): void
    {
        $response = $this->post(route('suppliers.layups.layers.store', [$this->supplier, $this->layup]), [
            'layer_order' => -1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response->assertSessionHasErrors('layer_order');
    }

    public function test_thickness_must_be_non_negative(): void
    {
        $response = $this->post(route('suppliers.layups.layers.store', [$this->supplier, $this->layup]), [
            'layer_order' => 1,
            'thickness' => -5.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response->assertSessionHasErrors('thickness');
    }

    public function test_layer_edit_page_is_displayed(): void
    {
        $layer = $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->get(route('suppliers.layups.layers.edit', [$this->supplier, $this->layup, $layer]));

        $response->assertOk();
        $response->assertViewIs('layers.edit');
    }

    public function test_layer_can_be_updated(): void
    {
        $layer = $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->put(route('suppliers.layups.layers.update', [$this->supplier, $this->layup, $layer]), [
            'layer_order' => 2,
            'thickness' => 12.5,
            'width' => 30.0,
            'angle' => 60.0,
        ]);

        $response->assertRedirect(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layers', [
            'id' => $layer->id,
            'layer_order' => 2,
            'thickness' => 12.5,
            'width' => 30.0,
            'angle' => 60.0,
        ]);
    }

    public function test_layer_can_be_deleted(): void
    {
        $layer = $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $response = $this->delete(route('suppliers.layups.layers.destroy', [$this->supplier, $this->layup, $layer]));

        $response->assertRedirect(route('suppliers.layups.layers.index', [$this->supplier, $this->layup]));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('clt_layers', ['id' => $layer->id]);
    }

    public function test_deleting_layup_cascades_to_layers(): void
    {
        $layer = $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $this->layup->delete();

        $this->assertDatabaseMissing('clt_layers', ['id' => $layer->id]);
    }

    public function test_deleting_supplier_cascades_to_layers(): void
    {
        $layer = $this->layup->layers()->create([
            'layer_order' => 1,
            'thickness' => 10.0,
            'width' => 20.0,
            'angle' => 45.0,
        ]);

        $this->supplier->delete();

        $this->assertDatabaseMissing('clt_layups', ['id' => $this->layup->id]);
        $this->assertDatabaseMissing('clt_layers', ['id' => $layer->id]);
    }
}
