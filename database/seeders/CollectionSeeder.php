<?php

namespace Database\Seeders;

use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CollectionSeeder extends Seeder
{
    /**
     * Seed Chapung Art default collections.
     */
    public function run(): void
    {
        $collections = [
            [
                'name' => 'Festival Asmat 2026',
                'description' => 'Kumpulan karya visual dan dokumentasi budaya dari Festival Asmat 2026.',
                'is_featured' => true,
            ],
            [
                'name' => 'Papua Selatan',
                'description' => 'Koleksi seni dan fotografi yang mengangkat identitas visual Papua Selatan.',
                'is_featured' => true,
            ],
            [
                'name' => 'Musamus Merauke',
                'description' => 'Karya yang terinspirasi dari lanskap, bentuk, dan simbol Musamus Merauke.',
                'is_featured' => false,
            ],
            [
                'name' => 'Noken Papua',
                'description' => 'Koleksi tentang noken sebagai warisan budaya, fungsi sosial, dan ekspresi visual Papua.',
                'is_featured' => true,
            ],
            [
                'name' => 'Landscape Papua',
                'description' => 'Lanskap Papua dalam karya seni, fotografi, dan dokumentasi visual.',
                'is_featured' => false,
            ],
        ];

        foreach ($collections as $collection) {
            Collection::updateOrCreate(
                ['slug' => Str::slug($collection['name'])],
                [
                    ...$collection,
                    'is_active' => true,
                ]
            );
        }
    }
}
