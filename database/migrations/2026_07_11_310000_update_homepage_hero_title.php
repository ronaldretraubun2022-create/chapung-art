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
        DB::table('homepage_sections')
            ->where('section_key', 'hero')
            ->update([
                'title' => 'CHAPUNG ART',
                'updated_at' => now(),
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
                'updated_at' => now(),
            ]);
    }
};
