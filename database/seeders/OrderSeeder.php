<?php
// database/seeders/OrderSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\User;
use App\Models\Product;

class OrderSeeder extends Seeder
{
    public function run()
    {
        $customers = User::where('role', 'customer')->with('client')->get();
        $products = Product::active()->get();

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery'];

        // Create 50 sample orders
        for ($i = 0; $i < 50; $i++) {
            $customer = $customers->random();
            $status = $statuses[array_rand($statuses)];
            $orderProducts = $products->random(rand(1, 5));

            $subtotal = 0;
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $subtotal += $product->price * $quantity;
            }

            $taxRate = 0.08; // 8% tax
            $taxAmount = $subtotal * $taxRate;
            $shippingAmount = $subtotal > 100 ? 0 : 15.99; // Free shipping over $100
            $totalAmount = $subtotal + $taxAmount + $shippingAmount;

            $billingAddress = $customer->client ? $customer->client->billing_address : [
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'address_line_1' => fake()->streetAddress(),
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'postal_code' => fake()->postcode(),
                'country' => 'US',
                'phone' => $customer->phone,
            ];

            $order = Order::create([
                'user_id' => $customer->id,
                'client_id' => $customer->client?->id,
                'status' => $status,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'shipping_amount' => $shippingAmount,
                'total_amount' => $totalAmount,
                'billing_address' => $billingAddress,
                'shipping_address' => $billingAddress,
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'payment_status' => in_array($status, ['delivered', 'shipped']) ? 'paid' :
                    ($status == 'cancelled' ? 'failed' : 'pending'),
                'notes' => rand(0, 1) ? fake()->sentence() : null,
                'shipped_at' => in_array($status, ['shipped', 'delivered']) ?
                    fake()->dateTimeBetween('-30 days', '-5 days') : null,
                'delivered_at' => $status == 'delivered' ?
                    fake()->dateTimeBetween('-5 days', 'now') : null,
                'created_at' => fake()->dateTimeBetween('-90 days', 'now'),
            ]);

            // Create order products
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $unitPrice = $product->price;
                $totalPrice = $unitPrice * $quantity;

                OrderProduct::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                    'product_options' => $product->attributes,
                ]);
            }
        }
    }
}
