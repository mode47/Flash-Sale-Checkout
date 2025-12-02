<?php

namespace tests\Unit;

use Tests\TestCase;
use App\Services\Hold\HoldService;
use App\Repositories\Hold\HoldRepositoryInterface;
use App\Models\Product;
use App\Models\Hold;
use Mockery;
use Illuminate\Support\Carbon;

class HoldServiceTest extends TestCase
{
    protected $holdService;
    protected $holdRepositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock الـ repository
        $this->holdRepositoryMock = Mockery::mock(HoldRepositoryInterface::class);
        $this->holdService = new HoldService($this->holdRepositoryMock);
    }

    /** @test */
    public function it_creates_hold_successfully()
    {
        // Arrange
        $productId = 1;
        $quantity = 5;
        
        $product = Product::factory()->make(['id' => $productId]);
        $hold = Hold::factory()->make([
            'product_id' => $productId,
            'quantity' => $quantity
        ]);

        // Expectations
        $this->holdRepositoryMock->shouldReceive('lockProduct')
            ->with($productId)
            ->once()
            ->andReturn($product);

        $this->holdRepositoryMock->shouldReceive('sumActiveHoldsCache')
            ->with($productId)
            ->once()
            ->andReturn(0);

        $this->holdRepositoryMock->shouldReceive('createHold')
            ->with($productId, $quantity)
            ->once()
            ->andReturn($hold);

        $this->holdRepositoryMock->shouldReceive('clearActiveHoldsCache')
            ->with($productId)
            ->once();

        // Act
        $result = $this->holdService->createHold($productId, $quantity);

        // Assert
        $this->assertEquals($hold, $result);
    }

    /** @test */
    public function it_throws_exception_when_product_not_found()
    {
        // Expectations
        $this->holdRepositoryMock->shouldReceive('lockProduct')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(\App\Exceptions\ProductNotFoundException::class);

        // Act
        $this->holdService->createHold(999, 5);
    }

    /** @test */
    public function it_gets_hold_details_successfully()
    {
        // Arrange
        $holdId = 1;
        $hold = Hold::factory()->make(['id' => $holdId]);

        // Expectations
        $this->holdRepositoryMock->shouldReceive('findWithProduct')
            ->with($holdId)
            ->once()
            ->andReturn($hold);

        // Act
        $result = $this->holdService->getHoldDetails($holdId);

        // Assert
        $this->assertEquals($hold, $result);
    }

    /** @test */
    public function it_updates_hold_status_successfully()
    {
        // Arrange
        $holdId = 1;
        $status = 'released';
        $hold = Hold::factory()->make(['id' => $holdId]);

        // Expectations
        $this->holdRepositoryMock->shouldReceive('updateStatus')
            ->with($holdId, $status)
            ->once()
            ->andReturn(true);

        $this->holdRepositoryMock->shouldReceive('find')
            ->with($holdId)
            ->once()
            ->andReturn($hold);

        $this->holdRepositoryMock->shouldReceive('clearActiveHoldsCache')
            ->with($hold->product_id)
            ->once();

        // Act
        $result = $this->holdService->updateHoldStatus($holdId, $status);

        // Assert
        $this->assertTrue($result['success']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}