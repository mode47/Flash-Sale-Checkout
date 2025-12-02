<?php
namespace App\Models;
use App\Enums\PaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'hold_id',
        'product_id',
        'quantity',
        'subtotal',
        'tax_amount',
        'unit_price',
        'total_amount',
        'is_taxable',
        'paid_at',
        'completed_at',
        'order_status',
        'payment_status'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'order_status' => OrderStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class
    ];

    /**
     * Relationships
     */
    public function hold()
    {
        return $this->belongsTo(Hold::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function webhooks()
    {
        return $this->hasMany(PaymentWebhook::class);
    }

    /**
     * Status Check Methods
     */
    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatusEnum::PAID;
    }

    public function isFailed(): bool
    {
        return $this->payment_status === PaymentStatusEnum::FAILED;
    }

    public function isPending(): bool
    {
        return $this->payment_status === PaymentStatusEnum::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->order_status === OrderStatusEnum::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->order_status === OrderStatusEnum::CANCELLED;
    }

    public function isProcessing(): bool
    {
        return $this->order_status === OrderStatusEnum::PROCESSING;
    }

    /**
     * Payment Status Methods
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === PaymentStatusEnum::PENDING;
    }

    public function isPaymentCompleted(): bool
    {
        return in_array($this->payment_status, [
            PaymentStatusEnum::PAID,
            PaymentStatusEnum::REFUNDED,
            PaymentStatusEnum::PARTIALLY_REFUNDED
        ]);
    }
    
    


  

    public function markAsPaid(): bool
    {
        return $this->update([
            'payment_status' => PaymentStatusEnum::PAID,
            'paid_at' => now()
        ]);
    }

    public function markAsFailed(): bool
    {
        return $this->update([
            'payment_status' => PaymentStatusEnum::FAILED
        ]);
    }
}