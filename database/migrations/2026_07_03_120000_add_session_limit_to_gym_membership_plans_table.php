<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table): void {
            $table->unsignedInteger('session_limit')->nullable()->after('duration_value');
        });
    }

    public function down(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table): void {
            $table->dropColumn('session_limit');
        });
    }
};
