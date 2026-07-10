<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    /**
     * Seed default homepage CMS sections without overwriting edited content.
     */
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'hero',
                'title' => 'Chapung Art Merauke',
                'subtitle' => 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.',
                'content' => 'Ruang digital untuk karya seni rupa, dokumentasi budaya, dan cerita visual dari Papua Selatan.',
                'button_text' => 'Lihat Galeri',
                'button_url' => '/gallery',
                'sort_order' => 10,
            ],
            [
                'section_key' => 'featured_artworks',
                'title' => 'Artwork Unggulan',
                'subtitle' => 'Pilihan karya seni Chapung Art.',
                'content' => 'Karya terpilih dari seniman dan ekosistem kreatif Papua.',
                'button_text' => 'Lihat Artwork',
                'button_url' => '/gallery',
                'sort_order' => 20,
            ],
            [
                'section_key' => 'featured_photographies',
                'title' => 'Fotografi Budaya',
                'subtitle' => 'Dokumentasi visual masyarakat dan alam Papua.',
                'content' => 'Kumpulan fotografi pilihan tentang identitas, ruang hidup, dan tradisi Papua Selatan.',
                'button_text' => 'Lihat Fotografi',
                'button_url' => '/photography',
                'sort_order' => 30,
            ],
            [
                'section_key' => 'featured_artists',
                'title' => 'Seniman Pilihan',
                'subtitle' => 'Profil kreator di balik karya.',
                'content' => 'Ruang apresiasi untuk seniman, fotografer, dan pelaku budaya Chapung Art.',
                'sort_order' => 40,
            ],
            [
                'section_key' => 'collections',
                'title' => 'Collections',
                'subtitle' => 'Kurasi tema seni dan budaya.',
                'content' => 'Koleksi tematik yang menghubungkan artwork, fotografi, dan narasi visual Papua.',
                'sort_order' => 50,
            ],
            [
                'section_key' => 'latest_posts',
                'title' => 'Berita Seni Budaya',
                'subtitle' => 'Artikel, kabar, dan cerita terbaru.',
                'content' => 'Update media Chapung Art tentang seni, budaya, dan kegiatan kreatif Papua Selatan.',
                'button_text' => 'Baca Media',
                'button_url' => '/media',
                'sort_order' => 60,
            ],
            [
                'section_key' => 'testimonials',
                'title' => 'Testimonials',
                'subtitle' => 'Dukungan untuk ekosistem kreatif Papua.',
                'content' => 'Ruang testimoni untuk kolaborator, kolektor, dan komunitas.',
                'sort_order' => 70,
            ],
            [
                'section_key' => 'partners',
                'title' => 'Partners',
                'subtitle' => 'Kolaborasi budaya dan kreatif.',
                'content' => 'Chapung Art terbuka untuk kemitraan seni, budaya, pendidikan, dan media.',
                'sort_order' => 80,
            ],
            [
                'section_key' => 'footer',
                'title' => 'CHAPUNG ART',
                'subtitle' => 'Merauke, Papua Selatan',
                'content' => 'Seni, budaya, dan cerita visual Papua Selatan.',
                'sort_order' => 90,
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::firstOrCreate(
                ['section_key' => $section['section_key']],
                [
                    ...$section,
                    'is_active' => true,
                ]
            );
        }
    }
}
