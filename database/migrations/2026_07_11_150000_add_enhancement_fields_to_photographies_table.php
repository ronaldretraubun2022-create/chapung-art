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
        Schema::table('photographies', function (Blueprint $table) {
            $table->string('lens')->nullable()->after('camera');
            $table->unsignedInteger('iso')->nullable()->after('lens');
            $table->string('aperture')->nullable()->after('iso');
            $table->string('shutter_speed')->nullable()->after('aperture');
            $table->string('focal_length')->nullable()->after('shutter_speed');
            $table->decimal('gps_lat', 10, 7)->nullable()->after('focal_length');
            $table->decimal('gps_lng', 10, 7)->nullable()->after('gps_lat');
            $table->timestamp('taken_at')->nullable()->after('gps_lng');
            $table->string('province')->nullable()->after('location');
            $table->string('country')->default('Indonesia')->after('province');
            $table->string('license')->nullable()->after('country');
            $table->unsignedInteger('stock')->default(1)->after('price');
            $table->unsignedBigInteger('views')->default(0)->after('stock');
            $table->string('seo_title')->nullable()->after('views');
            $table->text('seo_description')->nullable()->after('seo_title');
            $table->string('og_image')->nullable()->after('seo_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photographies', function (Blueprint $table) {
            $table->dropColumn([
                'lens',
                'iso',
                'aperture',
                'shutter_speed',
                'focal_length',
                'gps_lat',
                'gps_lng',
                'taken_at',
                'province',
                'country',
                'license',
                'stock',
                'views',
                'seo_title',
                'seo_description',
                'og_image',
            ]);
        });
    }
};
