<?php
// database/seeders/ClientSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;
use App\Models\User;

class ClientSeeder extends Seeder
{
    public function run()
    {
        $customers = User::where('role', 'customer')->get();

        foreach ($customers as $index => $user) {
            $isBusinessClient = $index % 3 == 0; // Every 3rd client is business

            $billingAddress = [
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'company' => $isBusinessClient ? $user->last_name . ' Corp' : null,
                'address_line_1' => fake()->streetAddress(),
                'address_line_2' => rand(0, 1) ? fake()->secondaryAddress() : null,
                'city' => fake()->city(),
                'state' => fake()->stateAbbr(),
                'postal_code' => fake()->postcode(),
                'country' => 'US',
                'phone' => $user->phone,
            ];

            Client::create([
                'user_id' => $user->id,
                'company_name' => $isBusinessClient ? $user->last_name . ' Corporation' : null,
                'tax_number' => $isBusinessClient ? 'TAX-' . strtoupper(fake()->lexify('???')) . fake()->numerify('###') : null,
                'billing_address' => $billingAddress,
                'shipping_address' => rand(0, 1) ? $billingAddress : [
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'company' => null,
                    'address_line_1' => fake()->streetAddress(),
                    'city' => fake()->city(),
                    'state' => fake()->stateAbbr(),
                    'postal_code' => fake()->postcode(),
                    'country' => 'US',
                    'phone' => $user->phone,
                ],
                'client_type' => $isBusinessClient ? 'business' : 'individual',
                'credit_limit' => $isBusinessClient ? rand(5000, 50000) : rand(1000, 5000),
                'current_balance' => rand(0, 1000),
                'payment_terms' => $isBusinessClient ? ['net_15', 'net_30', 'net_60'][rand(0, 2)] : 'immediate',
                'is_active' => true,
            ]);
        }
    }
}
