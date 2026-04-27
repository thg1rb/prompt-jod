<?php

use App\Enums\AlertType;
use App\Enums\AlertStatus;
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
        Schema::create('budget_alerts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete(); // Denormalized for easier queries
            $table->enum('alert_type', array_map(fn($case) => $case->value, AlertType::cases()));
            $table->enum('status', array_map(fn($case) => $case->value, AlertStatus::cases()))->default(AlertStatus::Sent->value);
            $table->decimal('threshold_percent', 5, 2); // e.g., 80.00 for 80%
            $table->decimal('amount_spent', 15, 2);
            $table->decimal('amount_remaining', 15, 2);
            $table->text('message')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamps();

            $table->unique(['budget_id', 'threshold_percent']);
            $table->index(['user_id', 'status']);
            $table->index(['budget_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
    }
};
