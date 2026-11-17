<?php

namespace Tests\Feature;

use App\Events\CourierLocationUpdated;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\LogisticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class SembakoDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $admin;
    private User $courier;
    private Product $cheapProduct;
    private Product $expensiveProduct;
    private StoreSetting $storeSetting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeSetting = StoreSetting::getSettings();

        $this->admin = User::create([
            'name' => 'Admin Sembako',
            'phone' => '081234567890',
            'email' => 'admin@sembako.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->courier = User::create([
            'name' => 'Budi Kurir',
            'phone' => '081234567891',
            'email' => 'driver@sembako.test',
            'password' => 'password',
            'role' => 'driver',
            'is_online' => true,
            'latitude' => -7.0051,
            'longitude' => 110.4381,
        ]);

        $this->customer = User::create([
            'name' => 'Siti Pelanggan',
            'phone' => '081234567892',
            'email' => 'pelanggan@sembako.test',
            'password' => 'password',
            'role' => 'customer',
            'address' => 'Jl. Erlangga Barat VII No. 12, Pleburan, Semarang Selatan',
            'latitude' => -7.0125,
            'longitude' => 110.4320,
        ]);

        $this->cheapProduct = Product::create([
            'title' => 'Garam Cap Kapal 250gr',
            'category' => 'Minyak & Bumbu',
            'unit' => 'pcs',
            'price' => 4500,
            'stock' => 50,
            'total_sold' => 200,
            'image_url' => 'https://example.com/garam.jpg',
            'is_active' => true,
        ]);

        $this->expensiveProduct = Product::create([
            'title' => 'Beras Pandan Wangi 5kg',
            'category' => 'Beras',
            'unit' => 'kg',
            'price' => 78500,
            'stock' => 30,
            'total_sold' => 150,
            'image_url' => 'https://example.com/beras.jpg',
            'is_active' => true,
        ]);
    }

    public function test_customer_storefront_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Toko Sembako Modern');
        $response->assertSee('Beras Pandan Wangi 5kg');
    }

    public function test_live_search_api_returns_matching_products(): void
    {
        $response = $this->getJson('/api/search?q=Beras');
        $response->assertStatus(200);
        $response->assertJsonFragment(['title' => 'Beras Pandan Wangi 5kg']);
        $response->assertJsonMissing(['title' => 'Garam Cap Kapal 250gr']);
    }

    public function test_shipping_fee_calculation_api(): void
    {
        $response = $this->postJson('/api/shipping-fee', [
            'lat' => -7.0125,
            'lng' => 110.4320,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['distance_km', 'shipping_fee', 'service_fee', 'store_lat', 'store_lng']);
        $this->assertGreaterThan(0, $response->json('shipping_fee'));
        $this->assertEquals($this->storeSetting->store_latitude, $response->json('store_lat'));
    }

    public function test_guest_cannot_checkout_and_receives_require_login(): void
    {
        $payload = [
            'customer_name' => 'Tamu',
            'customer_phone' => '081234567899',
            'delivery_address' => 'Jl. Pahlawan Semarang',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
            'payment_method' => 'COD',
            'cart_items' => [
                ['id' => $this->expensiveProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->postJson('/checkout', $payload);
        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'require_login' => true,
        ]);
    }

    public function test_cod_validation_fails_when_below_minimum_order(): void
    {
        // Subtotal: Rp 4.500 x 2 = Rp 9.000 (< Rp 50.000)
        $payload = [
            'customer_name' => 'Siti',
            'customer_phone' => '081234567892',
            'delivery_address' => 'Jl. Erlangga Barat VII',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
            'payment_method' => 'COD',
            'cart_items' => [
                ['id' => $this->cheapProduct->id, 'quantity' => 2],
            ],
        ];

        $response = $this->actingAs($this->customer)->postJson('/checkout', $payload);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['payment_method']);
    }

    public function test_cod_validation_passes_when_meeting_minimum_order(): void
    {
        // Subtotal: Rp 78.500 x 1 = Rp 78.500 (>= Rp 50.000)
        $payload = [
            'customer_name' => 'Siti Pelanggan',
            'customer_phone' => '081234567892',
            'delivery_address' => 'Jl. Erlangga Barat VII',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
            'payment_method' => 'COD',
            'cart_items' => [
                ['id' => $this->expensiveProduct->id, 'quantity' => 1],
            ],
        ];

        $response = $this->actingAs($this->customer)->postJson('/checkout', $payload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'payment_method' => 'COD',
            'order_status' => 'PENDING',
            'min_order_satisfied' => true,
        ]);
    }

    public function test_auth_pages_render_cleanly(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('Quick Demo Login');
        $loginResponse->assertSee('Admin Kasir');
        $loginResponse->assertSee('Kurir Driver');

        $registerResponse = $this->get('/register');
        $registerResponse->assertStatus(200);
        $registerResponse->assertSee('Daftar Akun Pelanggan');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sembako.test',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'admin@sembako.test',
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_quick_demo_login_for_all_roles(): void
    {
        // Test Quick Login Customer
        $customerLogin = $this->get('/login/quick/customer');
        $customerLogin->assertRedirect('/');
        $this->assertAuthenticatedAs($this->customer);

        // Test Quick Login Driver
        $driverLogin = $this->get('/login/quick/driver');
        $driverLogin->assertRedirect('/driver/dashboard');
        $this->assertAuthenticatedAs($this->courier);

        // Test Quick Login Admin
        $adminLogin = $this->get('/login/quick/admin');
        $adminLogin->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_customer_registration_creates_account_and_logs_in(): void
    {
        $payload = [
            'name' => 'Ahmad Baru',
            'email' => 'ahmad.baru@sembako.test',
            'phone' => '081298765432',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ];

        $response = $this->post('/register', $payload);
        $response->assertRedirect('/');

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad.baru@sembako.test',
            'role' => 'customer',
        ]);
        $this->assertAuthenticated();
    }

    public function test_role_middleware_restricts_and_authorizes_correctly(): void
    {
        // Customer cannot access admin dashboard
        $response = $this->actingAs($this->customer)->get('/admin/dashboard');
        $response->assertRedirect('/');

        // Customer cannot access driver dashboard
        $response = $this->actingAs($this->customer)->get('/driver/dashboard');
        $response->assertRedirect('/');

        // Driver cannot access admin dashboard
        $response = $this->actingAs($this->courier)->get('/admin/dashboard');
        $response->assertRedirect('/driver/dashboard');

        // Driver can access driver dashboard
        $response = $this->actingAs($this->courier)->get('/driver/dashboard');
        $response->assertStatus(200);

        // Admin can access admin dashboard
        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);

        // Admin CAN ALSO access driver dashboard for operational monitoring
        $response = $this->actingAs($this->admin)->get('/driver/dashboard');
        $response->assertStatus(200);
    }

    public function test_admin_orders_and_status_update(): void
    {
        $order = Order::create([
            'invoice_no' => 'INV/TEST/001',
            'customer_id' => $this->customer->id,
            'subtotal' => 78500,
            'shipping_fee' => 8000,
            'total_price' => 86500,
            'payment_method' => 'QRIS',
            'payment_status' => 'PENDING',
            'order_status' => 'PENDING',
            'delivery_address' => 'Jl. Erlangga Barat',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('INV/TEST/001');

        // Admin updates status to CONFIRMED
        $updateResponse = $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/status", [
            'order_status' => 'CONFIRMED',
        ]);
        $updateResponse->assertRedirect();

        $this->assertEquals('CONFIRMED', $order->fresh()->order_status);
    }

    public function test_courier_gps_location_broadcast(): void
    {
        Event::fake([CourierLocationUpdated::class]);

        $order = Order::create([
            'invoice_no' => 'INV/TEST/002',
            'customer_id' => $this->customer->id,
            'courier_id' => $this->courier->id,
            'subtotal' => 78500,
            'shipping_fee' => 8000,
            'total_price' => 86500,
            'payment_method' => 'QRIS',
            'payment_status' => 'PAID',
            'order_status' => 'SHIPPED',
            'delivery_address' => 'Jl. Erlangga Barat',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
        ]);

        $response = $this->actingAs($this->courier)->postJson("/courier/orders/{$order->id}/location", [
            'latitude' => -7.0090,
            'longitude' => 110.4350,
            'courier_id' => $this->courier->id,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('courier_location_logs', [
            'order_id' => $order->id,
            'latitude' => -7.0090,
            'longitude' => 110.4350,
        ]);

        Event::assertDispatched(CourierLocationUpdated::class);
    }

    public function test_simulated_qris_payment(): void
    {
        $order = Order::create([
            'invoice_no' => 'INV/TEST/003',
            'customer_id' => $this->customer->id,
            'subtotal' => 78500,
            'shipping_fee' => 8000,
            'total_price' => 86500,
            'payment_method' => 'QRIS',
            'payment_status' => 'PENDING',
            'order_status' => 'PENDING',
            'delivery_address' => 'Jl. Erlangga Barat',
            'delivery_lat' => -7.0125,
            'delivery_lng' => 110.4320,
        ]);

        $response = $this->postJson("/orders/{$order->id}/simulate-qris-pay");
        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('PAID', $order->payment_status);
        $this->assertEquals('CONFIRMED', $order->order_status);
    }

    public function test_admin_can_update_dynamic_store_settings_and_distance_updates(): void
    {
        // 1. Check settings page renders for admin
        $viewResponse = $this->actingAs($this->admin)->get('/admin/settings');
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Pengaturan Toko &amp; Lokasi', false);

        // 2. Update store location to new coordinates (e.g. Simpang Lima Semarang)
        $newSettings = [
            'store_name' => 'Toko Sembako Simpang Lima',
            'store_address' => 'Jl. Pandanaran No. 1, Simpang Lima, Semarang',
            'store_latitude' => -6.9903,
            'store_longitude' => 110.4229,
            'open_time' => '06:30',
            'close_time' => '22:00',
            'is_open' => 1,
            'shipping_rate_per_km' => 3000,
        ];

        $updateResponse = $this->actingAs($this->admin)->post('/admin/settings', $newSettings);
        $updateResponse->assertRedirect(route('admin.settings'));
        $updateResponse->assertSessionHas('success');

        // 3. Verify database reflects new values
        $this->assertDatabaseHas('store_settings', [
            'store_name' => 'Toko Sembako Simpang Lima',
            'shipping_rate_per_km' => 3000,
        ]);

        // 4. Verify API calculation uses new store coordinates
        $calcResponse = $this->postJson('/api/shipping-fee', [
            'lat' => -6.9903,
            'lng' => 110.4229,
        ]);

        $calcResponse->assertStatus(200);
        $this->assertEquals(-6.9903, $calcResponse->json('store_lat'));
        $this->assertEquals(0, $calcResponse->json('distance_km')); // exact location = 0 km
    }
}
