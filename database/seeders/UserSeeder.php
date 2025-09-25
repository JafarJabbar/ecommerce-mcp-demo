<?php
// database/seeders/UserSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create sample customers
        $customers = [
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '+1-555-0123',
                'date_of_birth' => '1985-06-15',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '+1-555-0124',
                'date_of_birth' => '1990-03-22',
                'gender' => 'female',
            ],
            [
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@example.com',
                'phone' => '+1-555-0125',
                'date_of_birth' => '1988-11-08',
                'gender' => 'male',
            ],
            [
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1-555-0126',
                'date_of_birth' => '1992-09-14',
                'gender' => 'female',
            ],
            [
                'first_name' => 'Robert',
                'last_name' => 'Wilson',
                'email' => 'robert.wilson@example.com',
                'phone' => '+1-555-0127',
                'date_of_birth' => '1983-01-30',
                'gender' => 'male',
            ],
        ];

        foreach ($customers as $customer) {
            User::create(array_merge($customer, [
                'password' => Hash::make('password'),
                'role' => 'customer',
                'is_active' => true,
                'email_verified_at' => now(),
            ]));
        }
    }
}
