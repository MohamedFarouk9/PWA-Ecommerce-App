<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderLifecycleTest extends TestCase
{
    // Feature test for full order checkout and payment security

    public function test_unauthenticated_request_cannot_create_order()
    {
        $response = $this->postJson('/api/orders', [
            'shipping_address' => '123 Test Ave',
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(401);
    }

    public function test_user_cannot_checkout_empty_cart()
    {
        $user = User::factory()->make(['id' => 9999]);
        $this->actingAs($user, 'api');

        $response = $this->postJson('/api/orders', [
            'shipping_address' => '123 Test Ave',
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(422);
    }
}
