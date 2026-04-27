<?php

use App\Enums\TransactionType;
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
            $table->uuid('slip_id')->nullable(); // Foreign key added in separate migration
            $table->enum('type', array_map(fn($case) => $case->value, TransactionType::cases()));
            $table->decimal('amount', 15, 2);
            $table->string('description')->nullable();
            $table->timestamp('transacted_at')->useCurrent();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Store additional OCR/extraction data
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'transacted_at']);
            $table->index(['wallet_id', 'transacted_at']);
            $table->index('category_id');
            $table->index(['user_id', 'transacted_at', 'wallet_id']);
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
