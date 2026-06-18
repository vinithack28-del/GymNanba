<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->index();
            $table->date('date');
            $table->string('category', 50);
            $table->string('sub_category', 50)->nullable();
            $table->string('description', 200);
            $table->integer('amount_paise');
            $table->integer('gst_paise')->default(0);
            $table->string('method', 20);
            $table->string('vendor', 100)->nullable();
            $table->string('reference', 100)->nullable();
            $table->text('receipt_url')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('approved'); // pending|approved|rejected
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_freq', 20)->nullable(); // daily|weekly|monthly|annual
            $table->date('recurrence_end')->nullable();
            $table->unsignedBigInteger('staff_id')->nullable()->index(); // salary expenses
            $table->string('salary_month', 7)->nullable(); // e.g. 2026-06
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->unsignedBigInteger('approved_by')->nullable()->index();
            $table->timestamps();

            $table->index(['tenant_id', 'date']);
            $table->index(['branch_id', 'date']);
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
