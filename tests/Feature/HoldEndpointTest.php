<?php
// tests/Feature/HoldEndpointTest.php
namespace Tests\Feature;


use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HoldEndpointTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_hold_successfully()
    {
        // Arrange
        $product = Product::factory()->create([
            'name' => 'Flash Sale Product', 
            'stock' => 100,
            'price' => 99.99
        ]);

        // Act
        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 5
        ]);

        // Assert
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'status' => 201
            ]);

        $this->assertDatabaseHas('holds', [
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'held'
        ]);
    }

    /** @test */
    public function it_returns_error_when_insufficient_stock()
    {
        $product = Product::factory()->create(['stock' => 5]);

        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 10
        ]);

        $response->assertStatus(409);
    }
}