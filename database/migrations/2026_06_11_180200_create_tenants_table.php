<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('gym_name', 80);
            $table->string('business_type', 20);
            $table->string('owner_name', 100);
            $table->string('owner_email')->unique();
            $table->string('phone', 20);
            $table->string('city', 100);
            $table->string('state', 100);
            $table->text('address');
            $table->string('gst_number', 30)->nullable();
            $table->string('subdomain', 30)->unique();
            $table->string('status', 20)->default('trial');
            $table->string('default_language', 10)->default('en-IN');
            $table->unsignedInteger('members_count')->default(0);
            $table->timestamp('last_owner_login_at')->nullable();
            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
