<?php

namespace Database\Factories;

use App\Models\Hold;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class HoldFactory extends Factory
{
    protected $model = Hold::class;

    public function definition()
    {
        return [
            'product_id' => Product::factory(),
            'quantity' => $this->faker->numberBetween(1, 10),
            'status' => 'held',
            'expires_at' => Carbon::now()->addMinutes($this->faker->numberBetween(10, 60)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'expires_at' => Carbon::now()->subMinutes(10),
            ];
        });
    }

    public function released()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'released',
            ];
        });
    }

    public function used()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'used',
            ];
        });
    }

    public function withProduct(Product $product)
    {
        return $this->state(function (array $attributes) use ($product) {
            return [
                'product_id' => $product->id,
            ];
        });
    }
}