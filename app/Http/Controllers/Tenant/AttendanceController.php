<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AttendanceLog;
use App\Models\WalkIn;
use App\Services\Tenant\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $service) {}

    // ── Check-ins ─────────────────────────────────────────────────────────────

    public function checkins(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->service->listCheckins($request->user(), $request);

        return view('tenant.attendance.checkins', $data);
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

        $log = $this->service->storeCheckin($request->user(), $validated + [
            'checked_in_by' => $request->user()->id ?? null,
        ]);

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

    // ── Walk-ins ─────────────────────────────────────────────────────────────

    public function walkins(Request $request): View
    {
        if (!$request->filled('branch_id') && $id = session('gymos_selected_branch_id')) {
            $request->merge(['branch_id' => $id]);
        }
        $data = $this->service->listWalkins($request->user(), $request);

        return view('tenant.attendance.walkins', $data);
    }

    public function storeWalkin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:100'],
            'phone'          => ['required', 'string', 'max:20'],
            'purpose'        => ['required', 'in:'.implode(',', WalkIn::PURPOSES)],
            'fee_paise'      => ['nullable', 'integer', 'min:0'],
            'payment_method' => ['nullable', 'in:'.implode(',', WalkIn::METHODS)],
            'reference'      => ['nullable', 'string', 'max:100'],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'guest_of_id'    => ['nullable', 'integer'],
            'branch_id'      => ['nullable', 'integer'],
        ]);

        $this->service->storeWalkin($request->user(), $validated);

        return back()->with('status', __('attendance.flash.walkin_logged'));
    }
}
