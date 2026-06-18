<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_login_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 255)->nullable();
            $table->string('location', 255)->nullable();
            $table->timestamp('logged_in_at');
            $table->timestamps();

            $table->index(['staff_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_login_activities');
    }
};
