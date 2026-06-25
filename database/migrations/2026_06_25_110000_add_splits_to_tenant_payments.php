<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table): void {
            $table->json('splits')->nullable()->after('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table): void {
            $table->dropColumn('splits');
        });
    }
};
