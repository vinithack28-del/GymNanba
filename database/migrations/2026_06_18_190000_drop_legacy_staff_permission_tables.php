<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('staff_permission_actions');
        Schema::dropIfExists('staff_permission_modules');
        Schema::dropIfExists('staff_role_templates');
    }

    public function down(): void
    {
        // Legacy tables intentionally not restored.
    }
};
