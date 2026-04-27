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
        Schema::table('slips', function (Blueprint $table) {
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('slip_id')->references('id')->on('slips')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slips', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['slip_id']);
        });
    }
};
