<?php
namespace App\Repositories\Product\Eloquent;
use App\Models\Product;

use App\Repositories\Product\ProductRepositoryInterface;


class EloquentProductRepository implements ProductRepositoryInterface
{
    public function __construct(private Product $productModel) {}

    public function find($id)
    {
        return $this->productModel
            ->select(['id','name','description','stock','price','image'])
            ->where('id', $id)
            ->firstOrFail();
    }

}