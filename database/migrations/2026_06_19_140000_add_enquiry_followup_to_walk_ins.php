<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('walk_ins', function (Blueprint $table): void {
            $table->string('enquiry_status', 20)->default('open')->after('notes');
            // open | followed_up | converted | closed — only relevant when purpose = inquiry
        });

        Schema::create('walk_in_followups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('walk_in_id')->constrained('walk_ins')->cascadeOnDelete();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('outcome', 30);
            // called | visited | messaged | no_answer | not_interested | converted
            $table->text('notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->unsignedBigInteger('logged_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walk_in_followups');
        Schema::table('walk_ins', function (Blueprint $table): void {
            $table->dropColumn('enquiry_status');
        });
    }
};
