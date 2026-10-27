<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        $calzado = Category::create([
            'name' => 'Calzado',
            'slug' => 'calzado',
        ]);

        $tecnologia = Category::create([
            'name' => 'Tecnología y Audio',
            'slug' => 'tecnologia-audio',
        ]);

        Product::create([
            'category_id' => $calzado->id,
            'name' => 'Zapatos Deportivos',
            'slug' => 'zapatos-deportivos',
            'description' => 'Calzado cómodo para uso diario',
            'price' => 59.99,
            'stock' => 15,
            'image' => 'products/Shoes.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $tecnologia->id,
            'name' => 'Audífonos Inalámbricos',
            'slug' => 'audifonos-inalambricos',
            'description' => 'Audífonos de alta fidelidad con aislamiento de ruido',
            'price' => 89.90,
            'stock' => 12,
            'image' => 'products/Headphones.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $tecnologia->id,
            'name' => 'iPhone',
            'slug' => 'iphone',
            'description' => 'Smartphone de última generación',
            'price' => 799.00,
            'stock' => 8,
            'image' => 'products/iPhone.jpg',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $tecnologia->id,
            'name' => 'MacBook',
            'slug' => 'macbook',
            'description' => 'Laptop portátil para desarrollo y productividad',
            'price' => 1299.00,
            'stock' => 5,
            'image' => 'products/Mac.jpg',
            'is_active' => true,
        ]);
    }
}