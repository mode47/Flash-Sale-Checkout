<?php
namespace App\Repositories\Hold;
use App\Models\Hold;

interface HoldRepositoryInterface
{
    public function createHold($data);
    public function findWithProduct(int $holdId);
    public function find(int $holdId);
    public function sumActiveHolds(int $productId): int;
    public function getExpiredHolds();
    public function bulkUpdateStatus($expiredHoldIds, $status);

}