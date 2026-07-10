<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Seed default Chapung Art tags.
     */
    public function run(): void
    {
        $tags = [
            'Papua',
            'Asmat',
            'Merauke',
            'Sagu',
            'Noken',
            'Tifa',
            'Musamus',
            'Budaya',
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['slug' => Str::slug($tag)],
                [
                    'name' => $tag,
                    'type' => 'general',
                    'is_active' => true,
                ]
            );
        }
    }
}
