<?php 
namespace App\Enums;

enum HoldStatusEnum: string 
{
    case HELD = 'held';
    case EXPIRED = 'expired';
    case USED = 'used'; 

    public static function getValues(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}