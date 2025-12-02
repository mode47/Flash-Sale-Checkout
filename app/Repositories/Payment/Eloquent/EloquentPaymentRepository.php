<?php
namespace App\Repositories\Payment\Eloquent;
use App\Repositories\Payment\PaymentRepositoryInterface;
use App\Models\PaymentWebhook;
class EloquentPaymentRepository implements PaymentRepositoryInterface

{
public function __construct(private PaymentWebhook $paymentWebhook) {}
   public function createWebHook(array $data):PaymentWebhook {
    
    return $this->paymentWebhook->create($data);
   }
public function findByIdempotencyKey(string $key): ?PaymentWebhook
{
    return $this->paymentWebhook->where('idempotency_key', $key)->first();

}
public function updateWebhookStatus(int $id, string $status, ?string $error = null): bool
    {
        $updateData = ['status' => $status];
        if ($error) {
            $updateData['error_message'] = $error;
        }   
        return $this->paymentWebhook->where('id', $id)->update($updateData);
    }
    public function incrementAttempts(int $id): bool
    {
        return $this->paymentWebhook->where('id', $id)->increment('attempts');
    }

    public function markAsProcessed(int $id): bool
    {
        return $this->paymentWebhook->where('id', $id)->update([
            'status' => 'processed',
            'is_processed' => true,
            'processed_at' => now()
        ]);
    }
    public function markAsFailed(int $id, string $errorMessage): bool
    {
        return $this->paymentWebhook->where('id', $id)->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
            'failed_at' => now()
        ]);
    }

}
