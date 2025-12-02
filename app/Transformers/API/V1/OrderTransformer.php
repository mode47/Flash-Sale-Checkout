<?php

namespace App\Transformers\API\V1;

use League\Fractal\TransformerAbstract;
use App\Models\Order;
use Carbon\Carbon;

class OrderTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     */
    protected array $defaultIncludes = [
        'product',
        'hold'
    ];
    protected array $availableIncludes = [
        'product.details',
        'hold.product'
    ];
    public function transform(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number ?? null,
            'hold_id' => $order->hold_id,
            'product_id' => $order->product_id,
            'quantity' => (int) $order->quantity,
            'pricing' => [
                'unit_price' => (float) $order->unit_price,
                'subtotal' => (float) $order->subtotal,
                'tax_amount' => (float) $order->tax_amount,
                'total_amount' => (float) $order->total_amount,
                'is_taxable' => (bool) $order->is_taxable,
                'tax_percentage' => $order->tax_percentage,
                'formatted' => [
                    'unit_price' => $order->formatted_unit_price,
                    'subtotal' => $order->formatted_subtotal,
                    'tax_amount' => $order->formatted_tax_amount,
                    'total_amount' => $order->formatted_total_amount,
                ]
            ],
            'status' => [
                'order_status' => $order->order_status,
                'payment_status' => $order->payment_status,
                'is_paid' => $order->isPaid(),
                'is_pending' => $order->isPending(),
                'is_completed' => $order->isCompleted(),
                'is_cancelled' => $order->isCancelled(),
                'labels' => [
                    'order_status' => $order->order_status->name,
                    'payment_status'=>$order->payment_status->name
                ]
            ],
            'timestamps' => [
                'paid_at' => $order->paid_at ? Carbon::parse($order->paid_at)->toISOString() : null,
                'completed_at' => $order->completed_at ? Carbon::parse($order->completed_at)->toISOString() : null,
                'created_at' => $order->created_at ? Carbon::parse($order->created_at)->toISOString() : null,
                'updated_at' => $order->updated_at ? Carbon::parse($order->updated_at)->toISOString() : null,
                'formatted' => [
                    'paid_at' => $order->paid_at ? $order->paid_at->format('Y-m-d H:i:s') : null,
                    'completed_at' => $order->completed_at ? $order->completed_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $order->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $order->updated_at->format('Y-m-d H:i:s'),
                ]
            ],
     
            
            
        ];
    }
    public function includeProduct(Order $order)
    {
        if (!$order->product) {
            return null;
        }

        return $this->item($order->product, new ProductTransformer());
    }
    public function includeHold(Order $order){
        if (!$order->hold) {
            return null;
        }
        return $this->item($order->hold, new HoldTransformer());
    }
}