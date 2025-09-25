<?php
// database/seeders/BrandSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Brand;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            ['name' => 'Apple', 'description' => 'Technology company known for iPhone, iPad, and Mac'],
            ['name' => 'Samsung', 'description' => 'South Korean electronics company'],
            ['name' => 'Nike', 'description' => 'Athletic footwear and apparel'],
            ['name' => 'Adidas', 'description' => 'German sportswear manufacturer'],
            ['name' => 'Sony', 'description' => 'Japanese electronics and entertainment company'],
            ['name' => 'Dell', 'description' => 'American computer technology company'],
            ['name' => 'HP', 'description' => 'Hewlett-Packard technology company'],
            ['name' => 'Zara', 'description' => 'Spanish fast fashion retailer'],
            ['name' => 'H&M', 'description' => 'Swedish fashion retailer'],
            ['name' => 'IKEA', 'description' => 'Swedish furniture and home goods company'],
            ['name' => 'LG', 'description' => 'South Korean electronics company'],
            ['name' => 'Microsoft', 'description' => 'American technology corporation'],
        ];

        foreach ($brands as $brand) {
            Brand::create([
                'name' => $brand['name'],
                'slug' => Str::slug($brand['name']),
                'description' => $brand['description'],
                'is_active' => true,
            ]);
        }
    }
}
