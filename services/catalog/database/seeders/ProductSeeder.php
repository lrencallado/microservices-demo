<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Bluetooth Headphones',
                'description' => 'High-quality wireless headphones with noise cancellation and 30-hour battery life.',
                'price' => 79.99,
                'stock' => 50,
                'image_url' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500',
            ],
            [
                'name' => 'Smart Watch Series 5',
                'description' => 'Fitness tracking, heart rate monitor, GPS, and water-resistant smartwatch.',
                'price' => 299.99,
                'stock' => 30,
                'image_url' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=500',
            ],
            [
                'name' => 'Laptop Stand Aluminum',
                'description' => 'Ergonomic laptop stand with adjustable height and angle for better posture.',
                'price' => 49.99,
                'stock' => 100,
                'image_url' => 'https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?w=500',
            ],
            [
                'name' => 'USB-C Hub 7-in-1',
                'description' => 'Multi-port USB-C hub with HDMI, USB 3.0, SD card reader, and power delivery.',
                'price' => 39.99,
                'stock' => 75,
                'image_url' => 'https://images.unsplash.com/photo-1625948515291-69613efd103f?w=500',
            ],
            [
                'name' => 'Mechanical Keyboard RGB',
                'description' => 'Gaming mechanical keyboard with customizable RGB lighting and blue switches.',
                'price' => 129.99,
                'stock' => 45,
                'image_url' => 'https://images.unsplash.com/photo-1587829741301-dc798b83add3?w=500',
            ],
            [
                'name' => 'Wireless Mouse Ergonomic',
                'description' => 'Ergonomic wireless mouse with adjustable DPI and rechargeable battery.',
                'price' => 34.99,
                'stock' => 120,
                'image_url' => 'https://images.unsplash.com/photo-1527814050087-3793815479db?w=500',
            ],
            [
                'name' => '4K Webcam Pro',
                'description' => 'Professional 4K webcam with autofocus, built-in microphone, and privacy shutter.',
                'price' => 89.99,
                'stock' => 60,
                'image_url' => 'https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?w=500',
            ],
            [
                'name' => 'Portable SSD 1TB',
                'description' => 'Ultra-fast portable SSD with 1TB storage, USB-C 3.2 Gen 2 interface.',
                'price' => 149.99,
                'stock' => 40,
                'image_url' => 'https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?w=500',
            ],
            [
                'name' => 'Phone Stand Adjustable',
                'description' => 'Universal adjustable phone stand for desk, compatible with all smartphones.',
                'price' => 19.99,
                'stock' => 200,
                'image_url' => 'https://images.unsplash.com/photo-1601524909162-ae8725290836?w=500',
            ],
            [
                'name' => 'LED Desk Lamp Smart',
                'description' => 'Smart LED desk lamp with app control, adjustable brightness and color temperature.',
                'price' => 59.99,
                'stock' => 85,
                'image_url' => 'https://images.unsplash.com/photo-1507473885765-e6ed057f782c?w=500',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
