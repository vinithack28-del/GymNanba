<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walk_ins', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('purpose', 50); // day_pass | free_trial | inquiry | guest
            $table->integer('fee_paise')->default(0);
            $table->string('payment_method', 20)->nullable(); // cash | upi | card
            $table->string('reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('guest_of_id')->nullable()->index(); // member_id
            $table->unsignedBigInteger('logged_by')->nullable()->index();   // staff_id
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_ins');
    }
};
