<?php
namespace App\Services\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\Hold\HoldRepositoryInterface;
use App\Repositories\Product\ProductRepositoryInterface;
use Illuminate\Support\Facades\DB;
use App\Services\Hold\HoldService;
use App\Models\Order;
use Exception;
class OrderService
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
         private HoldService  $holdService 
    ) {}
public function createOrder(int $holdId): Order
{
    $hold = $this->holdService->getHoldWithProductDetails($holdId);
    if (!$hold) {
        throw new RuntimeException("Hold not found with id: {$holdId}");
    }
    $product = $hold->products->first();
    if (!$product) {
        throw new RuntimeException("Product not found for this hold.");
    }
    $quantity = $product->pivot->quantity;
    $totals = $this->calculateTotals($product, $quantity);
    $data = [
        'hold_id'        => $hold->id,
        'product_id'     => $product->id,
        'quantity'       => $quantity,
        'order_status'   => 'pending',
        'payment_status' => 'pending',
        'is_taxable'     => $product->is_taxable,
        'tax_percentage' => $product->tax_percentage ?? 0,
    ];
        $data =array_merge($data, $totals);
        $order = $this->orderRepository->createOrder($data);
        $this->holdService->markHoldAsUsed($holdId);;
    return $order;
}
    public function getOrderById(int $orderId)
    {
        return $this->orderRepository->find($orderId);
    }
    private function calculateTotals($product, $quantity): array {
    $unit_price = $product->price;
    $subtotal = $unit_price * $quantity;
    $tax_amount = $product->is_taxable ? ($subtotal * ($product->tax_percentage ?? 0)) / 100 : 0;
    $total_amount = $subtotal + $tax_amount;
    return compact('unit_price', 'subtotal', 'tax_amount', 'total_amount');
}
}