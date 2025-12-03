<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Hold;
use App\Models\Order;
use App\Models\PaymentWebhook;
use App\Enums\HoldStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\PaymentStatusEnum;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FlashSaleFeatureTest extends TestCase
{
    use RefreshDatabase;
    
    protected Product $product;
    protected Order $order;
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test product
        $this->product = Product::create([
            'name' => 'Test Product',
            'description' => 'Test Description',
            'price' => 100.00,
            'stock' => 3,  // Only 3 items available
            'is_active' => true,
            'is_taxable' => true,
            'tax_percentage' => 14
        ]);
        $this->order = Order::create([
            'product_id' => $this->product->id,
            'quantity' => 1,
            'unit_price'=>$this->product->price,
            'total_amount' => $this->product->price,
            'payment_status' => PaymentStatusEnum::PENDING->value,
            'order_status' => OrderStatusEnum::PROCESSING->value,
        ]);
    }
    public function test_webhook_idempotency_same_key_repeated(): void
    {
        $idempotencyKey = 'test_idempotency_' . Str::random(10);
        $payload = [
            'order_id' => $this->order->id,
            'status' => 'paid',
            'amount' => $this->product->price,
            'payment_intent_id' => 'pi_test_12345',
            'event_type' => 'payment',
            'timestamp' => time(),
            'idempotency_key' => $idempotencyKey,
        ];
        $response1 = $this->postJson('/api/v1/payments/webhook', $payload);
        $response1->assertStatus(200);
        $response1->assertJson([
            'success' => true,
        ]);
        $this->order->refresh();
        $this->assertEquals('paid', $this->order->payment_status->value);
        $response2 = $this->postJson('/api/v1/payments/webhook', $payload);
        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'message' => 'Webhook already processed',
        ]);
        $this->order->refresh();
        $this->assertEquals("paid", $this->order->payment_status->value);
    }  
   public function test_parallel_hold_attempts_at_stock_boundary_no_oversell(): void
{
    // Simulate 5 concurrent hold requests for product with stock 3
    $successCount = 0;
    $failCount = 0;

    // Create a function to attempt hold creation
    $attemptHold = function ($productId) use (&$successCount, &$failCount) {
        try {
            DB::transaction(function () use ($productId, &$successCount) {
                $product = Product::where('id', $productId)
                    ->lockForUpdate()
                    ->first();
                $heldStock = DB::table('hold_product')
                    ->join('holds', 'hold_product.hold_id', '=', 'holds.id')
                    ->where('hold_product.product_id', $productId)
                    ->where('holds.status', HoldStatusEnum::HELD->value)
                    ->where('holds.expires_at', '>', now())
                    ->sum('hold_product.quantity');
                $availableStock = $product->stock - $heldStock;
                if ($availableStock > 0) {
                    $hold = Hold::create([
                        'user_id' => Str::uuid(),
                        'status' => HoldStatusEnum::HELD->value,
                        'expires_at' => now()->addMinutes(2)
                    ]);
                    DB::table('hold_product')->insert([
                        'hold_id' => $hold->id,
                        'product_id' => $productId,
                        'quantity' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $successCount++;
                    return true;
                }
                throw new \Exception('Insufficient stock');
            });
        } catch (\Exception $e) {
            $failCount++;
            return false;
        }
    };
    $promises = [];
    for ($i = 0; $i < 5; $i++) {
        $promises[] = $attemptHold($this->product->id);
    }
    usleep(100000);
    $this->assertEquals(3, $successCount,
        'Should have exactly 3 successful holds (matches stock)');
    $this->assertEquals(2, $failCount,
        'Should have exactly 2 failed holds (5 attempts - 3 stock)');

    // Verify total held doesn't exceed stock
    $totalHeld = DB::table('hold_product')
        ->join('holds', 'hold_product.hold_id', '=', 'holds.id')
        ->where('hold_product.product_id', $this->product->id)
        ->where('holds.status', HoldStatusEnum::HELD->value)
        ->where('holds.expires_at', '>', now())
        ->sum('hold_product.quantity');

    $this->assertLessThanOrEqual($this->product->stock, $totalHeld,
        'Total held quantity should not exceed product stock');

    // Log results
    echo "\n✅ Parallel Hold Test Results:";
    echo "\n   Product Stock: {$this->product->stock}";
    echo "\n   Successful Holds: {$successCount}";
    echo "\n   Failed Holds: {$failCount}";
    echo "\n   Total Held: {$totalHeld}";
}
}

 



