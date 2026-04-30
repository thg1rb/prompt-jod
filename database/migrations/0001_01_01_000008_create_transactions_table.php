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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('slip_id')->nullable(); // null = manual entry
            $table->string('type'); // expense | income | adjustment
            $table->decimal('amount', 15, 2);
            $table->string('sender')->nullable(); // pre-filled จาก slip หรือกรอกเอง
            $table->string('recipient')->nullable(); // pre-filled จาก slip หรือกรอกเอง
            $table->string('note')->nullable();
            $table->timestamp('transacted_at');
            $table->timestamps();

            $table->index(['user_id', 'transacted_at'], 'idx_txn_user_date');
            $table->index(['wallet_id', 'transacted_at']);
            $table->index('category_id');
            $table->index('slip_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
