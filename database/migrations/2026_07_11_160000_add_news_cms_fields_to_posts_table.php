<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('author_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('featured_image')->nullable()->after('excerpt');
            $table->timestamp('scheduled_at')->nullable()->after('published_at');
            $table->unsignedInteger('reading_time')->nullable()->after('scheduled_at');
            $table->unsignedBigInteger('views')->default(0)->after('reading_time');
            $table->string('seo_title')->nullable()->after('views');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('og_image')->nullable()->after('seo_description');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY status ENUM('draft', 'review', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE posts MODIFY status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
        }

        Schema::table('posts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('author_id');
            $table->dropColumn([
                'featured_image',
                'scheduled_at',
                'reading_time',
                'views',
                'seo_title',
                'seo_description',
                'og_image',
            ]);
        });
    }
};
