<?php
namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        Product::firstOrCreate([
            'name' => 'Sample Product',
            'description' => 'This is a sample product description.',
            'stock' => 1000000,
            'price' => 29.99,
            'image' => 'images/sample-product.jpg',
        ]);
    }
}