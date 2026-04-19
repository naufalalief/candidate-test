<?php

namespace Tests\Feature;

use App\Models\Layup;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LayupTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        $this->supplier = Supplier::create(['name' => 'Test Supplier']);
    }

    public function test_guests_cannot_access_layups(): void
    {
        Auth::logout();

        $this->get(route('suppliers.layups.index', $this->supplier))->assertRedirect('/login');
        $this->get(route('suppliers.layups.create', $this->supplier))->assertRedirect('/login');
        $this->post(route('suppliers.layups.store', $this->supplier))->assertRedirect('/login');
    }

    public function test_layups_index_page_is_displayed(): void
    {
        $response = $this->get(route('suppliers.layups.index', $this->supplier));

        $response->assertOk();
        $response->assertViewIs('layups.index');
    }

    public function test_layups_index_shows_layups_for_supplier(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $response = $this->get(route('suppliers.layups.index', $this->supplier));

        $response->assertOk();
        $response->assertSee('Layup A');
    }

    public function test_layup_create_page_is_displayed(): void
    {
        $response = $this->get(route('suppliers.layups.create', $this->supplier));

        $response->assertOk();
        $response->assertViewIs('layups.create');
    }

    public function test_layup_can_be_created(): void
    {
        $response = $this->post(route('suppliers.layups.store', $this->supplier), [
            'name' => 'New Layup',
        ]);

        $response->assertRedirect(route('suppliers.layups.index', $this->supplier));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layups', [
            'supplier_id' => $this->supplier->id,
            'name' => 'New Layup',
        ]);
    }

    public function test_layup_creation_requires_name(): void
    {
        $response = $this->post(route('suppliers.layups.store', $this->supplier), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_layup_show_page_is_displayed(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $response = $this->get(route('suppliers.layups.show', [$this->supplier, $layup]));

        $response->assertOk();
        $response->assertViewIs('layups.show');
        $response->assertSee('Layup A');
    }

    public function test_layup_edit_page_is_displayed(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $response = $this->get(route('suppliers.layups.edit', [$this->supplier, $layup]));

        $response->assertOk();
        $response->assertViewIs('layups.edit');
    }

    public function test_layup_can_be_updated(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Old Name']);

        $response = $this->put(route('suppliers.layups.update', [$this->supplier, $layup]), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('suppliers.layups.index', $this->supplier));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('clt_layups', ['id' => $layup->id, 'name' => 'Updated Name']);
    }

    public function test_layup_update_requires_name(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $response = $this->put(route('suppliers.layups.update', [$this->supplier, $layup]), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_layup_can_be_deleted(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $response = $this->delete(route('suppliers.layups.destroy', [$this->supplier, $layup]));

        $response->assertRedirect(route('suppliers.layups.index', $this->supplier));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('clt_layups', ['id' => $layup->id]);
    }

    public function test_deleting_supplier_cascades_to_layups(): void
    {
        $layup = $this->supplier->layups()->create(['name' => 'Layup A']);

        $this->supplier->delete();

        $this->assertDatabaseMissing('clt_layups', ['id' => $layup->id]);
    }
}
