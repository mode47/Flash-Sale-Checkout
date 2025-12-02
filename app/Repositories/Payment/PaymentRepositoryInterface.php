<?php
namespace App\Repositories\Payment;
use App\Models\PaymentWebhook;
interface PaymentRepositoryInterface
{

    
    public function findByIdempotencyKey(string $key): ?PaymentWebhook;
    public function createWebHook(array $data): PaymentWebhook;
    public function updateWebhookStatus(int $id, string $status, ?string $error = null): bool;
    public function incrementAttempts(int $id): bool;
    public function markAsProcessed(int $id): bool;
    public function markAsFailed(int $id, string $errorMessage): bool;


}