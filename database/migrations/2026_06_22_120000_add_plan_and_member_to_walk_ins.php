<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('walk_ins', function (Blueprint $table): void {
            $table->unsignedBigInteger('plan_id')->nullable()->after('fee_paise')->index();
            $table->unsignedBigInteger('member_id')->nullable()->after('plan_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('walk_ins', function (Blueprint $table): void {
            $table->dropColumn(['plan_id', 'member_id']);
        });
    }
};
