<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('type', 30);           // cardio|strength|free_weights|functional|other
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->unsignedInteger('purchase_price_paise')->nullable();
            $table->string('status', 20)->default('operational');  // operational|maintenance|broken
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('equipment_service_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('equipment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('service_date');
            $table->string('service_type', 30);   // maintenance|repair|inspection|calibration|cleaning|replacement
            $table->unsignedInteger('cost_paise')->default(0);
            $table->string('service_provider', 200)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_service_records');
        Schema::dropIfExists('equipment');
    }
};
