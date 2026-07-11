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
        Schema::create('order_status_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->string('payment_status_from')->nullable();
            $table->string('payment_status_to')->nullable();
            $table->string('source')->default('system');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'created_at'], 'order_history_order_created_idx');
            $table->index(['status_to', 'payment_status_to'], 'order_history_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_histories');
    }
};
