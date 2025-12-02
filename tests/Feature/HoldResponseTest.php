<?php
// tests/Feature/HoldResponseTest.php
namespace Tests\Feature;


use Tests\TestCase;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HoldResponseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_correct_response_structure()
    {
        $product = Product::factory()->create(['stock' => 10]);

        $response = $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 2
        ]);

        $response->assertJsonStructure([
            'success',
            'status',
            'message', 
            'data' => [
                'hold_id',
                'product_id',
                'quantity',
                'expires_at',
                'status'
            ]
        ]);

        // التأكد من أنواع البيانات
        $responseData = $response->json('data');
        $this->assertIsInt($responseData['hold_id']);
        $this->assertIsInt($responseData['product_id']);
        $this->assertIsInt($responseData['quantity']);
        $this->assertIsString($responseData['expires_at']);
        $this->assertIsString($responseData['status']);
    }
}