<?php
namespace App\Services\Payment;

use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Services\Order\OrderPaymentService;
use App\Services\Hold\HoldService;  
use Illuminate\Support\Facades\DB;
use App\Models\PaymentWebhook;
use App\Enums\PaymentStatusEnum;
use Exception;

class PaymentService
{
    public function __construct(
        private PaymentRepositoryInterface $paymentRepository,
        private OrderPaymentService $orderPaymentService,
        private HoldService $holdService
    ) {}

    public function processWebhook(array $data): array
    {
        return DB::transaction(function () use ($data) {

            $idempotencyKey = $this->generateIdempotencyKey($data);

            // تحقق من وجود webhook مسبقًا
           
            $existingWebhook = $this->paymentRepository->findByIdempotencyKey($idempotencyKey);
          
            if ($existingWebhook) {
                $this->paymentRepository->incrementAttempts($existingWebhook->id);
                if ($existingWebhook->is_processed) {
                    // لو المعالجة تمت قبل كده
                    return $this->prepareAlreadyProcessedResponse($existingWebhook);
                }
                // لو موجود لكن لم يُعالج بعد، نستخدم نفس record
                $webhook = $existingWebhook;
            } else {
                // لو مش موجود، نعمل create
                $webhook = $this->createWebhook($data, $idempotencyKey);
            }

            try {
                $result = $this->processPayment($webhook, $data);

                // علم الـ webhook كمعالج
                $this->paymentRepository->markAsProcessed($webhook->id);

                // أضف http_status=200 للـ response
                return array_merge($result, ['http_status' => 200]);

            } catch (Exception $e) {
                $this->paymentRepository->markAsFailed($webhook->id, $e->getMessage());
                throw $e;
            }

        }, 3);
    }

    private function generateIdempotencyKey(array $data): string
    {
        $uniqueParts = [
            
            $data['order_id'] ?? 'unknown',
            $data['event_type'] ?? 'payment',
            $data['timestamp'] ?? 0
        ];

        return 'wh_' . hash('sha256', implode('|', $uniqueParts));
    }

    private function createWebhook(array $data, string $idempotencyKey): PaymentWebhook
    {
        $orderId = $this->extractOrderId($data);

        $payload = [
            'idempotency_key' => $idempotencyKey,
            'order_id' => $orderId,
            'payment_intent_id' => $data['payment_intent_id'] ?? null,
            'status' => 'processing',
            'payload' => $data,
            'attempts' => 1,
            'is_processed' => false
        ];
        return $this->paymentRepository->createWebHook($payload);
    }

    private function extractOrderId(array $data): int
    {
        if (isset($data['order_id'])) return (int) $data['order_id'];
        if (isset($data['metadata']['order_id'])) return (int) $data['metadata']['order_id'];
        if (isset($data['data']['object']['metadata']['order_id'])) return (int) $data['data']['object']['metadata']['order_id'];
        throw new \Exception('Order ID not found in webhook data');
    }

    private function processPayment(PaymentWebhook $webhook, array $data): array
    {
            if (!$webhook->order) {
                return [
                    'success' => false,
                    'message' => 'Order not found',
                    'http_status' => 404
                ];
            }
        if (!$webhook->order) {
            throw new \Exception("Order not found for webhook");
        }
        $paymentStatus = $data['status'] ?? 'unknown';
        $successStatuses = ['succeeded', 'paid', 'completed', PaymentStatusEnum::PAID->value];
        $failedStatuses = ['failed', 'canceled', 'refunded', PaymentStatusEnum::FAILED->value, PaymentStatusEnum::REFUNDED->value];
        if (in_array($paymentStatus, $successStatuses, true)) {
            return $this->orderPaymentService->markAsPaid($webhook->order, $data);
        }

        if (in_array($paymentStatus, $failedStatuses, true)) {
            $result = $this->orderPaymentService->markAsFailed($webhook->order, $data);

            if ($webhook->order->hold_id) {
                $this->holdService->releaseHold($webhook->order->hold_id);
            }

            return $result;
        }

        if ($paymentStatus === PaymentStatusEnum::PARTIALLY_REFUNDED->value) {
            return $this->handlePartialRefund($webhook->order, $data);
        }
        throw new \Exception("Unknown payment status: {$paymentStatus}");
    }
    private function handlePartialRefund($order, array $data): array
    {
        $refundAmount = $data['refund_amount'] ?? 0;
        $this->orderPaymentService->markAsPartiallyRefunded($order->id, $refundAmount);

        return [
            'success' => true,
            'message' => 'Partial refund processed',
            'order_status' => $order->order_status,
            'payment_status' => PaymentStatusEnum::PARTIALLY_REFUNDED->value,
            'refund_amount' => $refundAmount,
            'http_status' => 200
        ];
    }

    private function prepareAlreadyProcessedResponse(PaymentWebhook $webhook): array
    {
        return [
            'success' => true,
            'message' => 'Webhook already processed',
            'order_status' => $webhook->order->order_status ?? null,
            'payment_status' => $webhook->order->payment_status ?? null,
            'processed_at' => $webhook->processed_at,
            'http_status' => 200
        ];
    }
}
