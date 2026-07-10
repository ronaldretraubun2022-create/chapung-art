<?php

namespace Database\Seeders;

use App\Models\Artist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArtistSeeder extends Seeder
{
    /**
     * Seed sample Chapung Art artists.
     */
    public function run(): void
    {
        $artists = [
            [
                'name' => 'Chapung Art Studio',
                'bio' => 'Studio kreatif yang mendokumentasikan dan mengembangkan karya seni visual Papua Selatan.',
                'origin_area' => 'Papua Selatan',
                'city' => 'Merauke',
                'province' => 'Papua Selatan',
                'specialization' => 'Kurasi seni, lukisan, dan dokumentasi budaya',
                'education' => 'Komunitas kreatif dan pelatihan seni visual.',
                'achievements' => 'Mengarsipkan karya kreator lokal dan memperluas akses pasar seni Papua.',
                'exhibitions' => 'Program showcase Chapung Art.',
                'is_featured' => true,
            ],
            [
                'name' => 'Seniman Papua Selatan',
                'bio' => 'Profil seniman lokal yang mengangkat narasi tanah, manusia, dan identitas budaya Papua Selatan.',
                'origin_area' => 'Papua Selatan',
                'city' => 'Merauke',
                'province' => 'Papua Selatan',
                'specialization' => 'Lukisan kontemporer dan motif budaya Papua',
                'education' => 'Pembelajaran seni berbasis komunitas.',
                'achievements' => 'Aktif dalam pengembangan karya visual berbasis budaya lokal.',
                'exhibitions' => 'Pameran komunitas seni Papua Selatan.',
                'is_featured' => true,
            ],
            [
                'name' => 'Fotografer Budaya Papua',
                'bio' => 'Fotografer dokumenter yang berfokus pada ritual, lanskap, dan kehidupan budaya Papua.',
                'origin_area' => 'Papua',
                'city' => 'Jayapura',
                'province' => 'Papua',
                'specialization' => 'Fotografi budaya dan dokumenter',
                'education' => 'Pelatihan fotografi dokumenter dan visual storytelling.',
                'achievements' => 'Mendokumentasikan kegiatan budaya dan kehidupan masyarakat Papua.',
                'exhibitions' => 'Pameran fotografi budaya Papua.',
                'is_featured' => false,
            ],
        ];

        foreach ($artists as $artist) {
            Artist::updateOrCreate(
                ['slug' => Str::slug($artist['name'])],
                [
                    ...$artist,
                    'country' => 'Indonesia',
                    'is_active' => true,
                ]
            );
        }
    }
}
