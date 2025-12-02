<?php
// tests/Feature/HoldExpiryTest.php  
namespace Tests\Feature;


use Tests\TestCase;
use App\Models\Product;
use App\Models\Hold;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class HoldExpiryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function expired_holds_do_not_affect_availability()
    {
        // Arrange
        $product = Product::factory()->create(['stock' => 10]);

        // عمل hold منتهي
        Hold::factory()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'status' => 'held',
            'expires_at' => Carbon::now()->subMinutes(5) // منتهي
        ]);

        // Act - محاولة عمل hold جديد
        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 8
        ]);

        // Assert - المفروض ينجح لأن الـ hold القديم منتهي
        $response->assertStatus(201);
    }
}