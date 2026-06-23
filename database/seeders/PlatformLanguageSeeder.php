<?php

namespace Database\Seeders;

use App\Models\PlatformLanguage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformLanguageSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $languages = [
            ['locale_code' => 'en-IN', 'display_name' => 'English (India)', 'is_active' => true, 'completeness_pct' => 100, 'is_rtl' => false],
            ['locale_code' => 'hi-IN', 'display_name' => 'Hindi', 'is_active' => true, 'completeness_pct' => 94, 'is_rtl' => false],
            ['locale_code' => 'ta-IN', 'display_name' => 'Tamil', 'is_active' => true, 'completeness_pct' => 92, 'is_rtl' => false],
            ['locale_code' => 'te-IN', 'display_name' => 'Telugu', 'is_active' => true, 'completeness_pct' => 91, 'is_rtl' => false],
            ['locale_code' => 'kn-IN', 'display_name' => 'Kannada', 'is_active' => false, 'completeness_pct' => 76, 'is_rtl' => false],
            ['locale_code' => 'mr-IN', 'display_name' => 'Marathi', 'is_active' => false, 'completeness_pct' => 81, 'is_rtl' => false],
        ];

        foreach ($languages as $language) {
            PlatformLanguage::updateOrCreate(
                ['locale_code' => $language['locale_code']],
                $language,
            );
        }
    }
}
