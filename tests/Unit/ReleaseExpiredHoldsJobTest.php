<?php

namespace tests\Unit;

use App\Jobs\ReleaseExpiredHoldsJob;
use App\Models\Hold;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReleaseExpiredHoldsJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_marks_expired_holds_as_expired()
    {
        $product = Product::factory()->create(['stock' => 10]);

        $hold = Hold::factory()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'status' => 'held',
            'expires_at' => now()->subMinutes(10), // expired
        ]);

        // Run job manually
        (new ReleaseExpiredHoldsJob())->handle(app('App\Services\Hold\HoldService'));

        $this->assertDatabaseHas('holds', [
            'id' => $hold->id,
            'status' => 'expired'
        ]);
    }
}
