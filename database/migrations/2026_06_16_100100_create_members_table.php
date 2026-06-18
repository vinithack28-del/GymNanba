<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('member_code', 20);
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('dob')->nullable();
            $table->text('address')->nullable();
            $table->string('id_proof_type', 20)->nullable();
            $table->string('id_proof_number', 50)->nullable();
            $table->string('id_proof_url')->nullable();
            $table->string('photo_url')->nullable();
            $table->unsignedBigInteger('plan_id')->nullable()->index();
            $table->string('plan_name', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status', 20)->default('active');
            $table->bigInteger('balance_paise')->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'member_code']);
            $table->unique(['tenant_id', 'phone']);
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
