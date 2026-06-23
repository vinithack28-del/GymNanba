<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            $table->integer('paid_paise')->default(0)->after('total_paise');
            // amount actually collected now (equals total_paise for full payments)
            $table->boolean('is_partial')->default(false)->after('paid_paise');
            $table->integer('due_paise')->default(0)->after('is_partial');
            $table->date('due_date')->nullable()->after('due_paise');
            $table->boolean('reminder_sent')->default(false)->after('due_date');
        });

        Schema::create('payment_splits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->string('method', 20);
            $table->integer('amount_paise');
            $table->string('reference', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_splits');
        Schema::table('payments', function (Blueprint $table): void {
            $table->dropColumn(['paid_paise', 'is_partial', 'due_paise', 'due_date', 'reminder_sent']);
        });
    }
};
