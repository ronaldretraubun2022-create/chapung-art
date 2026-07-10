<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $heroCopy = [
            'title' => 'Chapung Art Merauke',
            'subtitle' => 'Galeri Seni Rupa, fotografi budaya, dan media kreatif Papua Selatan.',
            'content' => 'Ruang digital untuk karya seni rupa, dokumentasi budaya, dan cerita visual dari Papua Selatan.',
            'button_text' => 'Lihat Galeri',
            'button_url' => '/gallery',
            'sort_order' => 10,
            'is_active' => true,
            'updated_at' => now(),
        ];

        if (DB::table('homepage_sections')->where('section_key', 'hero')->exists()) {
            DB::table('homepage_sections')->where('section_key', 'hero')->update($heroCopy);

            return;
        }

        DB::table('homepage_sections')->insert([
            ...$heroCopy,
            'section_key' => 'hero',
            'created_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('homepage_sections')
            ->where('section_key', 'hero')
            ->update([
                'title' => 'Chapung Art Merauke',
                'subtitle' => 'Galeri seni, fotografi budaya, dan media kreatif Papua Selatan.',
                'content' => 'Ruang digital untuk karya seni rupa, dokumentasi budaya, dan cerita visual dari Papua Selatan.',
                'updated_at' => now(),
            ]);
    }
};
