<?php

namespace Tests\Feature;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function authenticatedUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        return $user;
    }

    public function test_guests_cannot_access_suppliers(): void
    {
        $this->get(route('suppliers.index'))->assertRedirect('/login');
        $this->get(route('suppliers.create'))->assertRedirect('/login');
        $this->post(route('suppliers.store'))->assertRedirect('/login');
    }

    public function test_suppliers_index_page_is_displayed(): void
    {
        $this->authenticatedUser();

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertViewIs('suppliers.index');
    }

    public function test_suppliers_index_shows_suppliers(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        $response = $this->get(route('suppliers.index'));

        $response->assertOk();
        $response->assertSee('Test Supplier');
    }

    public function test_supplier_create_page_is_displayed(): void
    {
        $this->authenticatedUser();

        $response = $this->get(route('suppliers.create'));

        $response->assertOk();
        $response->assertViewIs('suppliers.create');
    }

    public function test_supplier_can_be_created(): void
    {
        $this->authenticatedUser();

        $response = $this->post(route('suppliers.store'), [
            'name' => 'New Supplier',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['name' => 'New Supplier']);
    }

    public function test_supplier_creation_requires_name(): void
    {
        $this->authenticatedUser();

        $response = $this->post(route('suppliers.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_supplier_show_page_is_displayed(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        $response = $this->get(route('suppliers.show', $supplier));

        $response->assertOk();
        $response->assertViewIs('suppliers.show');
        $response->assertSee('Test Supplier');
    }

    public function test_supplier_edit_page_is_displayed(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        $response = $this->get(route('suppliers.edit', $supplier));

        $response->assertOk();
        $response->assertViewIs('suppliers.edit');
    }

    public function test_supplier_can_be_updated(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Old Name']);

        $response = $this->put(route('suppliers.update', $supplier), [
            'name' => 'Updated Name',
        ]);

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'name' => 'Updated Name']);
    }

    public function test_supplier_update_requires_name(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        $response = $this->put(route('suppliers.update', $supplier), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_supplier_can_be_deleted(): void
    {
        $this->authenticatedUser();
        $supplier = Supplier::create(['name' => 'Test Supplier']);

        $response = $this->delete(route('suppliers.destroy', $supplier));

        $response->assertRedirect(route('suppliers.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('suppliers', ['id' => $supplier->id]);
    }
}
