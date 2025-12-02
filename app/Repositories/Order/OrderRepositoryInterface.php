<?php

namespace App\Repositories\Order;


interface OrderRepositoryInterface
{
    public function createOrder($data);
    public function find(int $orderId);
    public function update(int $id, array $data): bool; 
    public function updatePaymentStatus(int $id, string $paymentStatus, array $additionalData = []): bool;
    public function findByHoldId(int $holdId);
}