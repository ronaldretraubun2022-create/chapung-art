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
        Schema::table('artworks', function (Blueprint $table): void {
            $table->index(['is_featured', 'created_at'], 'artworks_featured_created_idx');
            $table->index(['category_id', 'created_at'], 'artworks_category_created_idx');
            $table->index(['artist_id', 'created_at'], 'artworks_artist_created_idx');
            $table->index(['collection_id', 'created_at'], 'artworks_collection_created_idx');
            $table->index(['status', 'created_at'], 'artworks_status_created_idx');
            $table->index('price', 'artworks_price_idx');
        });

        Schema::table('photographies', function (Blueprint $table): void {
            $table->index(['is_featured', 'created_at'], 'photos_featured_created_idx');
            $table->index(['category_id', 'created_at'], 'photos_category_created_idx');
            $table->index(['artist_id', 'created_at'], 'photos_artist_created_idx');
            $table->index(['collection_id', 'created_at'], 'photos_collection_created_idx');
            $table->index(['status', 'created_at'], 'photos_status_created_idx');
            $table->index('price', 'photos_price_idx');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->index(['status', 'published_at'], 'posts_status_published_idx');
            $table->index(['category_id', 'status', 'published_at'], 'posts_category_status_published_idx');
            $table->index(['is_featured', 'created_at'], 'posts_featured_created_idx');
        });

        Schema::table('page_views', function (Blueprint $table): void {
            $table->index(['viewable_type', 'viewed_at'], 'page_views_type_viewed_idx');
            $table->index(['device', 'viewed_at'], 'page_views_device_viewed_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_views', function (Blueprint $table): void {
            $table->dropIndex('page_views_type_viewed_idx');
            $table->dropIndex('page_views_device_viewed_idx');
        });

        Schema::table('posts', function (Blueprint $table): void {
            $table->dropIndex('posts_status_published_idx');
            $table->dropIndex('posts_category_status_published_idx');
            $table->dropIndex('posts_featured_created_idx');
        });

        Schema::table('photographies', function (Blueprint $table): void {
            $table->dropIndex('photos_featured_created_idx');
            $table->dropIndex('photos_category_created_idx');
            $table->dropIndex('photos_artist_created_idx');
            $table->dropIndex('photos_collection_created_idx');
            $table->dropIndex('photos_status_created_idx');
            $table->dropIndex('photos_price_idx');
        });

        Schema::table('artworks', function (Blueprint $table): void {
            $table->dropIndex('artworks_featured_created_idx');
            $table->dropIndex('artworks_category_created_idx');
            $table->dropIndex('artworks_artist_created_idx');
            $table->dropIndex('artworks_collection_created_idx');
            $table->dropIndex('artworks_status_created_idx');
            $table->dropIndex('artworks_price_idx');
        });
    }
};
