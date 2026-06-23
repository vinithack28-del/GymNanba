<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_modules', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->string('icon', 50)->nullable();
            $table->unsignedInteger('sort_order')->default(9999);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_modules');
    }
};
