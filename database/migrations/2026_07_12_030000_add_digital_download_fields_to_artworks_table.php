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
            $table->boolean('digital_download_enabled')->default(false)->after('license')->index();
            $table->string('digital_file_path')->nullable()->after('digital_download_enabled');
            $table->string('digital_file_name')->nullable()->after('digital_file_path');
            $table->unsignedBigInteger('digital_file_size')->nullable()->after('digital_file_name');
            $table->string('digital_file_mime', 120)->nullable()->after('digital_file_size');
            $table->timestamp('digital_file_uploaded_at')->nullable()->after('digital_file_mime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artworks', function (Blueprint $table): void {
            $table->dropIndex(['digital_download_enabled']);
            $table->dropColumn([
                'digital_download_enabled',
                'digital_file_path',
                'digital_file_name',
                'digital_file_size',
                'digital_file_mime',
                'digital_file_uploaded_at',
            ]);
        });
    }
};
