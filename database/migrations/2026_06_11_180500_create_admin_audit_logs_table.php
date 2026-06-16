<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name', 100);
            $table->string('actor_ip', 45)->nullable();
            $table->string('action_type', 40);
            $table->string('target_type', 40);
            $table->string('target_id', 40)->nullable();
            $table->string('target_name', 255)->nullable();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->json('difference')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
