<?php
namespace App\Enums;
enum OrderStatusEnum :string{
    case PENDING='pending';
    case PROCESSING='processing';
    case COMPLETED='completed';
    case CANCELLED='cancelled';
    case REFUNDED='refunded';


    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

    
