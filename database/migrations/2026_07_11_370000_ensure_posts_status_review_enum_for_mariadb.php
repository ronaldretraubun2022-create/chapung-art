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
        if (! $this->usesMariaDbCompatibleDriver()) {
            return;
        }

        DB::statement("ALTER TABLE posts MODIFY status ENUM('draft', 'review', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->usesMariaDbCompatibleDriver()) {
            return;
        }

        DB::statement("ALTER TABLE posts MODIFY status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft'");
    }

    private function usesMariaDbCompatibleDriver(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
