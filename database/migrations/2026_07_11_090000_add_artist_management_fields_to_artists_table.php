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
        Schema::table('artists', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->string('name')->after('user_id');
            $table->string('slug')->unique()->after('name');
            $table->string('photo')->nullable()->after('slug');
            $table->longText('bio')->nullable()->after('photo');
            $table->string('origin_area')->nullable()->after('bio');
            $table->string('city')->nullable()->after('origin_area');
            $table->string('province')->nullable()->after('city');
            $table->string('country')->default('Indonesia')->after('province');
            $table->date('birth_date')->nullable()->after('country');
            $table->string('email')->nullable()->after('birth_date');
            $table->string('phone')->nullable()->after('email');
            $table->string('whatsapp')->nullable()->after('phone');
            $table->string('instagram')->nullable()->after('whatsapp');
            $table->string('facebook')->nullable()->after('instagram');
            $table->string('website')->nullable()->after('facebook');
            $table->string('specialization')->nullable()->after('website');
            $table->text('education')->nullable()->after('specialization');
            $table->text('achievements')->nullable()->after('education');
            $table->text('exhibitions')->nullable()->after('achievements');
            $table->boolean('is_featured')->default(false)->after('exhibitions');
            $table->boolean('is_active')->default(true)->after('is_featured');
            $table->softDeletes()->after('updated_at');

            $table->index(['is_active', 'is_featured']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('artists', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'is_featured']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'name',
                'slug',
                'photo',
                'bio',
                'origin_area',
                'city',
                'province',
                'country',
                'birth_date',
                'email',
                'phone',
                'whatsapp',
                'instagram',
                'facebook',
                'website',
                'specialization',
                'education',
                'achievements',
                'exhibitions',
                'is_featured',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};
