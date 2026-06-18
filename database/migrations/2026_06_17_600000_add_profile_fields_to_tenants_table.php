<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('logo_url')->nullable()->after('business_type');
            $table->string('cover_photo_url')->nullable()->after('logo_url');
            $table->string('address2', 100)->nullable()->after('address');
            $table->char('pin', 6)->nullable()->after('state');
            $table->string('email', 255)->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('gstin', 15)->nullable()->after('gst_number');
            $table->string('pan', 10)->nullable()->after('gstin');
            $table->string('reg_number', 30)->nullable()->after('pan');
            $table->jsonb('social_links')->nullable()->after('reg_number');
            $table->text('about')->nullable()->after('social_links');
            $table->jsonb('operating_hours')->nullable()->after('about');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo_url', 'cover_photo_url', 'address2', 'pin', 'email',
                'website', 'gstin', 'pan', 'reg_number', 'social_links',
                'about', 'operating_hours',
            ]);
        });
    }
};
