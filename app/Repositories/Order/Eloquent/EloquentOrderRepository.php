<?php

namespace App\Repositories\Order\Eloquent;

use App\Models\Order;
use App\Models\Hold;
use App\Repositories\Order\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private Order $orderModel,
        private Hold $holdModel
    ) {}
    
    public function createOrder( $data):Order
    {
      return DB::transaction(function() use ($data) {
        return $this->orderModel->create($data);
    });
    }
    
    public function find(int $orderId): ?Order
    {
        return $this->orderModel->find($orderId);
    }
    
    public function findByHoldId(int $holdId): ?Order
    {
        return $this->orderModel->where('hold_id', $holdId)->first();
    }
    public function update(int $id, array $data): bool
    {
        return $this->orderModel->where('id', $id)->update($data);
    }
    
    public function updatePaymentStatus(int $id, string $paymentStatus, array $additionalData = []): bool
    {
        $data = array_merge(['payment_status' => $paymentStatus], $additionalData);
        return $this->orderModel->where('id', $id)->update($data);
    }
}