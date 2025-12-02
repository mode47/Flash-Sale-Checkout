<?php
namespace App\Enums;
enum PaymentStatusEnum :string{
    case PENDING='pending';
    case PAID='paid';
    case FAILED='failed';
    case REFUNDED='refunded';
    case PARTIALLY_REFUNDED='partially_refunded';
    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}