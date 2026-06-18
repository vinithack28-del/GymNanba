<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table) {
            $table->string('duration_type', 10)->default('days')->after('duration_days');
            $table->unsignedInteger('duration_value')->default(30)->after('duration_type');
            $table->boolean('gst_applicable')->default(false)->after('price_paise');
            $table->decimal('gst_rate', 5, 2)->nullable()->after('gst_applicable');
            $table->unsignedInteger('max_members')->default(0)->after('gst_rate');
            $table->unsignedInteger('grace_days')->default(0)->after('max_members');
            $table->json('inclusions')->nullable()->after('grace_days');
            $table->boolean('allow_freeze')->default(true)->after('inclusions');
            $table->unsignedInteger('max_freeze_days')->default(30)->after('allow_freeze');
        });

        // Backfill duration_value from existing duration_days
        DB::statement('UPDATE gym_membership_plans SET duration_type = \'days\', duration_value = duration_days');
    }

    public function down(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table) {
            $table->dropColumn([
                'duration_type','duration_value','gst_applicable','gst_rate',
                'max_members','grace_days','inclusions','allow_freeze','max_freeze_days',
            ]);
        });
    }
};
