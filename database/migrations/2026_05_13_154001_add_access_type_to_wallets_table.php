<?php

use App\Enums\WalletAccess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->enum('access_type', array_map(fn ($case) => $case->value, WalletAccess::cases()))
                ->default(WalletAccess::Personal->value)
                ->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropColumn('access_type');
        });
    }
};
