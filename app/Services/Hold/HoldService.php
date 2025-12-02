<?php
namespace App\Services\Hold;

use App\Enums\HoldStatusEnum;
use App\Repositories\Hold\HoldRepositoryInterface;
use RuntimeException;
use InvalidArgumentException;
use App\Models\Hold;
use App\Services\Product\ProductService;
use Illuminate\Support\Facades\Cache;
class HoldService
{
    public function __construct(
        private ProductService $productService,
        private HoldRepositoryInterface $holdsRepository,
    ){}
    public function createHold($data): Hold  
    {
        if ($data['quantity'] <= 0) {
            throw new InvalidArgumentException("Quantity must be greater than zero");  
        }
        $hold = $this->holdsRepository->createHold($data);
        $this->productService->clearStockCache($data['product_id']);
        $this->clearActiveHoldsCache($data['product_id']);
        return $hold;
    }

    public function clearActiveHoldsCache(int $productId): void
    {
        Cache::forget("product:{$productId}:active_holds");
        Cache::forget("product:{$productId}:available_stock");
    }

    public function releaseExpiredHolds(): array  
    {
        $startTime = microtime(true);
        $expiredHoldIds = $this->holdsRepository->getExpiredHolds()->pluck('id')->toArray();
        
        if (empty($expiredHoldIds)) {
            return [
                'total_expired' => 0,
                'released' => 0,
                'execution_time' => round(microtime(true) - $startTime, 4)
            ];
        }
        $result = $this->holdsRepository->bulkUpdateStatus($expiredHoldIds, HoldStatusEnum::EXPIRED->value);
        $releasedCount = $result['updated'];
        foreach ($result['affected_product_ids'] as $productId) {
            $this->clearActiveHoldsCache($productId);
        }
        return [
            'total_expired' => count($expiredHoldIds),
            'released' => $releasedCount,
            'failed' => count($expiredHoldIds) - $releasedCount,
            'execution_time' => round(microtime(true) - $startTime, 4)
        ];
    }
    public function getHoldDetails(int $holdId): Hold  
    {
        $hold = $this->holdsRepository->findWithProduct($holdId);
    
        if (!$hold) {
            throw new RuntimeException("Hold not found with id: {$holdId}");
        }

        $isValid = $this->validateHold($holdId);
        if (!$isValid['valid']) {
            throw new RuntimeException($isValid['message']);
        }
        return $hold;
    }
        public function getHoldWithProductDetails(int $holdId): Hold  
        {
            $hold = $this->holdsRepository->findWithProduct($holdId);
            if (!$hold) {
                throw new RuntimeException("Hold not found with id: {$holdId}");
            }       
            $isValid = $this->validateHold($holdId);
            if (!$isValid['valid']) {
                throw new RuntimeException($isValid['message']);
            }
            return $hold;
        }
    public function validateHold(int $holdId): array
    {
        $hold = $this->holdsRepository->find($holdId);
        if (!$hold) {
            return [
                'valid' => false,
                'message' => "Hold not found with id: {$holdId}",
            ];
        }

        if ($hold->status !== 'held') {
            return [
                'valid' => false,
                'message' => "Hold status is '{$hold->status}', not valid for use.",
            ];
        }

        if ($hold->expires_at <= now()) {
            return [
                'valid' => false,
                'message' => "Hold has expired.",
            ];
        }

        return [
            'valid' => true,
            'message' => "Hold is valid.",
        ];
    }
    public function markHoldAsUsed(int $holdId): void
{
    $hold = $this->holdsRepository->find($holdId);
    if (!$hold) {
        throw new RuntimeException("Hold not found with id: {$holdId}");
    }

    $hold->update([
        'status' => HoldStatusEnum::USED->value,
    ]);
}
}