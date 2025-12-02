<?php
namespace App\Transformers\API\V1;

use App\Models\Hold;
use Flugg\Responder\Transformers\Transformer;

class HoldTransformer extends Transformer
{
    /**
     * List of available relations.
     *
     * @var string[]
     */
    protected $relations = [];

    /**
     * List of autoloaded default relations.
     *
     * @var array
     */
    protected $load = [];

    /**
     * Transform the model.
     *
     * @param  \App\Models\Hold $hold
     * @return array
     */
    public function transform(Hold $hold)
    {
        $formattedProducts = $this->transformHoldProducts($hold);
        return [
            'hold_id' => $hold->id,
            'status' => $hold->status,
            'products' => $formattedProducts,
            'expires_at' => $hold->expires_at ? $hold->expires_at->toISOString() : null,
            'created_at' => $hold->created_at ? $hold->created_at->toISOString() : null,
            'expires_in_seconds' => max(0, now()->diffInSeconds($hold->expires_at, false)),
            'is_expired' => $hold->expires_at->isPast() || $hold->status === 'expired',
            'is_valid' => $hold->status === 'held' && !$hold->expires_at->isPast()
        ];
    }

    private function transformHoldProducts(Hold $hold)
    {
        $products = $hold->products()->get();
        $formattedProducts = [];
        foreach ($products as $product) {
            $formattedProducts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'image' => $product->image,
                'quantity' => $product->pivot->quantity ?? 0
            ];
        }
        return $formattedProducts;
    }
}
