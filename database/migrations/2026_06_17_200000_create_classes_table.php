<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('type', 30); // yoga|hiit|zumba|strength|pilates|crossfit|aerobics|custom
            $table->string('room', 80)->nullable();
            $table->foreignId('trainer_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->date('class_date');
            $table->integer('max_capacity');
            $table->boolean('allow_waitlist')->default(true);
            $table->boolean('visible')->default(true);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index(); // recurring series — points to first occurrence id
            $table->string('status', 20)->default('scheduled'); // scheduled|cancelled|completed
            $table->text('cancel_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'class_date']);
            $table->index(['branch_id', 'class_date']);
            $table->index(['trainer_id', 'class_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
