<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates sample products for testing
     */
    public function run(): void
    {
        // Array of sample products
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop for professionals',
                'price' => 999.99,
                'stock' => 50,
                'image_url' => null,
            ],
            [
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone with advanced features',
                'price' => 699.99,
                'stock' => 100,
                'image_url' => null,
            ],
            [
                'name' => 'Headphones',
                'description' => 'Noise-cancelling wireless headphones',
                'price' => 199.99,
                'stock' => 75,
                'image_url' => null,
            ],
            [
                'name' => 'Keyboard',
                'description' => 'Mechanical gaming keyboard with RGB lighting',
                'price' => 129.99,
                'stock' => 60,
                'image_url' => null,
            ],
            [
                'name' => 'Mouse',
                'description' => 'Ergonomic wireless mouse',
                'price' => 49.99,
                'stock' => 120,
                'image_url' => null,
            ],
            [
                'name' => 'Monitor',
                'description' => '27-inch 4K UHD monitor',
                'price' => 399.99,
                'stock' => 40,
                'image_url' => null,
            ],
            [
                'name' => 'USB Cable',
                'description' => 'Fast charging USB-C cable',
                'price' => 19.99,
                'stock' => 200,
                'image_url' => null,
            ],
            [
                'name' => 'Tablet',
                'description' => '10-inch tablet perfect for entertainment',
                'price' => 449.99,
                'stock' => 80,
                'image_url' => null,
            ],
            [
                'name' => 'Webcam',
                'description' => '1080p HD webcam for video conferencing',
                'price' => 79.99,
                'stock' => 55,
                'image_url' => null,
            ],
            [
                'name' => 'External SSD',
                'description' => '1TB portable solid state drive',
                'price' => 149.99,
                'stock' => 90,
                'image_url' => null,
            ],
        ];

        // Create each product
        foreach ($products as $product) {
            Product::create($product);
        }

        // Output success message
        $this->command->info('Products created successfully!');
    }
}