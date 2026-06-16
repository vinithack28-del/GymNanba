<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->string('billing_cycle', 20);
            $table->unsignedBigInteger('price_paise');
            $table->unsignedInteger('max_members')->default(0);
            $table->unsignedInteger('max_branches')->default(0);
            $table->unsignedInteger('max_staff_accounts')->default(0);
            $table->json('feature_flags')->nullable();
            $table->boolean('trial_eligible')->default(false);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
