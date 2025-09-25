<?php
// database/seeders/CategorySeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and gadgets',
                'children' => [
                    ['name' => 'Smartphones', 'description' => 'Mobile phones and accessories'],
                    ['name' => 'Laptops', 'description' => 'Portable computers and notebooks'],
                    ['name' => 'Audio', 'description' => 'Headphones, speakers, and audio equipment'],
                    ['name' => 'Gaming', 'description' => 'Gaming consoles and accessories'],
                ]
            ],
            [
                'name' => 'Clothing',
                'description' => 'Fashion and apparel',
                'children' => [
                    ['name' => 'Men\'s Fashion', 'description' => 'Clothing for men'],
                    ['name' => 'Women\'s Fashion', 'description' => 'Clothing for women'],
                    ['name' => 'Shoes', 'description' => 'Footwear for all occasions'],
                    ['name' => 'Accessories', 'description' => 'Fashion accessories'],
                ]
            ],
            [
                'name' => 'Home & Garden',
                'description' => 'Home improvement and garden supplies',
                'children' => [
                    ['name' => 'Furniture', 'description' => 'Home and office furniture'],
                    ['name' => 'Kitchen', 'description' => 'Kitchen appliances and utensils'],
                    ['name' => 'Garden', 'description' => 'Garden tools and plants'],
                    ['name' => 'Decor', 'description' => 'Home decoration items'],
                ]
            ],
            [
                'name' => 'Sports & Outdoors',
                'description' => 'Sports equipment and outdoor gear',
                'children' => [
                    ['name' => 'Fitness', 'description' => 'Gym and fitness equipment'],
                    ['name' => 'Outdoor Sports', 'description' => 'Outdoor activities equipment'],
                    ['name' => 'Team Sports', 'description' => 'Equipment for team sports'],
                ]
            ],
        ];

        foreach ($categories as $categoryData) {
            $parent = Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'is_active' => true,
            ]);

            if (isset($categoryData['children'])) {
                foreach ($categoryData['children'] as $index => $childData) {
                    Category::create([
                        'name' => $childData['name'],
                        'slug' => Str::slug($childData['name']),
                        'description' => $childData['description'],
                        'parent_id' => $parent->id,
                        'is_active' => true,
                        'sort_order' => $index,
                    ]);
                }
            }
        }
    }
}
