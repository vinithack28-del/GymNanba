<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->string('domain_mode', 20)->default('shared')->after('subdomain');
            $table->string('custom_domain')->nullable()->unique()->after('domain_mode');
            $table->string('database_mode', 20)->default('shared')->after('custom_domain');
            $table->string('database_name')->nullable()->unique()->after('database_mode');
            $table->foreignId('owner_user_id')->nullable()->after('database_name')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('owner_user_id');
            $table->dropUnique(['custom_domain']);
            $table->dropUnique(['database_name']);
            $table->dropColumn(['domain_mode', 'custom_domain', 'database_mode', 'database_name']);
        });
    }
};
