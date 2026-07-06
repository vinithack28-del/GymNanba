<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Concerns\InteractsWithTenant;
use App\Http\Controllers\Controller;
use App\Services\Tenant\ReportService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;

class ReportController extends Controller
{
    use InteractsWithTenant;

    public function __construct(private readonly ReportService $svc) {}

    private function gymSlug(): string
    {
        return str_replace(' ', '_', strtolower(request()->user()->tenant->gym_name ?? 'gym'));
    }

    // â”€â”€ Landing â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function index(){
        return Inertia::render('Tenant/Reports/Index', [
            'canRevenue'    => $this->svc->canRevenue(),
            'canMembers'    => $this->svc->canMembers(),
            'canAttendance' => $this->svc->canAttendance(),
            'canStaff'      => $this->svc->canStaff(),
        ]);
    }

    // â”€â”€ Revenue â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function revenue(Request $request){
        abort_unless($this->svc->canRevenue(), 403);
        $data = $this->svc->revenue($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Revenue', $data);
    }

    public function exportRevenue(Request $request): Response
    {
        abort_unless($this->svc->canRevenue(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportRevenueCsv($request, $this->tenantId());
        return $this->csvDownload($csv, "gymos_revenue_{$this->gymSlug()}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv");
    }

    // â”€â”€ Members â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function members(Request $request){
        abort_unless($this->svc->canMembers(), 403);
        $data = $this->svc->members($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Members', $data);
    }

    public function exportMembers(Request $request): Response
    {
        abort_unless($this->svc->canMembers(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportMembersCsv($request, $this->tenantId());
        return $this->csvDownload($csv, "gymos_members_{$this->gymSlug()}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv");
    }

    // â”€â”€ Attendance â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function attendance(Request $request){
        abort_unless($this->svc->canAttendance(), 403);
        $data = $this->svc->attendance($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Attendance', $data);
    }

    public function exportAttendance(Request $request): Response
    {
        abort_unless($this->svc->canAttendance(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportAttendanceCsv($request, $this->tenantId());
        return $this->csvDownload($csv, "gymos_attendance_{$this->gymSlug()}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv");
    }

    // â”€â”€ Staff â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

    public function staff(Request $request){
        abort_unless($this->svc->canStaff(), 403);
        $data = $this->svc->staff($request, $this->tenantId());
        return Inertia::render('Tenant/Reports/Staff', $data);
    }

    public function exportStaff(Request $request): Response
    {
        abort_unless($this->svc->canStaff(), 403);
        $range = $this->svc->resolveRange($request);
        $csv   = $this->svc->exportStaffCsv($request, $this->tenantId());
        return $this->csvDownload($csv, "gymos_staff_{$this->gymSlug()}_{$range['from']->toDateString()}_{$range['to']->toDateString()}.csv");
    }
}

