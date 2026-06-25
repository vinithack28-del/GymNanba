<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->boolean('is_trial')->default(false)->after('trial_eligible');
            $table->unsignedTinyInteger('trial_days')->nullable()->after('is_trial');
            $table->string('billing_cycle', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn(['is_trial', 'trial_days']);
            $table->string('billing_cycle', 20)->nullable(false)->change();
        });
    }
};
