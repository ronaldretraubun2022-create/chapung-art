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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('banner_image')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'is_featured']);
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->foreignId('collection_id')->nullable()->after('artist_id')->constrained()->nullOnDelete();
        });

        Schema::table('photographies', function (Blueprint $table) {
            $table->foreignId('collection_id')->nullable()->after('artist_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_id');
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_id');
        });

        Schema::dropIfExists('collections');
    }
};
