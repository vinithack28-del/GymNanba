<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\WalkIn;
use App\Models\WalkInFollowup;
use App\Services\Tenant\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service) {}

    // â”€â”€ Check-ins â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function checkins(Request $request){
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }

        if ($request->get('view') === 'sheet') {
            $data = $this->service->sheetView($request->user(), $request);
            return Inertia::render('Tenant/Attendance/Checkins', array_merge($data, ['viewMode' => 'sheet']));
        }

        $data = $this->service->listCheckins($request->user(), $request);

        return Inertia::render('Tenant/Attendance/Checkins', array_merge($data, ['viewMode' => 'list']));
    }

    public function storeCheckin(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'member_id' => ['required', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'method'    => ['nullable', 'in:'.implode(',', AttendanceLog::METHODS)],
            'reason'    => ['nullable', 'string', 'max:500'],
            'force'     => ['nullable', 'boolean'],
        ]);

        $log = $this->service->storeCheckin($request->user(), $validated);

        if ($request->expectsJson()) {
            return response()->json([
                'ok'  => true,
                'log' => [
                    'id'           => $log->id,
                    'member_name'  => $log->member?->name,
                    'checked_in_at'=> $log->checked_in_at->format('H:i'),
                ],
            ]);
        }

        return back()->with('status', __('attendance.flash.checked_in'));
    }

    public function checkout(Request $request, AttendanceLog $log): JsonResponse|RedirectResponse
    {
        $log = $this->service->checkout($request->user(), $log);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'checked_out_at' => $log->checked_out_at->format('H:i')]);
        }

        return back()->with('status', __('attendance.flash.checked_out'));
    }

    public function destroyCheckin(Request $request, AttendanceLog $log): RedirectResponse
    {
        $this->service->destroy($request->user(), $log);

        return back()->with('status', __('attendance.flash.deleted'));
    }

    public function memberSearch(Request $request): JsonResponse
    {
        $q       = trim((string) $request->get('q'));
        $members = $this->service->memberSearch($request->user(), $q);

        return response()->json($members);
    }

    public function exportCheckins(Request $request): Response
    {
        $csv  = $this->service->exportCheckinsCsv($request->user(), $request);
        $date = $request->get('date', now()->toDateString());

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"checkins-{$date}.csv\"",
        ]);
    }

    // â”€â”€ Walk-ins â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function walkins(Request $request){
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->service->listWalkins($request->user(), $request);

        return Inertia::render('Tenant/Attendance/Walkins', $data);
    }

    public function storeWalkin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'regex:/^\d{10,20}$/'],
            'purpose'        => ['required', 'in:'.implode(',', WalkIn::PURPOSES)],
            'plan_id'        => ['nullable', 'integer', 'exists:gym_membership_plans,id'],
            'fee_paise'      => ['nullable', 'integer', 'min:0'],
            'payment_methods' => ['nullable', 'array'],
            'payment_methods.*' => ['required', 'in:'.implode(',', WalkIn::METHODS)],
            'amounts'         => ['nullable', 'array'],
            'amounts.*'       => ['nullable', 'numeric', 'min:0'],
            'references'      => ['nullable', 'array'],
            'references.*'    => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'guest_of_id'    => ['nullable', 'integer'],
            'branch_id'      => ['nullable', 'integer'],
        ]);

        $this->service->storeWalkin($request->user(), $validated);

        return back()->with('status', __('attendance.flash.walkin_logged'));
    }

    public function storeFollowup(Request $request, WalkIn $walkIn): RedirectResponse
    {
        $validated = $request->validate([
            'outcome'            => ['required', 'in:'.implode(',', WalkInFollowup::OUTCOMES)],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'next_followup_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $this->service->storeFollowup($request->user(), $walkIn, $validated);

        return back()->with('status', 'Follow-up logged successfully.');
    }

    public function followupHistory(WalkIn $walkIn, Request $request): JsonResponse
    {
        abort_unless($walkIn->tenant_id === $request->user()->tenant_id, 403);

        $history = $walkIn->followups()->with('loggedByUser')->get()->map(fn ($f) => [
            'id'                 => $f->id,
            'outcome'            => $f->outcome,
            'notes'              => $f->notes,
            'next_followup_date' => $f->next_followup_date?->format('d-m-Y'),
            'logged_by'          => $f->loggedByUser?->name ?? 'Staff',
            'created_at'         => $f->created_at->format('d-m-Y, H:i'),
        ]);

        return response()->json([
            'walk_in' => [
                'name'  => $walkIn->name,
                'phone' => $walkIn->phone,
            ],
            'history' => $history,
        ]);
    }
}
