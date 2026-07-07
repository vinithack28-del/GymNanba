<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plan_upgrades', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('old_member_plan_id')->nullable()->constrained('gym_membership_plans')->nullOnDelete();
            $table->foreignId('new_member_plan_id')->constrained('gym_membership_plans')->cascadeOnDelete();
            $table->date('upgrade_date');
            $table->unsignedBigInteger('old_plan_price_paise')->default(0);
            $table->unsignedBigInteger('new_plan_price_paise')->default(0);
            $table->unsignedBigInteger('upgrade_amount_paise')->default(0);
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->enum('status', ['pending_payment', 'completed', 'cancelled'])->default('pending_payment');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'upgrade_date']);
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_upgrades');
    }
};
