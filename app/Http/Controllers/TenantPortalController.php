<?php

namespace App\Http\Controllers;

use App\Services\Tenant\DashboardService;
use Inertia\Inertia;

class TenantPortalController extends Controller
{
    public function __construct(private readonly DashboardService $dashboardService) {}

    public function dashboard(){
        $tenant = request()->user()?->tenant;

        return Inertia::render('Tenant/Portal', $this->dashboardService->build(request()->user()) + [
            'tenant' => $tenant,
        ]);
    }

    public function comingSoon(string $slug){
        $pages = $this->pageTitles();

        abort_unless(array_key_exists($slug, $pages), 404);

        return Inertia::render('Tenant/ComingSoon', [
            'tenant' => request()->user()?->tenant,
            'pageTitle' => $pages[$slug],
        ]);
    }

    private function pageTitles(): array
    {
        return [
            'members' => 'Members',
            'memberships-plans' => 'Memberships / plans',
            'renewals-due' => 'Renewals due',
            'attendance' => 'Attendance',
            'check-in-log' => 'Check-in log',
            'walk-ins' => 'Walk-ins',
            'classes-schedules' => 'Classes & schedules',
            'timetable' => 'Timetable',
            'book-a-class' => 'Book a class',
            'trainers' => 'Trainers',
            'branches' => 'Branches',
            'staff' => 'Staff',
            'all-staff' => 'All staff',
            'roles-permissions' => 'Roles & permissions',
            'staff-attendance' => 'Staff attendance',
            'payments' => 'Payments',
            'collect-fee' => 'Collect fee',
            'payment-history' => 'Payment history',
            'pending-dues' => 'Pending dues',
            'invoices' => 'Invoices',
            'pos-store' => 'POS / store',
            'products' => 'Products',
            'sales' => 'Sales',
            'stock' => 'Stock',
            'expenses' => 'Expenses',
            'reports' => 'Reports',
            'revenue-report' => 'Revenue report',
            'member-report' => 'Member report',
            'attendance-report' => 'Attendance report',
            'staff-report' => 'Staff report',
            'notifications' => 'Notifications',
            'settings' => 'Settings',
            'gym-profile' => 'Gym profile',
            'my-account' => 'My account',
            'integrations' => 'Integrations',
            'language' => 'Language',
        ];
    }
}

