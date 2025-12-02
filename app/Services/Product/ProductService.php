<?php 
namespace App\Services\Product;

use App\Repositories\Hold\HoldRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(private ProductRepositoryInterface $productRepository, private HoldRepositoryInterface $holdRepository) {}

    public function getProductById($id)
    {
        $product = $this->productRepository->find($id);
        $availableStock = $this->calculateAvailableStock($product->id, $product->stock);
        $product->available_stock = $availableStock;
        return $product;
    }
    
    private function calculateAvailableStock($productId, $totalStock)
    {
        return Cache::remember("product:{$productId}:available_stock",
         5, function () use ($productId, $totalStock) {
             $activeHoldsQuantity = $this->holdRepository->sumActiveHolds($productId);       
            return max(0, $totalStock - $activeHoldsQuantity);
        });
    }

    public function clearStockCache($productId): void
    {
        Cache::forget("product:{$productId}");
        Cache::forget("product:{$productId}:available_stock");
        Cache::forget("product:{$productId}:active_holds");
    }
    
}