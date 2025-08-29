<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'customer_id' => \App\Models\Customer::factory(),
            'total' => 0, // will update after attaching products
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * After creating the order, attach products with pivot data (OrderProduct table).
     */
    public function configure()
    {
        return $this->afterCreating(function (Order $order) {
            // Pick 1–5 random products
            $products = Product::inRandomOrder()->take(rand(1, 5))->get();

            $total = 0;

            foreach ($products as $product) {
                $quantity = rand(1, 3);
                $price = $product->price * $quantity;

                $order->products()->attach($product->id, [
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                $total += $price;
            }

            // Update total price
            $order->update(['total' => $total]);
        });
    }
}
