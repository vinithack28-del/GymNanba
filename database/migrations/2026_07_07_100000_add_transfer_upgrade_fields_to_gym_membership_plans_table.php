<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table): void {
            // Transfer fields
            $table->boolean('is_transferable')->default(false)->after('max_freeze_days');
            $table->boolean('has_transfer_fee')->default(false)->after('is_transferable');
            $table->unsignedBigInteger('transfer_fee_amount')->nullable()->after('has_transfer_fee');
            $table->boolean('transfer_fee_gst_applicable')->default(false)->after('transfer_fee_amount');
            $table->text('transfer_notes')->nullable()->after('transfer_fee_gst_applicable');

            // Upgrade fields
            $table->boolean('is_upgradable')->default(false)->after('transfer_notes');
            $table->boolean('has_upgrade_charge')->default(false)->after('is_upgradable');
            $table->enum('upgrade_charge_type', ['full_new_plan', 'difference_amount', 'custom_amount'])->nullable()->after('has_upgrade_charge');
            $table->unsignedBigInteger('upgrade_custom_amount')->nullable()->after('upgrade_charge_type');
            $table->text('upgrade_notes')->nullable()->after('upgrade_custom_amount');
        });
    }

    public function down(): void
    {
        Schema::table('gym_membership_plans', function (Blueprint $table): void {
            $table->dropColumn([
                'is_transferable',
                'has_transfer_fee',
                'transfer_fee_amount',
                'transfer_fee_gst_applicable',
                'transfer_notes',
                'is_upgradable',
                'has_upgrade_charge',
                'upgrade_charge_type',
                'upgrade_custom_amount',
                'upgrade_notes',
            ]);
        });
    }
};
