<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->foreignId('artist_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });

        Schema::table('photographies', function (Blueprint $table) {
            $table->foreignId('artist_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artist_id');
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artist_id');
        });
    }
};
