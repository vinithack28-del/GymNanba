<?php

namespace App\Services\Tenant;

use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\Member;
use App\Models\WalkIn;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AttendanceService
{
    // ── Check-in log ─────────────────────────────────────────────────────────

    public function listCheckins(object $user, Request $request): array
    {
        $date     = $request->get('date', now()->toDateString());
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));
        $method   = $request->get('method');
        $search   = trim((string) $request->get('search'));

        $query = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->with(['member', 'branch', 'checkedInBy'])
            ->whereDate('checked_in_at', $date)
            ->orderByDesc('checked_in_at');

        if ($branchId) {
            $query->forBranch($branchId);
        }

        if ($method) {
            $query->where('method', $method);
        }

        if ($search) {
            $query->whereHas('member', fn ($q) => $q
                ->where('name', 'ilike', "%{$search}%")
                ->orWhere('member_code', 'ilike', "%{$search}%")
                ->orWhere('phone', 'ilike', "%{$search}%")
            );
        }

        $logs = $query->paginate(30)->withQueryString();

        return [
            'logs'        => $logs,
            'stats'       => $this->checkinStats($user->tenant_id, $date, $branchId),
            'branches'    => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'methods'     => AttendanceLog::METHODS,
            'date'        => $date,
            'branchId'    => $branchId,
            'canManage'   => $this->canManage($user),
            'canCheckin'  => $this->canCheckin($user),
        ];
    }

    public function storeCheckin(object $user, array $validated): AttendanceLog
    {
        $member = Member::query()
            ->forTenant($user->tenant_id)
            ->findOrFail($validated['member_id']);

        // Block inactive members
        abort_if($member->status === 'inactive', 422, 'Member is inactive and cannot check in.');

        // Check for already open check-in today
        $openToday = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->where('member_id', $member->id)
            ->whereDate('checked_in_at', now()->toDateString())
            ->whereNull('checked_out_at')
            ->exists();

        if ($openToday && ! ($validated['force'] ?? false)) {
            abort(409, 'Member already has an open check-in today.');
        }

        $branchId = $this->resolveBranch($user, $validated['branch_id'] ?? null)
            ?? $user->branch_id
            ?? Branch::forTenant($user->tenant_id)->active()->value('id');

        return AttendanceLog::query()->create([
            'tenant_id'     => $user->tenant_id,
            'member_id'     => $member->id,
            'branch_id'     => $branchId,
            'method'        => $validated['method'] ?? 'manual',
            'checked_in_at' => now(),
            'reason'        => $validated['reason'] ?? null,
            'checked_in_by' => $validated['checked_in_by'] ?? null,
            'created_at'    => now(),
        ]);
    }

    public function checkout(object $user, AttendanceLog $log): AttendanceLog
    {
        abort_unless($log->tenant_id === $user->tenant_id, 403);
        abort_unless(is_null($log->checked_out_at), 422, 'Already checked out.');

        $log->update(['checked_out_at' => now()]);

        return $log->fresh();
    }

    public function destroy(object $user, AttendanceLog $log): void
    {
        abort_unless($log->tenant_id === $user->tenant_id, 403);
        abort_unless($this->canManage($user), 403);

        $log->delete();
    }

    public function memberSearch(object $user, string $query): Collection
    {
        if (strlen($query) < 2) {
            return collect();
        }

        return Member::query()
            ->forTenant($user->tenant_id)
            ->where('status', '!=', 'inactive')
            ->where(fn ($q) => $q
                ->where('name', 'ilike', "%{$query}%")
                ->orWhere('phone', 'ilike', "%{$query}%")
                ->orWhere('member_code', 'ilike', "%{$query}%")
            )
            ->with('plan')
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'phone', 'member_code', 'photo_url', 'plan_name', 'expiry_date', 'status']);
    }

    public function exportCheckinsCsv(object $user, Request $request): string
    {
        $date     = $request->get('date', now()->toDateString());
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));
        $method   = $request->get('method');

        $query = AttendanceLog::query()
            ->forTenant($user->tenant_id)
            ->with(['member', 'branch', 'checkedInBy'])
            ->whereDate('checked_in_at', $date)
            ->orderByDesc('checked_in_at');

        if ($branchId) {
            $query->forBranch($branchId);
        }
        if ($method) {
            $query->where('method', $method);
        }

        $rows = $query->get();

        $lines = ["Date,Time,Member name,Member ID,Plan,Branch,Method,Check-out,Duration,Logged by"];

        foreach ($rows as $log) {
            $lines[] = implode(',', [
                $log->checked_in_at->toDateString(),
                $log->checked_in_at->format('H:i'),
                '"'.str_replace('"', '""', $log->member?->name ?? '').'"',
                $log->member?->member_code ?? '',
                '"'.str_replace('"', '""', $log->member?->plan_name ?? '').'"',
                '"'.str_replace('"', '""', $log->branch?->name ?? '').'"',
                $log->method,
                $log->checked_out_at?->format('H:i') ?? '',
                $log->duration ?? '',
                '"'.str_replace('"', '""', $log->checkedInBy?->name ?? '').'"',
            ]);
        }

        return implode("\n", $lines);
    }

    // ── Walk-ins ─────────────────────────────────────────────────────────────

    public function listWalkins(object $user, Request $request): array
    {
        $date     = $request->get('date', now()->toDateString());
        $branchId = $this->resolveBranch($user, $request->get('branch_id'));

        $query = WalkIn::query()
            ->forTenant($user->tenant_id)
            ->with(['branch', 'guestOf'])
            ->whereDate('created_at', $date)
            ->orderByDesc('created_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $logs = $query->paginate(30)->withQueryString();

        $todayTotal   = $query->toBase()->count();
        $todayRevenue = WalkIn::query()
            ->forTenant($user->tenant_id)
            ->whereDate('created_at', $date)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->sum('fee_paise');

        return [
            'logs'        => $logs,
            'branches'    => Branch::forTenant($user->tenant_id)->active()->orderBy('name')->get(),
            'members'     => Member::forTenant($user->tenant_id)->where('status', 'active')->orderBy('name')->get(['id', 'name', 'member_code']),
            'purposes'    => WalkIn::PURPOSES,
            'methods'     => WalkIn::METHODS,
            'date'        => $date,
            'todayTotal'  => $todayTotal,
            'todayRevenue'=> $todayRevenue,
            'canManage'   => $this->canManage($user),
        ];
    }

    public function storeWalkin(object $user, array $validated): WalkIn
    {
        $branchId = $this->resolveBranch($user, $validated['branch_id'] ?? null)
            ?? $user->branch_id
            ?? Branch::forTenant($user->tenant_id)->active()->value('id');

        return WalkIn::query()->create([
            'tenant_id'      => $user->tenant_id,
            'branch_id'      => $branchId,
            'name'           => $validated['name'],
            'phone'          => $validated['phone'],
            'purpose'        => $validated['purpose'],
            'fee_paise'      => $validated['fee_paise'] ?? 0,
            'payment_method' => ($validated['fee_paise'] ?? 0) > 0 ? ($validated['payment_method'] ?? null) : null,
            'reference'      => $validated['reference'] ?? null,
            'notes'          => $validated['notes'] ?? null,
            'guest_of_id'    => $validated['guest_of_id'] ?? null,
            'logged_by'      => null,
            'created_at'     => now(),
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function checkinStats(int $tenantId, string $date, ?int $branchId): array
    {
        $base = AttendanceLog::query()
            ->where('tenant_id', $tenantId)
            ->whereDate('checked_in_at', $date);

        if ($branchId) {
            $base->where('branch_id', $branchId);
        }

        $total  = (clone $base)->count();
        $unique = (clone $base)->distinct('member_id')->count('member_id');

        // Peak hour: group by hour
        $byHour = (clone $base)
            ->selectRaw("date_part('hour', checked_in_at) as hour, COUNT(*) as cnt")
            ->groupByRaw("date_part('hour', checked_in_at)")
            ->orderByDesc('cnt')
            ->first();

        $peakHour = $byHour
            ? sprintf('%02d:00–%02d:00 (%d)', $byHour->hour, $byHour->hour + 1, $byHour->cnt)
            : '—';

        return [
            'total'     => $total,
            'unique'    => $unique,
            'peak_hour' => $peakHour,
        ];
    }

    public function canManage(object $user): bool
    {
        return in_array($user->role, ['tenant_owner'], true);
    }

    public function canCheckin(object $user): bool
    {
        return in_array($user->role, ['tenant_owner', 'branch_manager', 'receptionist'], true);
    }

    private function resolveBranch(object $user, mixed $branchId): ?int
    {
        if (in_array($user->role, ['branch_manager', 'branch_admin'], true)) {
            return (int) $user->branch_id;
        }

        return filled($branchId) ? (int) $branchId : null;
    }
}
