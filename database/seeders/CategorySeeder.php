<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed Chapung Art default categories.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Lukisan Papua', 'type' => 'artwork'],
            ['name' => 'Fotografi Budaya', 'type' => 'photography'],
            ['name' => 'Berita Seni Budaya', 'type' => 'post'],
            ['name' => 'Ukiran Papua', 'type' => 'artwork'],
            ['name' => 'Marketplace Seni', 'type' => 'artwork'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'type' => $category['type'],
                    'is_active' => true,
                ]
            );
        }
    }
}
