<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50);
            $table->string('title', 150)->nullable();
            $table->string('status', 60)->nullable();
            $table->date('assessment_date')->nullable();
            $table->date('next_assessment_date')->nullable();
            $table->json('payload')->nullable();
            $table->text('ai_insight')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'member_id', 'type']);
            $table->index(['tenant_id', 'assessment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_assessments');
    }
};
