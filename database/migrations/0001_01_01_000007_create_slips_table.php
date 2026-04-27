<?php

use App\Enums\SlipStatus;
use App\Enums\VerificationStatus;
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
            $table->foreignUuid('wallet_id')->constrained()->restrictOnDelete();
            $table->foreignUuid('category_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('transaction_id')->nullable(); // Foreign key added in separate migration
            $table->enum('status', array_map(fn($case) => $case->value, SlipStatus::cases()))->default(SlipStatus::Pending->value);
            $table->enum('verification_status', array_map(fn($case) => $case->value, VerificationStatus::cases()))->default(VerificationStatus::Unverified->value);
            $table->uuid('duplicate_of')->nullable(); // Self-reference for duplicate detection
            $table->string('transaction_id_field')->nullable(); // From OCR
            $table->decimal('amount', 15, 2)->nullable();
            $table->timestamp('transacted_at')->nullable();
            $table->string('sender')->nullable();
            $table->string('sender_bank')->nullable();
            $table->string('recipient')->nullable();
            $table->string('recipient_bank')->nullable();
            $table->text('raw_ocr_data')->nullable(); // Full OCR response
            $table->string('image_path');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('transaction_id_field');
            $table->index(['user_id', 'transaction_id_field']);
            $table->index(['wallet_id', 'transacted_at']);
            $table->index(['status', 'verification_status']);
            $table->index('duplicate_of');
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
