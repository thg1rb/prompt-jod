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
        Schema::create('slips', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('ocr_status'); // pending | processing | done | failed
            $table->text('ocr_raw_text')->nullable();
            $table->string('transaction_ref')->nullable(); // เลขอ้างอิงจากสลิป ใช้ตรวจ duplicate
            $table->string('sender')->nullable();
            $table->string('recipient')->nullable();
            $table->string('bank')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamp('transferred_at')->nullable(); // วันเวลาบนสลิป
            $table->string('verification_status')->default('unverified'); // unverified | verified | mismatch | unavailable
            $table->boolean('is_duplicate')->default(false);
            $table->uuid('duplicate_of')->nullable(); // Self-reference for duplicate detection
            $table->timestamps();

            $table->index('transaction_ref', 'idx_slips_ref');
            $table->index(['user_id', 'transaction_ref'], 'idx_slips_user_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slips');
    }
};
