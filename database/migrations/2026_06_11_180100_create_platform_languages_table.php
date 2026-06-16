<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_languages', function (Blueprint $table) {
            $table->string('locale_code', 10)->primary();
            $table->string('display_name', 50);
            $table->boolean('is_active')->default(false);
            $table->unsignedSmallInteger('completeness_pct')->default(0);
            $table->boolean('is_rtl')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_languages');
    }
};
