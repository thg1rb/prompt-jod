<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('fixed_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->foreignUuid('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignUuid('wallet_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('icon');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'name']);
            $table->unique(['wallet_id', 'name']);
            $table->index('fixed_category_id');
            $table->index(['user_id', 'deleted_at']);
            $table->index(['wallet_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_categories');
    }
};
