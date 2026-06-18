<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->unsignedBigInteger('payment_id')->nullable()->index(); // NULL for manual invoices
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('invoice_number', 30)->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->jsonb('line_items');  // [{description, qty, rate_paise, gst_rate, amount_paise}]
            $table->integer('subtotal_paise');
            $table->integer('gst_paise')->default(0);
            $table->integer('total_paise');
            $table->string('status', 20)->default('paid'); // paid|unpaid|partial|void
            $table->text('notes')->nullable();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();

            $table->index(['member_id', 'invoice_date']);
            $table->index(['tenant_id', 'invoice_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
