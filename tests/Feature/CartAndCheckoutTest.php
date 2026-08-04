<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_index_calculates_weight_for_selected_items_only()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $product1 = Product::create([
            'name' => 'Kurma Ajwa',
            'description' => 'Premium dates',
            'category' => 'Dates',
            'base_price' => 20.00,
            'stock' => 100,
            'weight' => 0.50, // 0.5kg
        ]);

        $product2 = Product::create([
            'name' => 'Madu Sidr',
            'description' => 'Pure honey',
            'category' => 'Honey',
            'base_price' => 50.00,
            'stock' => 50,
            'weight' => 1.20, // 1.2kg
        ]);

        // Cart item 1 (quantity 2, weight = 1.0kg, selected = true)
        $item1 = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 2,
            'is_selected' => true,
        ]);

        // Cart item 2 (quantity 1, weight = 1.2kg, selected = false)
        $item2 = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 1,
            'is_selected' => false,
        ]);

        $response = $this->actingAs($user)->get(route('shop.cart'));

        $response->assertStatus(200);
        // Subtotal should be RM40.00 (only product 1)
        $response->assertViewHas('subtotal', 40.00);
        // Total weight should be 1.00kg (only product 1 * 2)
        $response->assertViewHas('totalWeight', 1.00);
    }

    public function test_update_selection_endpoint_updates_database_and_returns_correct_totals_and_weight()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $product1 = Product::create([
            'name' => 'Product 1',
            'category' => 'Dates',
            'base_price' => 10.00,
            'stock' => 10,
            'weight' => 0.40,
        ]);

        $product2 = Product::create([
            'name' => 'Product 2',
            'category' => 'Honey',
            'base_price' => 30.00,
            'stock' => 10,
            'weight' => 0.80,
        ]);

        $item1 = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product1->id,
            'quantity' => 1,
            'is_selected' => false,
        ]);

        $item2 = CartItem::create([
            'user_id' => $user->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'is_selected' => false,
        ]);

        // Select both items
        $response = $this->actingAs($user)->postJson(route('cart.update-selection'), [
            'checked_ids' => [$item1->id, $item2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'subtotal' => '70.00',
                'total_weight' => 2.00,
                'total_weight_formatted' => '2.00 kg',
                'total' => '70.00',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item1->id,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item2->id,
            'is_selected' => true,
        ]);

        // Deselect item 2
        $response2 = $this->actingAs($user)->postJson(route('cart.update-selection'), [
            'checked_ids' => [$item1->id],
        ]);

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'subtotal' => '10.00',
                'total_weight' => 0.40,
                'total_weight_formatted' => '0.40 kg',
                'total' => '10.00',
            ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item1->id,
            'is_selected' => true,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'id' => $item2->id,
            'is_selected' => false,
        ]);
    }

    public function test_easyparcel_shipping_rates_uses_provided_weight()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        // Query shipping rates for West Malaysia (Postcode 47100, Selangor) with 2.50kg weight
        $response = $this->actingAs($user)->get(route('checkout.shipping_rates', [
            'postcode' => '47100',
            'state' => 'Selangor',
            'weight' => 2.50,
        ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'rates' => [
                    '*' => ['service_id', 'service_name', 'courier_name', 'price', 'delivery']
                ]
            ]);

        $rates = $response->json('rates');
        $this->assertNotEmpty($rates);
    }

    public function test_easyparcel_oauth2_open_api_fetches_live_rates()
    {
        \Illuminate\Support\Facades\Http::fake([
            '*oauth/token*' => \Illuminate\Support\Facades\Http::response([
                'access_token' => 'mock_oauth_access_token_123',
                'expires_in'   => 3600,
            ], 200),
            '*EPRateCheckingBulk*' => \Illuminate\Support\Facades\Http::response([
                'api_status' => 'Success',
                'result' => [
                    [
                        'rates' => [
                            [
                                'service_id'   => 'EP-SERVICE-1',
                                'service_name' => 'Next Day Delivery',
                                'courier_name' => 'Kurier Standard Live',
                                'price'        => '9.90',
                                'delivery'     => '1-2 days',
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        config([
            'services.easyparcel.client_id' => 'test_client_id',
            'services.easyparcel.client_secret' => 'test_client_secret',
        ]);

        $service = new \App\Services\EasyParcelService();
        $token = $service->getAccessToken();
        $this->assertEquals('mock_oauth_access_token_123', $token);

        $rates = $service->getRates('47100', 1.0, 'Selangor');
        $this->assertNotEmpty($rates);
        $this->assertEquals('Kurier Standard Live', $rates[0]['courier_name']);
        $this->assertTrue($rates[0]['is_live']);
    }
}
