<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\PlatformLanguage;
use Illuminate\Http\Request;
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
            ],
            'locale' => app()->getLocale(),
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
