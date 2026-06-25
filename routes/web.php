<?php

use App\Http\Controllers\Public\OnlineRegistrationController;
use App\Http\Controllers\Tenant\BranchController;
use App\Http\Controllers\Tenant\MemberController;
use App\Http\Controllers\Tenant\MemberRegistrationController;
use App\Http\Controllers\Tenant\MembershipPlanController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\RenewalController;
use App\Http\Controllers\Tenant\AttendanceController;
use App\Http\Controllers\Tenant\AssessmentController;
use App\Http\Controllers\Tenant\ClassController;
use App\Http\Controllers\Tenant\ExpenseController;
use App\Http\Controllers\Tenant\InvoiceController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\ReportController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\EquipmentController;
use App\Http\Controllers\Tenant\LockerController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController as AdminInvoiceController;
use App\Http\Controllers\Admin\LocalizationController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PasswordChangeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Public member self-registration ─────────────────────────────────────────
Route::get('/join/{token}',         [OnlineRegistrationController::class, 'show'])->name('register.show');
Route::post('/join/{token}',        [OnlineRegistrationController::class, 'submit'])->name('register.submit');
Route::get('/join/{token}/success', [OnlineRegistrationController::class, 'success'])->name('register.success');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'password_changed'])->group(function (): void {
    Route::get('/dashboard', function () {
        return request()->user()?->isSuperAdmin()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('tenant.dashboard');
    })->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/language', [LocalizationController::class, 'update'])->name('language.update');

    Route::prefix('admin')->middleware('super_admin')->name('admin.')->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::get('/tenants/new', [TenantController::class, 'create'])->name('tenants.create');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}', [TenantController::class, 'show'])->name('tenants.show');
        Route::get('/tenants/{tenant}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
        Route::put('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::get('/tenants/{tenant}/delete', [TenantController::class, 'deletePage'])->name('tenants.delete');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
        Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('/invoices', [AdminInvoiceController::class, 'index'])->name('invoices.index');
        Route::post('/invoices/renewals', [AdminInvoiceController::class, 'storeRenewal'])->name('invoices.renewals.store');
        Route::post('/invoices/part-payment', [AdminInvoiceController::class, 'storePartPayment'])->name('invoices.part-payment.store');
        Route::post('/invoices/payments', [AdminInvoiceController::class, 'storePayment'])->name('invoices.payments.store');
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings/languages/{language}', [SettingsController::class, 'updateLanguage'])->name('settings.languages.update');
    });

    Route::middleware('tenant_user')->name('tenant.')->group(function (): void {
        Route::get('/dashboard', [TenantPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/coming-soon/{slug}', [TenantPortalController::class, 'comingSoon'])->name('coming-soon');

        Route::post('/switch-branch', function (\Illuminate\Http\Request $request) {
            $branchId = $request->input('branch_id');
            $tenant   = $request->user()->tenant;
            if ($branchId === 'all') {
                session()->forget('gymos_selected_branch_id');
            } else {
                $branch = \App\Models\Branch::forTenant($tenant->id)->active()->find((int) $branchId);
                if ($branch) {
                    session(['gymos_selected_branch_id' => $branch->id]);
                }
            }
            return redirect()->back();
        })->name('switch-branch');

        Route::prefix('branches')->name('branches.')->group(function (): void {
            Route::get('/', [BranchController::class, 'index'])->name('index');
            Route::get('/create', [BranchController::class, 'create'])->name('create');
            Route::post('/', [BranchController::class, 'store'])->name('store');
            Route::get('/{branch}/edit', [BranchController::class, 'edit'])->name('edit');
            Route::put('/{branch}', [BranchController::class, 'update'])->name('update');
            Route::patch('/{branch}/deactivate', [BranchController::class, 'deactivate'])->name('deactivate');
            Route::patch('/{branch}/reactivate', [BranchController::class, 'reactivate'])->name('reactivate');
        });

        Route::prefix('plans')->name('plans.')->group(function (): void {
            Route::get('/', [MembershipPlanController::class, 'index'])->name('index');
            Route::get('/create', [MembershipPlanController::class, 'create'])->name('create');
            Route::post('/', [MembershipPlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [MembershipPlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [MembershipPlanController::class, 'update'])->name('update');
            Route::post('/{plan}/duplicate', [MembershipPlanController::class, 'duplicate'])->name('duplicate');
            Route::post('/{plan}/archive', [MembershipPlanController::class, 'archive'])->name('archive');
        });

        Route::prefix('renewals')->name('renewals.')->group(function (): void {
            Route::get('/', [RenewalController::class, 'index'])->name('index');
            Route::post('/{member}/renew', [RenewalController::class, 'renew'])->name('renew');
        });

        Route::prefix('members')->name('members.')->group(function (): void {
            Route::get('/', [MemberController::class, 'index'])->name('index');
            Route::get('/create', [MemberController::class, 'create'])->name('create');
            Route::get('/walkin-lookup', [MemberController::class, 'walkinLookup'])->name('walkin-lookup');
            Route::post('/', [MemberController::class, 'store'])->name('store');

            // Online registration management (must be before /{member} wildcard)
            Route::post('/registration-link/email', [MemberRegistrationController::class, 'sendEmail'])->name('registration-link.email');
            Route::prefix('registrations')->name('registrations.')->group(function (): void {
                Route::get('/',                                        [MemberRegistrationController::class, 'index'])->name('index');
                Route::post('/{registration}/confirm',                 [MemberRegistrationController::class, 'confirm'])->name('confirm');
                Route::post('/{registration}/reject',                  [MemberRegistrationController::class, 'reject'])->name('reject');
            });

            Route::get('/{member}', [MemberController::class, 'show'])->name('show');
            Route::get('/{member}/edit', [MemberController::class, 'edit'])->name('edit');
            Route::put('/{member}', [MemberController::class, 'update'])->name('update');
            Route::patch('/{member}/toggle-status', [MemberController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{member}/freeze', [MemberController::class, 'freeze'])->name('freeze');
            Route::patch('/{member}/unfreeze', [MemberController::class, 'unfreeze'])->name('unfreeze');
            Route::delete('/{member}', [MemberController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('staff')->name('staff.')->group(function (): void {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::get('/create', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::get('/roles', [StaffController::class, 'roles'])->name('roles');
            Route::get('/roles/create', [StaffController::class, 'createRole'])->name('roles.create');
            Route::post('/roles', [StaffController::class, 'storeRole'])->name('roles.store');
            Route::get('/roles/{role}/edit', [StaffController::class, 'editRole'])->name('roles.edit');
            Route::put('/roles/{role}', [StaffController::class, 'updateRolePermissions'])->name('roles.update');
            Route::post('/roles/{role}/reset', [StaffController::class, 'resetRolePermissions'])->name('roles.reset');
            Route::delete('/roles/{role}', [StaffController::class, 'destroyRole'])->name('roles.destroy');
            Route::get('/attendance', [StaffController::class, 'attendance'])->name('attendance');
            Route::post('/attendance', [StaffController::class, 'storeAttendance'])->name('attendance.store');
            Route::get('/{staff}', [StaffController::class, 'show'])->name('show');
            Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('/{staff}', [StaffController::class, 'update'])->name('update');
            Route::post('/{staff}/deactivate', [StaffController::class, 'deactivate'])->name('deactivate');
            Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('classes')->name('classes.')->group(function (): void {
            Route::get('/', fn () => redirect()->route('tenant.classes.timetable'))->name('index');
            Route::get('/timetable', [ClassController::class, 'timetable'])->name('timetable');
            Route::get('/trainers', [ClassController::class, 'trainers'])->name('trainers');
            Route::get('/book', [ClassController::class, 'book'])->name('book');
            Route::get('/member-search', [ClassController::class, 'memberSearch'])->name('member-search');
            Route::get('/create', [ClassController::class, 'create'])->name('create');
            Route::post('/', [ClassController::class, 'store'])->name('store');
            Route::get('/{class}', [ClassController::class, 'show'])->name('show');
            Route::get('/{class}/edit', [ClassController::class, 'edit'])->name('edit');
            Route::put('/{class}', [ClassController::class, 'update'])->name('update');
            Route::post('/{class}/cancel', [ClassController::class, 'cancel'])->name('cancel');
            Route::post('/{class}/book', [ClassController::class, 'storeBooking'])->name('book.store');
            Route::delete('/{class}/bookings/{booking}', [ClassController::class, 'cancelBooking'])->name('booking.cancel');
            Route::get('/{class}/attendance', [ClassController::class, 'attendance'])->name('attendance');
            Route::post('/{class}/attendance', [ClassController::class, 'storeAttendance'])->name('attendance.store');
        });

        Route::prefix('attendance')->name('attendance.')->group(function (): void {
            Route::get('/', fn () => redirect()->route('tenant.attendance.checkins'))->name('index');
            Route::get('/checkins', [AttendanceController::class, 'checkins'])->name('checkins');
            Route::post('/checkins', [AttendanceController::class, 'storeCheckin'])->name('checkins.store');
            Route::patch('/checkins/{log}/checkout', [AttendanceController::class, 'checkout'])->name('checkins.checkout');
            Route::delete('/checkins/{log}', [AttendanceController::class, 'destroyCheckin'])->name('checkins.destroy');
            Route::get('/checkins/export', [AttendanceController::class, 'exportCheckins'])->name('checkins.export');
            Route::get('/member-search', [AttendanceController::class, 'memberSearch'])->name('member-search');
            Route::get('/walkins', [AttendanceController::class, 'walkins'])->name('walkins');
            Route::post('/walkins', [AttendanceController::class, 'storeWalkin'])->name('walkins.store');
            Route::post('/walkins/{walkIn}/followup', [AttendanceController::class, 'storeFollowup'])->name('walkins.followup');
            Route::get('/walkins/{walkIn}/followup-history', [AttendanceController::class, 'followupHistory'])->name('walkins.followup-history');
        });

        Route::prefix('assess')->name('assess.')->group(function (): void {
            Route::get('/report', [AssessmentController::class, 'report'])->name('report');
            Route::get('/questionnaire', [AssessmentController::class, 'questionnaire'])->name('questionnaire');
            Route::get('/questionnaire/create', [AssessmentController::class, 'questionnaireCreate'])->name('questionnaire.create');
            Route::get('/questionnaire/{record}/edit', [AssessmentController::class, 'questionnaireEdit'])->name('questionnaire.edit');
            Route::post('/questionnaire', [AssessmentController::class, 'saveQuestionnaire'])->name('questionnaire.save');
            Route::get('/nutrition', [AssessmentController::class, 'nutrition'])->name('nutrition');
            Route::post('/nutrition', [AssessmentController::class, 'storeNutrition'])->name('nutrition.store');
            Route::put('/nutrition/{record}', [AssessmentController::class, 'updateNutrition'])->name('nutrition.update');
            Route::get('/body-metrics', [AssessmentController::class, 'bodyMetrics'])->name('body-metrics');
            Route::post('/body-metrics', [AssessmentController::class, 'storeBodyMetrics'])->name('body-metrics.store');
            Route::put('/body-metrics/{record}', [AssessmentController::class, 'updateBodyMetrics'])->name('body-metrics.update');
            Route::get('/body-metrics/progress', [AssessmentController::class, 'bodyMetricsProgress'])->name('body-metrics.progress');
            Route::get('/posture', [AssessmentController::class, 'posture'])->name('posture');
            Route::post('/posture', [AssessmentController::class, 'storePosture'])->name('posture.store');
            Route::put('/posture/{record}', [AssessmentController::class, 'updatePosture'])->name('posture.update');
            Route::get('/balance', [AssessmentController::class, 'balance'])->name('balance');
            Route::post('/balance', [AssessmentController::class, 'storeBalance'])->name('balance.store');
            Route::put('/balance/{record}', [AssessmentController::class, 'updateBalance'])->name('balance.update');
            Route::post('/balance/{record}/insight', [AssessmentController::class, 'generateBalanceInsight'])->name('balance.insight');
            Route::get('/vitals', [AssessmentController::class, 'vitals'])->name('vitals');
            Route::post('/vitals', [AssessmentController::class, 'storeVitals'])->name('vitals.store');
            Route::put('/vitals/{record}', [AssessmentController::class, 'updateVitals'])->name('vitals.update');
            Route::get('/fitness', [AssessmentController::class, 'fitness'])->name('fitness');
            Route::post('/fitness', [AssessmentController::class, 'storeFitness'])->name('fitness.store');
            Route::put('/fitness/{record}', [AssessmentController::class, 'updateFitness'])->name('fitness.update');
            Route::get('/goal-forecasting', [AssessmentController::class, 'goalForecasting'])->name('goal-forecasting');
            Route::delete('/records/{record}', [AssessmentController::class, 'destroy'])->name('records.destroy');
            Route::get('/member-search', [AssessmentController::class, 'memberSearch'])->name('member-search');
        });

        Route::prefix('expenses')->name('expenses.')->group(function (): void {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('/create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::get('/export', [ExpenseController::class, 'export'])->name('export');
            Route::get('/sub-categories/{category}', [ExpenseController::class, 'subCategories'])->name('sub-categories');
            Route::get('/{expense}/edit', [ExpenseController::class, 'edit'])->name('edit');
            Route::put('/{expense}', [ExpenseController::class, 'update'])->name('update');
            Route::delete('/{expense}', [ExpenseController::class, 'destroy'])->name('destroy');
            Route::post('/{expense}/approve', [ExpenseController::class, 'approve'])->name('approve');
            Route::post('/{expense}/reject', [ExpenseController::class, 'reject'])->name('reject');
        });

        Route::prefix('invoices')->name('invoices.')->group(function (): void {
            Route::get('/', [InvoiceController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceController::class, 'create'])->name('create');
            Route::post('/', [InvoiceController::class, 'store'])->name('store');
            Route::get('/member-search', [InvoiceController::class, 'memberSearch'])->name('member-search');
            Route::post('/compute-totals', [InvoiceController::class, 'computeTotals'])->name('compute-totals');
            Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
            Route::post('/{invoice}/void', [InvoiceController::class, 'void'])->name('void');
        });

        Route::prefix('reports')->name('reports.')->group(function (): void {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/revenue/export', [ReportController::class, 'exportRevenue'])->name('revenue.export');
            Route::get('/members', [ReportController::class, 'members'])->name('members');
            Route::get('/members/export', [ReportController::class, 'exportMembers'])->name('members.export');
            Route::get('/attendance', [ReportController::class, 'attendance'])->name('attendance');
            Route::get('/attendance/export', [ReportController::class, 'exportAttendance'])->name('attendance.export');
            Route::get('/staff', [ReportController::class, 'staff'])->name('staff');
            Route::get('/staff/export', [ReportController::class, 'exportStaff'])->name('staff.export');
        });

        Route::prefix('payments')->name('payments.')->group(function (): void {
            Route::get('/', fn () => redirect()->route('tenant.payments.history'))->name('index');
            Route::get('/collect', [PaymentController::class, 'collect'])->name('collect');
            Route::post('/collect', [PaymentController::class, 'store'])->name('store');
            Route::get('/history', [PaymentController::class, 'history'])->name('history');
            Route::get('/dues', [PaymentController::class, 'dues'])->name('dues');
            Route::get('/member-search', [PaymentController::class, 'memberSearch'])->name('member-search');
            Route::post('/{payment}/void', [PaymentController::class, 'void'])->name('void');
            Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
        });

        Route::prefix('pos')->name('pos.')->group(function (): void {
            Route::get('/', fn () => redirect()->route('tenant.pos.sales'))->name('index');

            Route::get('/products', [PosController::class, 'products'])->name('products');
            Route::get('/products/create', [PosController::class, 'createProduct'])->name('products.create');
            Route::post('/products', [PosController::class, 'storeProduct'])->name('products.store');
            Route::get('/products/{product}/edit', [PosController::class, 'editProduct'])->name('products.edit');
            Route::put('/products/{product}', [PosController::class, 'updateProduct'])->name('products.update');
            Route::patch('/products/{product}/status', [PosController::class, 'toggleProductStatus'])->name('products.status');

            Route::get('/sales', [PosController::class, 'sales'])->name('sales');
            Route::post('/sales', [PosController::class, 'checkout'])->name('sales.checkout');
            Route::get('/sales/{sale}', [PosController::class, 'showSale'])->name('sales.show');
            Route::post('/sales/{sale}/refund', [PosController::class, 'refundSale'])->name('sales.refund');

            Route::get('/stock', [PosController::class, 'stock'])->name('stock');
            Route::post('/stock/restock', [PosController::class, 'restock'])->name('stock.restock');
            Route::post('/stock/adjust', [PosController::class, 'adjust'])->name('stock.adjust');
        });

        Route::prefix('equipment')->name('equipment.')->group(function (): void {
            Route::get('/',                                                     [EquipmentController::class, 'index'])->name('index');
            Route::get('/create',                                              [EquipmentController::class, 'create'])->name('create');
            Route::get('/summary',                                              [EquipmentController::class, 'summary'])->name('summary');
            Route::post('/',                                                    [EquipmentController::class, 'store'])->name('store');
            Route::get('/{equipment}/details',                                  [EquipmentController::class, 'details'])->name('details');
            Route::put('/{equipment}',                                          [EquipmentController::class, 'update'])->name('update');
            Route::delete('/{equipment}',                                       [EquipmentController::class, 'destroy'])->name('destroy');
            Route::post('/{equipment}/service-records',                         [EquipmentController::class, 'storeServiceRecord'])->name('service-records.store');
            Route::delete('/{equipment}/service-records/{record}',              [EquipmentController::class, 'destroyServiceRecord'])->name('service-records.destroy');
        });

        Route::prefix('lockers')->name('lockers.')->group(function (): void {
            Route::get('/', [LockerController::class, 'index'])->name('index');
            Route::get('/create', [LockerController::class, 'create'])->name('create');
            Route::post('/', [LockerController::class, 'store'])->name('store');
            Route::get('/member-search', [LockerController::class, 'memberSearch'])->name('member-search');
            Route::get('/{locker}', [LockerController::class, 'show'])->name('show');
            Route::get('/{locker}/details', [LockerController::class, 'details'])->name('details');
            Route::put('/{locker}', [LockerController::class, 'update'])->name('update');
            Route::delete('/{locker}', [LockerController::class, 'destroy'])->name('destroy');
            Route::post('/{locker}/assign', [LockerController::class, 'assign'])->name('assign');
            Route::post('/{locker}/reassign', [LockerController::class, 'reassign'])->name('reassign');
            Route::post('/{locker}/release', [LockerController::class, 'release'])->name('release');
        });

        Route::prefix('settings')->name('settings.')->group(function (): void {
            Route::get('/profile', [SettingController::class, 'profile'])->name('profile');
            Route::put('/profile', [SettingController::class, 'updateProfile'])->name('profile.update');

            Route::get('/account', [SettingController::class, 'account'])->name('account');
            Route::put('/account', [SettingController::class, 'updateAccount'])->name('account.update');
            Route::put('/account/password', [SettingController::class, 'changePassword'])->name('account.password');
            Route::delete('/account/sessions/{session}', [SettingController::class, 'terminateSession'])->name('account.sessions.terminate');
            Route::delete('/account/sessions', [SettingController::class, 'terminateOtherSessions'])->name('account.sessions.terminate-others');

            Route::get('/integrations', [SettingController::class, 'integrations'])->name('integrations');
            Route::put('/integrations/{key}', [SettingController::class, 'updateIntegration'])->name('integrations.update');
            Route::delete('/integrations/{key}', [SettingController::class, 'disconnectIntegration'])->name('integrations.disconnect');
            Route::post('/integrations/{key}/test', [SettingController::class, 'testIntegration'])->name('integrations.test');

            Route::get('/language', [SettingController::class, 'language'])->name('language');
            Route::put('/language', [SettingController::class, 'updateLanguage'])->name('language.update');

            Route::get('/subscription', [SettingController::class, 'subscription'])->name('subscription');

            Route::get('/data', [SettingController::class, 'data'])->name('data');
            Route::post('/data/export', [SettingController::class, 'exportData'])->name('data.export');
            Route::post('/data/delete-request', [SettingController::class, 'requestDeletion'])->name('data.delete-request');

            Route::redirect('/', '/settings/profile')->name('index');
        });
    });
});

Route::middleware('auth')->group(function (): void {
    Route::get('/password/change-required', [PasswordChangeController::class, 'edit'])->name('password.change');
    Route::post('/password/change-required', [PasswordChangeController::class, 'update'])->name('password.change.update');
});
