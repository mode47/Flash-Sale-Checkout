<?php
namespace App\Repositories\Hold\Eloquent;

use App\Enums\HoldStatusEnum;
use App\Repositories\Hold\HoldRepositoryInterface;
use App\Models\Hold;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class EloquentHoldRepository implements HoldRepositoryInterface
{
    public function __construct(
        private Hold $holdModel,
        private Product $productModel
    ){}

        public function createHold($data): Hold  
        {
            return DB::transaction(function() use ($data) {
                try {
                    $product = $this->lockProductForUpdates($data['product_id']);

                    $existingHolds = $this->sumActiveHolds($product->id);
                    $availableStock = $product->stock - $existingHolds;

                    if ($availableStock < $data['quantity']) {
                        throw new RuntimeException(
                            "Insufficient stock. Available: {$availableStock}, Requested: {$data['quantity']}"
                        );
                    }

                    
                    $hold = $this->holdModel->create([
                            'user_id' => Str::uuid(), // Assuming user_id is generated as UUID
                            'status' => HoldStatusEnum::HELD->value,
                            'expires_at' => now()->addMinutes(2)
                        ]);

                    $product->holds()->attach($hold->id, ['quantity' => $data['quantity']]);

                    return $hold;

                } catch (ModelNotFoundException $e) {
                    throw new RuntimeException("Product not found with id: {$data['product_id']}");
                }
            });
        }

    public function getExpiredHolds()
    {
        return $this->holdModel->where('status', HoldStatusEnum::HELD->value)
            ->where('expires_at', '<=', now())
            ->get();
    }

    public function bulkUpdateStatus($holdIds, $status)
    {
        $validStatuses = ['held', 'expired', 'released', 'used'];
        if (!in_array($status, $validStatuses)) {
            throw new InvalidArgumentException("Invalid status");
        }
        
        $updated = $this->holdModel->whereIn('id', $holdIds)
            ->update(['status' => $status]);
        
        if ($updated > 0) {
            
            $affectedProductIds = $this->holdModel->whereIn('id', $holdIds)
                ->distinct('product_id')
                ->pluck('product_id');
        }

        return [
            'affected_product_ids' => $affectedProductIds,
            'updated' => $updated
        ];
    }

    public function find(int $holdId): ?Hold
    {
        return $this->holdModel->findOrFail($holdId);
    }
    public function sumActiveHolds(int $productId): int
    {
        $product = $this->productModel->find($productId);
        return $product->holds->where('status', HoldStatusEnum::HELD->value)
            ->where('expires_at', '>', now())
            ->sum('pivot.quantity');
    }
    public function findWithProduct(int $holdId): ?Hold
    {
        return $this->holdModel->with('products') 
            ->find($holdId);
    }


    public function lockProductForUpdates(int $productId)
    {
        return $this->productModel->where('id', $productId)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
