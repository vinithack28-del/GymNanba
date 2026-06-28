<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\Tenant\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $svc) {}

    private function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    // ── Landing ───────────────────────────────────────────────────────────────

    public function index(){
        return Inertia::render('Tenant/Reports/Index', [
            'canRevenue'    => $this->svc->canRevenue(),
            'canMembers'    => $this->svc->canMembers(),
            'canAttendance' => $this->svc->canAttendance(),
            'canStaff'      => $this->svc->canStaff(),
        ]);
    }

    // ── Revenue ───────────────────────────────────────────────────────────────

    public function revenue(Request $request){
        abort_unless($this->svc->canRevenue(), 403);
        $data = $this->svc->revenue($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Revenue'$data);
    }

    public function exportRevenue(Request $request): Response
    {
        abort_unless($this->svc->canRevenue(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportRevenueCsv($request, $this->tenantId());
        $gym   = str_replace(' ', '_', strtolower(request()->user()->tenant->gym_name ?? 'gym'));
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"gymos_revenue_{$gym}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv\"",
        ]);
    }

    // ── Members ───────────────────────────────────────────────────────────────

    public function members(Request $request){
        abort_unless($this->svc->canMembers(), 403);
        $data = $this->svc->members($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Members'$data);
    }

    public function exportMembers(Request $request): Response
    {
        abort_unless($this->svc->canMembers(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportMembersCsv($request, $this->tenantId());
        $gym   = str_replace(' ', '_', strtolower(request()->user()->tenant->gym_name ?? 'gym'));
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"gymos_members_{$gym}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv\"",
        ]);
    }

    // ── Attendance ────────────────────────────────────────────────────────────

    public function attendance(Request $request){
        abort_unless($this->svc->canAttendance(), 403);
        $data = $this->svc->attendance($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Attendance'$data);
    }

    public function exportAttendance(Request $request): Response
    {
        abort_unless($this->svc->canAttendance(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportAttendanceCsv($request, $this->tenantId());
        $gym   = str_replace(' ', '_', strtolower(request()->user()->tenant->gym_name ?? 'gym'));
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"gymos_attendance_{$gym}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv\"",
        ]);
    }

    // ── Staff ─────────────────────────────────────────────────────────────────

    public function staff(Request $request){
        abort_unless($this->svc->canStaff(), 403);
        $data = $this->svc->staff($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Staff'$data);
    }

    public function exportStaff(Request $request): Response
    {
        abort_unless($this->svc->canStaff(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportStaffCsv($request, $this->tenantId());
        $gym   = str_replace(' ', '_', strtolower(request()->user()->tenant->gym_name ?? 'gym'));
        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"gymos_staff_{$gym}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv\"",
        ]);
    }
}
