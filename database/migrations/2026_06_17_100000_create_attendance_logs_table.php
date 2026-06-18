<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->index();
            $table->string('method', 20)->default('manual'); // qr | biometric | manual
            $table->timestamp('checked_in_at')->useCurrent();
            $table->timestamp('checked_out_at')->nullable();
            $table->boolean('is_auto_checkout')->default(false);
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('checked_in_by')->nullable()->index(); // staff_id
            $table->timestamp('created_at')->useCurrent();

            $table->index(['member_id', 'checked_in_at']);
            $table->index(['branch_id', 'checked_in_at']);
            $table->index(['tenant_id', 'checked_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
