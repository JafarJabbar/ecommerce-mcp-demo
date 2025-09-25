<?php
// database/seeders/ProductSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::whereNotNull('parent_id')->get();
        $brands = Brand::all();

        $products = [
            // Electronics - Smartphones
            [
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest iPhone with A17 Pro chip and titanium design',
                'short_description' => 'Premium smartphone with advanced camera system',
                'price' => 999.00,
                'compare_price' => 1099.00,
                'stock_quantity' => 50,
                'category' => 'Smartphones',
                'brand' => 'Apple',
                'attributes' => ['color' => 'Natural Titanium', 'storage' => '128GB']
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Flagship Android phone with S Pen and AI features',
                'short_description' => 'Premium Android smartphone with stylus',
                'price' => 1199.00,
                'stock_quantity' => 30,
                'category' => 'Smartphones',
                'brand' => 'Samsung',
                'attributes' => ['color' => 'Titanium Black', 'storage' => '256GB']
            ],

            // Electronics - Laptops
            [
                'name' => 'MacBook Pro 14"',
                'description' => '14-inch MacBook Pro with M3 chip for professional workflows',
                'short_description' => 'Professional laptop for creative work',
                'price' => 1999.00,
                'stock_quantity' => 25,
                'category' => 'Laptops',
                'brand' => 'Apple',
                'attributes' => ['processor' => 'M3 Pro', 'memory' => '18GB', 'storage' => '512GB']
            ],
            [
                'name' => 'Dell XPS 13',
                'description' => 'Ultra-thin laptop with Intel Core i7 processor',
                'short_description' => 'Compact and powerful ultrabook',
                'price' => 1299.00,
                'compare_price' => 1399.00,
                'stock_quantity' => 40,
                'category' => 'Laptops',
                'brand' => 'Dell',
                'attributes' => ['processor' => 'Intel i7', 'memory' => '16GB', 'storage' => '512GB SSD']
            ],

            // Clothing - Men's Fashion
            [
                'name' => 'Classic Cotton T-Shirt',
                'description' => 'Premium cotton t-shirt with comfortable fit',
                'short_description' => 'Basic cotton tee for everyday wear',
                'price' => 29.99,
                'stock_quantity' => 100,
                'category' => 'Men\'s Fashion',
                'brand' => 'H&M',
                'attributes' => ['size' => 'M', 'color' => 'Navy Blue', 'material' => '100% Cotton']
            ],
            [
                'name' => 'Slim Fit Jeans',
                'description' => 'Modern slim fit jeans in dark wash',
                'short_description' => 'Contemporary denim with stretch',
                'price' => 79.99,
                'compare_price' => 99.99,
                'stock_quantity' => 75,
                'category' => 'Men\'s Fashion',
                'brand' => 'Zara',
                'attributes' => ['size' => '32W x 32L', 'color' => 'Dark Blue', 'fit' => 'Slim']
            ],

            // Sports & Outdoors - Fitness
            [
                'name' => 'Air Max 270',
                'description' => 'Nike Air Max 270 running shoes with air cushioning',
                'short_description' => 'Comfortable running shoes with air technology',
                'price' => 150.00,
                'stock_quantity' => 60,
                'category' => 'Fitness',
                'brand' => 'Nike',
                'attributes' => ['size' => '10', 'color' => 'Black/White', 'type' => 'Running']
            ],
            [
                'name' => 'Ultraboost 22',
                'description' => 'Adidas Ultraboost with responsive cushioning',
                'short_description' => 'Premium running shoes with boost technology',
                'price' => 180.00,
                'stock_quantity' => 45,
                'category' => 'Fitness',
                'brand' => 'Adidas',
                'attributes' => ['size' => '9.5', 'color' => 'Core Black', 'type' => 'Running']
            ],

            // Home & Garden - Furniture
            [
                'name' => 'MALM Bed Frame',
                'description' => 'Modern bed frame with clean lines and storage',
                'short_description' => 'Minimalist bed frame with under-bed storage',
                'price' => 249.00,
                'stock_quantity' => 20,
                'category' => 'Furniture',
                'brand' => 'IKEA',
                'attributes' => ['size' => 'Queen', 'color' => 'White', 'material' => 'Particleboard']
            ],
        ];

        foreach ($products as $productData) {
            $category = $categories->where('name', $productData['category'])->first();
            $brand = $brands->where('name', $productData['brand'])->first();

            if ($category && $brand) {
                Product::create([
                    'name' => $productData['name'],
                    'slug' => Str::slug($productData['name']),
                    'description' => $productData['description'],
                    'short_description' => $productData['short_description'],
                    'sku' => 'SKU-' . strtoupper(Str::random(8)),
                    'price' => $productData['price'],
                    'compare_price' => $productData['compare_price'] ?? null,
                    'cost_price' => $productData['price'] * 0.6, // 40% markup
                    'stock_quantity' => $productData['stock_quantity'],
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'status' => 'active',
                    'is_featured' => rand(0, 1) == 1,
                    'attributes' => $productData['attributes'],
                    'images' => [
                        'https://via.placeholder.com/800x600/000000/FFFFFF?text=' . urlencode($productData['name']),
                        'https://via.placeholder.com/800x600/333333/FFFFFF?text=' . urlencode($productData['name'] . '+2'),
                    ],
                    'weight' => rand(100, 5000) / 100, // Random weight between 1-50 lbs
                    'dimensions' => [
                        'length' => rand(5, 50),
                        'width' => rand(5, 30),
                        'height' => rand(1, 20)
                    ]
                ]);
            }
        }
    }
}
