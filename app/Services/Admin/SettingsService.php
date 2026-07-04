<?php

namespace App\Services\Admin;

use App\Models\PlatformLanguage;
use Illuminate\Database\Eloquent\Collection;

class SettingsService
{
    public function __construct(private readonly AuditLogService $auditLogService)
    {
    }

    public function getIndexData(): array
    {
        return [
            'languages' => PlatformLanguage::query()->orderByDesc('is_active')->orderBy('display_name')->get(),
            'activeSessions' => 1,
        ];
    }

    public function updateLanguage(PlatformLanguage $language, bool $isActive): bool
    {
        if ($isActive && $language->completeness_pct < 90) {
            return false;
        }

        $old = $language->is_active;
        $language->update(['is_active' => $isActive]);

        $this->auditLogService->log(
            'SETTINGS_CHANGE',
            'SETTINGS',
            $language->locale_code,
            $language->display_name,
            ['is_active' => ['old' => $old, 'new' => $language->is_active]],
        );

        return true;
    }
}

