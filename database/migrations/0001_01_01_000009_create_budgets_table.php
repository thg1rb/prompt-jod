<?php

use App\Enums\BudgetPeriod;
use App\Enums\BudgetStatus;
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
        Schema::create('budgets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained()->cascadeOnDelete();
            $table->enum('period', array_map(fn($case) => $case->value, BudgetPeriod::cases()));
            $table->enum('status', array_map(fn($case) => $case->value, BudgetStatus::cases()))->default(BudgetStatus::Active->value);
            $table->decimal('amount', 15, 2);
            $table->integer('year');
            $table->integer('month')->nullable(); // Null for yearly budgets
            $table->boolean('is_active')->default(true);
            $table->boolean('alert_enabled')->default(true);
            $table->decimal('alert_threshold', 5, 2)->default(80.00); // Alert at 80%
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'category_id', 'year', 'month']);
            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'year', 'month']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
