<?php
namespace Tests\Feature;
use App\Models\Product;
use App\Models\Hold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoldControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_hold_successfully()
    {
        $product = Product::factory()->create([
            'stock' => 10
        ]);

        $response = $this->postJson('/api/v1/hold', [
            'product_id' => $product->id,
            'quantity' => 3
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id',
                         'product_id',
                         'quantity',
                         'expires_at',
                         'status'
                     ]
                 ]);

        $this->assertDatabaseHas('holds', [
            'product_id' => $product->id,
            'quantity' => 3,
            'status' => 'held'
        ]);
    }


    /** @test */
    public function it_fails_to_create_hold_if_stock_insufficient()
    {
        $product = Product::factory()->create([
            'stock' => 2
        ]);

        $response = $this->postJson('/api/v1/hold', [
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // based on your error handler (maybe 400 or 422)
        $response->assertStatus(422);
    }

    /** @test */
    public function it_releases_expired_holds_successfully()
    {
        $product = Product::factory()->create(['stock' => 10]);

        $hold = Hold::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'held',
            'expires_at' => now()->subMinutes(5) // expired
        ]);

        $response = $this->getJson('/api/v1/hold/release-expired-holds');

        $response->assertStatus(200);

        $this->assertDatabaseHas('holds', [
            'id' => $hold->id,
            'status' => 'expired'
        ]);
    }
}
