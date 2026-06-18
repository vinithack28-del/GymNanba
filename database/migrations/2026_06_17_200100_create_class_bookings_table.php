<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('member_id')->constrained('members')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('status', 20)->default('booked'); // booked|waitlisted|cancelled|attended|absent|late_cancel
            $table->unsignedSmallInteger('waitlist_pos')->nullable();
            $table->unsignedBigInteger('booked_by')->nullable()->index();
            $table->timestamps();

            $table->unique(['class_id', 'member_id']);
            $table->index(['tenant_id', 'class_id']);
            $table->index(['member_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_bookings');
    }
};
