<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_login_instantly_as_customer(): void
    {
        $response = $this->get(route('login.customer'));

        $response->assertRedirect(route('products.index'));
        $this->assertAuthenticated();

        $user = auth()->user();
        $this->assertTrue($user->isCustomer());
        $this->assertEquals('customer', $user->role);
    }

    public function test_customer_cannot_access_dashboard_and_is_redirected_to_products(): void
    {
        $customer = User::create([
            'name' => 'Tamu Customer',
            'email' => 'customer_test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('dashboard'));

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('error');
    }

    public function test_customer_cannot_access_internal_operations(): void
    {
        $customer = User::create([
            'name' => 'Tamu Customer',
            'email' => 'customer_test2@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $this->actingAs($customer)->get(route('purchases.index'))
            ->assertRedirect(route('products.index'));

        $this->actingAs($customer)->get(route('sales.index'))
            ->assertRedirect(route('products.index'));

        $this->actingAs($customer)->get(route('products.low-stock'))
            ->assertRedirect(route('products.index'));

        $this->actingAs($customer)->get('/users')
            ->assertRedirect(route('products.index'));
    }

    public function test_customer_can_view_product_catalog_and_detail(): void
    {
        $category = Category::create(['name' => 'Elektronik']);
        $product = Product::create([
            'category_id' => $category->id,
            'sku' => 'SKU-TEST-001',
            'name' => 'Laptop Demo',
            'purchase_price' => 5000000,
            'selling_price' => 7500000,
            'stock' => 15,
            'minimum_stock' => 5,
        ]);

        $customer = User::create([
            'name' => 'Tamu Customer',
            'email' => 'customer_test3@example.com',
            'password' => bcrypt('password123'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get(route('products.index'));
        $response->assertOk();
        $response->assertSee('Laptop Demo');
        $response->assertSee('7.500.000');
        // Customer should NOT see purchase price
        $response->assertDontSee('5.000.000');

        $detailResponse = $this->actingAs($customer)->get(route('products.show', $product));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Laptop Demo');
        $detailResponse->assertSee('7.500.000');
        $detailResponse->assertSee('Ujian Akhir Semester (UAS)');
        $detailResponse->assertDontSee('Estimasi Margin Laba');
    }
}
