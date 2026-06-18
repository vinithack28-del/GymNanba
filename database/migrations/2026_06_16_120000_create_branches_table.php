<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name', 80);
            $table->string('address1', 100);
            $table->string('address2', 100)->nullable();
            $table->string('city', 50);
            $table->string('state', 50);
            $table->string('pin', 6);
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->string('manager_name', 100)->nullable();
            $table->json('operating_hours')->nullable();
            $table->json('amenities')->nullable();
            $table->string('gst_number', 15)->nullable();
            $table->string('status', 20)->default('active');
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['tenant_id', 'name']);
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
