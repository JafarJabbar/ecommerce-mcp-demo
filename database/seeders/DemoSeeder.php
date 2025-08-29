<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        Category::factory(5)
            ->has(Product::factory(10)) // each category has 10 products
            ->create();

        Order::factory(10)->create()->each(function ($order) {
            $products = Product::inRandomOrder()->take(rand(1, 5))->get();

            $total = 0;
            foreach ($products as $product) {
                $qty = rand(1, 3);
                $order->products()->attach($product->id, [
                    'quantity' => $qty,
                    'price' => $product->price,
                ]);
                $total += $product->price * $qty;
            }

            $order->update(['total' => $total]);
        });
    }
}
