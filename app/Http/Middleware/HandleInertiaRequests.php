<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\PlatformLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $this->getUserPermissions($request),
            ],
            'locale' => app()->getLocale(),
            'translations' => [
                'common' => fn () => trans('common'),
            ],
            'flash' => [
                'status' => fn () => $request->session()->get('status'),
                'error' => fn () => $request->session()->get('error'),
                'email_sent' => fn () => $request->session()->get('email_sent'),
            ],
            'portalLanguages' => fn () => $this->getActivePortalLanguages(),
            'branchContext' => fn () => $this->getBranchContext($request),
        ];
    }

    private function getActivePortalLanguages(): array
    {
        try {
            return PlatformLanguage::query()
                ->where('is_active', true)
                ->orderBy('display_name')
                ->get(['locale_code', 'display_name'])
                ->toArray();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getUserPermissions(Request $request): array
    {
        $user = $request->user();

        if (! $user) {
            return [];
        }

        if ($user->isSuperAdmin() || $user->isGymOwner()) {
            return ['*'];
        }

        if (! $user->tenant_id) {
            return [];
        }

        try {
            return DB::table('permissions as p')
                ->join('role_has_permissions as rhp', 'rhp.permission_id', '=', 'p.id')
                ->join('roles as r', 'r.id', '=', 'rhp.role_id')
                ->join('model_has_roles as mhr', function ($join) use ($user): void {
                    $join->on('mhr.role_id', '=', 'r.id')
                        ->where('mhr.model_type', '=', $user::class)
                        ->where('mhr.model_id', '=', $user->id)
                        ->where('mhr.tenant_id', '=', $user->tenant_id);
                })
                ->distinct()
                ->pluck('p.name')
                ->values()
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function getBranchContext(Request $request): ?array
    {
        $user = $request->user();

        if (! $user || $user->isSuperAdmin() || ! $user->tenant_id) {
            return null;
        }

        try {
            $branches = Branch::forTenant($user->tenant_id)
                ->active()
                ->orderByRaw('is_primary DESC, name ASC')
                ->get(['id', 'name', 'is_primary']);

            $ownerCanSwitch = $user->isGymOwner();
            $selectedBranchId = $ownerCanSwitch
                ? session('gymos_selected_branch_id')
                : $user->branch_id;

            $selectedBranch = $selectedBranchId
                ? $branches->firstWhere('id', $selectedBranchId)
                : null;

            if ($ownerCanSwitch && $selectedBranchId && ! $selectedBranch) {
                session()->forget('gymos_selected_branch_id');
                $selectedBranchId = null;
            }

            return [
                'ownerCanSwitch' => $ownerCanSwitch,
                'selectedBranchId' => $selectedBranchId,
                'selectedBranchName' => $selectedBranch?->name,
                'branches' => $branches->map(fn (Branch $branch) => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                    'is_primary' => (bool) $branch->is_primary,
                ])->values()->all(),
            ];
        } catch (\Throwable) {
            return null;
        }
    }
}
