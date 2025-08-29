<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->unique()->words(3, true),
            'price' => $this->faker->randomFloat(2, 10, 500), // 10–500 AZN/USD/etc.
            'stock' => $this->faker->numberBetween(0, 100),
        ];
    }
}
