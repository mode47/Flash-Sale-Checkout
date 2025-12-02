<?php
namespace App\Transformers\API\V1;

use App\Models\Product;
use Flugg\Responder\Transformers\Transformer;

class ProductTransformer extends Transformer
{
    /**
     * Transform the given model.
     *
     * @param \App\Models\Product $product
     * @return array
     */
    public function transform(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'total_stock' => $product->stock,
            'available_stock' => $product->available_stock ?? 0,
            'image' => $product->image,
            'is_active' => (bool) $product->is_active,
            'is_available' => $product->available_stock > 0,
            'created_at' => $product->created_at ?$product->created_at->toISOString():null,
            'updated_at' => $product->updated_at ? $product->updated_at->toISOString() : null 
        ];
    }
}