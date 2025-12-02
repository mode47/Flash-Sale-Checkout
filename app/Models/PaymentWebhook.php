<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;

class PaymentWebhook extends Model
{
    protected $fillable = [
       'idempotency_key',
        'order_id',
        'status',
        'payload',
        'attempts',
        'is_processed',
        'processed_at',
        'error_message',
        'failed_at'
    ];
    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
        public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
