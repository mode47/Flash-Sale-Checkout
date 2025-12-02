<?php
namespace App\Services\Order;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Enums\PaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Support\Facades\Log;
class OrderPaymentService{
 public function __construct(
        private OrderRepositoryInterface $orderRepository
    ) {}       
    public function markAsPaid(Order $order, array $data = []): array
    {
        if ($order->payment_status === PaymentStatusEnum::PAID->value) {
            return $this->alreadyPaidResponse($order);
        }
        $updateData = [
            'payment_status' => PaymentStatusEnum::PAID->value,
            'paid_at' => now(),
            'order_status' => OrderStatusEnum::PROCESSING->value
        ];

        $this->orderRepository->update($order->id, $updateData);
        return [
            'success' => true,
            'message' => 'Payment successful',
            'order_status' => OrderStatusEnum::PROCESSING->value,
            'payment_status' => PaymentStatusEnum::PAID->value
        ];
    }
    public function markAsFailed(Order $order, array $data = []): array
    {
        if ($order->payment_status === PaymentStatusEnum::FAILED->value) {
            return $this->alreadyFailedResponse($order);
        }
        $this->orderRepository->update($order->id, [
            'payment_status' => PaymentStatusEnum::FAILED->value,
            'order_status' => OrderStatusEnum::CANCELLED->value,
            'failure_reason' => $data['failure_reason'] ?? $data['error_message'] ?? 'Unknown',
            'failed_at' => now()
        ]);
        Log::warning('Order payment failed', [
            'order_id' => $order->id,
            'payment_status' => PaymentStatusEnum::FAILED->value,
            'reason' => $data['failure_reason'] ?? 'Unknown'
        ]);
        return [
            'success' => false,
            'message' => 'Payment failed',
            'order_status' => OrderStatusEnum::CANCELLED->value,
            'payment_status' => PaymentStatusEnum::FAILED->value
        ];
    }
     public function markAsPartiallyRefunded(int $orderId, float $refundAmount): bool
    {
        $order = $this->orderRepository->find($orderId);
        
        if (!$order) {
            throw new \Exception("Order not found: {$orderId}");
        }
        return $this->orderRepository->update($orderId, [
            'payment_status' => PaymentStatusEnum::PARTIALLY_REFUNDED->value,
            'refund_amount' => $refundAmount,
            'refunded_at' => now()
        ]);
    }
    private function alreadyPaidResponse(Order $order): array
    {
        return [
            'success' => true,
            'message' => 'Order already paid',
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status
        ];
    }
     private function alreadyFailedResponse(Order $order): array
    {
        return [
            'success' => false,
            'message' => 'Payment already failed',
            'order_status' => $order->order_status,
            'payment_status' => $order->payment_status
        ];
    }
}