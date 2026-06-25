<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table): void {
            $table->foreignId('subscription_id')->nullable()->after('admin_id')
                  ->constrained('subscriptions')->nullOnDelete();
            $table->string('payment_type', 20)->default('manual')->after('paid_at'); // manual|renewal|part_payment
            $table->string('notes', 255)->nullable()->after('payment_type');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payments', function (Blueprint $table): void {
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['subscription_id', 'payment_type', 'notes']);
        });
    }
};
