<?php

use App\Enums\SubscriptionStatus;
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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('omise_customer_id')->nullable();
            $table->string('omise_card_id')->nullable();
            $table->string('omise_schedule_id')->nullable();
            $table->string('omise_default_card_id')->nullable();
            $table->enum('status', array_map(fn ($case) => $case->value, SubscriptionStatus::cases()))->default(SubscriptionStatus::Active->value);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('THB');
            $table->integer('billing_day')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['user_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
