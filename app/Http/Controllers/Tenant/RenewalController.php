<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\Branch;
use App\Models\GymMembershipPlan;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RenewalController extends Controller
{
    public function index(Request $request){
        $tenant = $request->user()->tenant;
        $today  = now()->toDateString();

        $selectedBranchId = session('gymos_selected_branch_id');
        $selectedBranch   = null;
        if ($selectedBranchId) {
            $selectedBranch = Branch::forTenant($tenant->id)->active()->find($selectedBranchId);
            if (!$selectedBranch) {
                session()->forget('gymos_selected_branch_id');
                $selectedBranchId = null;
            }
        }

        $base = fn () => Member::forTenant($tenant->id)
            ->with('plan')
            ->when($selectedBranchId, fn ($q) => $q->where('branch_id', $selectedBranchId));

        $stats = [
            'expired'     => $base()->where(fn ($q) => $q->whereDate('expiry_date', '<', $today)->orWhere(fn ($q2) => $this->whereSessionExhausted($q2)))->count(),
            'today'       => $base()->whereDate('expiry_date', '=', $today)->count(),
            'seven_days'  => $base()->whereDate('expiry_date', '>', $today)->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString())->count(),
            'thirty_days' => $base()->whereDate('expiry_date', '>', $today)->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())->count(),
        ];

        $tab  = $request->get('tab', '7days');
        $from = $request->get('from');
        $to   = $request->get('to');
        $planId = $request->get('plan_id');

        $query = $base();

        match ($tab) {
            'all'     => null,
            'expired' => $query->where(fn ($q) => $q->whereDate('expiry_date', '<', $today)->orWhere(fn ($q2) => $this->whereSessionExhausted($q2))),
            'today'   => $query->whereDate('expiry_date', '=', $today),
            '3days'   => $query->whereDate('expiry_date', '>', $today)->whereDate('expiry_date', '<=', now()->addDays(3)->toDateString()),
            '7days'   => $query->whereDate('expiry_date', '>', $today)->whereDate('expiry_date', '<=', now()->addDays(7)->toDateString()),
            '30days'  => $query->whereDate('expiry_date', '>', $today)->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString()),
            'custom'  => $query
                ->when($from, fn ($q) => $q->whereDate('expiry_date', '>=', $from))
                ->when($to,   fn ($q) => $q->whereDate('expiry_date', '<=', $to)),
            default   => $query->where(fn ($q) => $q
                ->whereDate('expiry_date', '<=', now()->addDays(30)->toDateString())
                ->orWhere(fn ($q2) => $this->whereSessionExhausted($q2))),
        };

        if ($planId) {
            $query->where('plan_id', $planId);
        }

        // Most overdue first, then nearest expiry
        $query->orderByRaw("expiry_date ASC");

        $members  = $query->paginate(25)->withQueryString();
        $members->getCollection()->transform(function (Member $member) {
            $usedSessions = $this->memberUsedSessions($member);
            $sessionLimit = (int) ($member->plan?->session_limit ?? 0);
            $member->setAttribute('used_sessions', $usedSessions);
            $member->setAttribute('session_limit', $sessionLimit);
            $member->setAttribute('renewal_due_type', $sessionLimit > 0 && $usedSessions >= $sessionLimit ? 'sessions' : 'duration');

            return $member->append(['initials', 'balance_rupees']);
        });
        $plans    = GymMembershipPlan::forTenant($tenant->id)->active()->orderBy('name')->get();
        $branches = Branch::forTenant($tenant->id)->active()->orderByRaw('is_primary DESC, name ASC')->get();

        return Inertia::render('Tenant/Renewals/Index', compact(
            'stats', 'members', 'plans', 'branches', 'today', 'tab', 'from', 'to', 'selectedBranch', 'planId'
        ));
    }

    public function renew(Request $request, Member $member): RedirectResponse
    {
        abort_unless($member->tenant_id === $request->user()->tenant?->id, 403);

        $validated = $request->validate([
            'plan_id'        => 'required|exists:gym_membership_plans,id',
            'start_date'     => 'required|date',
            'payment_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:cash,upi,card,bank,cheque',
            'notes'          => 'nullable|string|max:300',
        ]);

        $plan = GymMembershipPlan::find($validated['plan_id']);
        abort_unless($plan && $plan->tenant_id === $member->tenant_id, 422);

        $expiryDate = $plan->computeExpiryDate($validated['start_date']);

        $member->update([
            'plan_id'    => $plan->id,
            'plan_name'  => $plan->name,
            'start_date' => $validated['start_date'],
            'expiry_date'=> $expiryDate,
            'status'     => 'active',
            'notes'      => $validated['notes'] ?? $member->notes,
        ]);

        return redirect()->route('tenant.renewals.index')
            ->with('status', $expiryDate
                ? "Membership renewed for {$member->name}. New expiry: {$expiryDate}."
                : "Session membership renewed for {$member->name}.");
    }

    private function whereSessionExhausted($query)
    {
        return $query->whereHas('plan', fn ($planQuery) => $planQuery
            ->whereNotNull('session_limit')
            ->where('session_limit', '>', 0)
            ->whereRaw('(
                select count(*)
                from attendance_logs
                where attendance_logs.member_id = members.id
                  and attendance_logs.tenant_id = members.tenant_id
                  and (members.start_date is null or DATE(attendance_logs.checked_in_at) >= members.start_date)
            ) >= gym_membership_plans.session_limit')
        );
    }

    private function memberUsedSessions(Member $member): int
    {
        return AttendanceLog::query()
            ->where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->when($member->start_date, fn ($q) => $q->whereDate('checked_in_at', '>=', $member->start_date->toDateString()))
            ->count();
    }
}

