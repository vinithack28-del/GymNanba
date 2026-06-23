<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lockers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('locker_number', 20);
            $table->string('location', 200)->nullable();
            $table->string('availability', 20)->default('available');
            $table->string('status', 20)->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id', 'branch_id', 'locker_number'], 'lockers_tenant_branch_number_unique');
            $table->index(['branch_id', 'availability']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('locker_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locker_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->date('from_date');
            $table->date('to_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('released_by')->nullable()->constrained('staff')->nullOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();

            $table->index(['locker_id', 'from_date']);
            $table->index(['member_id', 'from_date']);
            $table->index('released_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locker_assignments');
        Schema::dropIfExists('lockers');
    }
};
