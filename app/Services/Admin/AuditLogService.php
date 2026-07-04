<?php

namespace App\Services\Admin;

use App\Models\AdminAuditLog;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AuditLogService
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return AdminAuditLog::query()
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function log(
        string $actionType,
        string $targetType,
        ?string $targetId,
        ?string $targetName,
        array $difference = [],
        ?Request $request = null,
        ?User $user = null,
    ): void {
        if (! DB::getSchemaBuilder()->hasTable('admin_audit_logs')) {
            return;
        }

        $request ??= request();
        $user ??= $request->user();

        AdminAuditLog::query()->create([
            'actor_admin_id' => $user?->id,
            'actor_name' => $user?->name ?? 'System',
            'actor_ip' => $request->ip(),
            'action_type' => $actionType,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'target_name' => $targetName,
            'difference' => $difference,
            'user_agent' => $request->userAgent(),
            'created_at' => Carbon::now(),
        ]);
    }
}

