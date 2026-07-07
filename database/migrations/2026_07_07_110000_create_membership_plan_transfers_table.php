<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_plan_transfers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('source_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('target_member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained('gym_membership_plans')->cascadeOnDelete();
            $table->date('transfer_date');
            $table->date('old_start_date');
            $table->date('old_expiry_date');
            $table->date('new_start_date');
            $table->date('new_expiry_date');
            $table->unsignedInteger('remaining_days')->default(0);
            $table->unsignedBigInteger('transfer_fee_amount')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable()->index();
            $table->unsignedBigInteger('payment_id')->nullable()->index();
            $table->enum('status', ['pending_payment', 'completed', 'cancelled'])->default('pending_payment');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'transfer_date']);
            $table->index(['source_member_id', 'status']);
            $table->index(['target_member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_plan_transfers');
    }
};
