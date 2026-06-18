<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->index();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->string('receipt_number', 20)->unique();
            $table->integer('amount_paise');          // base amount (excl. GST)
            $table->integer('gst_paise')->default(0); // GST amount
            $table->integer('total_paise');           // amount + gst
            $table->string('method', 20);             // cash|upi|card|bank|cheque
            $table->string('reference', 100)->nullable();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('active'); // active|voided
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('voided_by')->nullable()->index();
            $table->unsignedBigInteger('collected_by')->nullable()->index();
            $table->timestamps();

            $table->index(['member_id', 'payment_date']);
            $table->index(['branch_id', 'payment_date']);
            $table->index(['tenant_id', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
