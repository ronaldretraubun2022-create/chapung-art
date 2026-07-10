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
            $table->string('sku')->nullable()->unique()->after('id');
            $table->string('material')->nullable()->after('medium');
            $table->string('technique')->nullable()->after('material');
            $table->string('orientation')->nullable()->after('technique');
            $table->string('frame')->nullable()->after('orientation');
            $table->decimal('width', 10, 2)->nullable()->after('frame');
            $table->decimal('height', 10, 2)->nullable()->after('width');
            $table->decimal('depth', 10, 2)->nullable()->after('height');
            $table->decimal('weight', 10, 2)->nullable()->after('depth');
            $table->string('condition')->nullable()->after('weight');
            $table->string('location')->nullable()->after('condition');
            $table->string('certificate_number')->nullable()->after('location');
            $table->string('license')->nullable()->after('certificate_number');
            $table->unsignedInteger('stock')->default(1)->after('license');
            $table->unsignedBigInteger('views')->default(0)->after('stock');
            $table->unsignedBigInteger('likes')->default(0)->after('views');
            $table->string('seo_title')->nullable()->after('likes');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('og_image')->nullable()->after('seo_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table) {
            $table->dropUnique(['sku']);
            $table->dropColumn([
                'sku',
                'material',
                'technique',
                'orientation',
                'frame',
                'width',
                'height',
                'depth',
                'weight',
                'condition',
                'location',
                'certificate_number',
                'license',
                'stock',
                'views',
                'likes',
                'seo_title',
                'seo_description',
                'og_image',
            ]);
        });
    }
};
