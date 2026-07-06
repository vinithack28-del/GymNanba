<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Shared helpers for tenant-scoped controllers: resolving the current tenant,
 * applying the session-selected branch filter, guarding record ownership and
 * streaming CSV downloads.
 */
trait InteractsWithTenant
{
    protected const SELECTED_BRANCH_SESSION_KEY = 'gymos_selected_branch_id';

    /**
     * The id of the tenant owning the current request.
     */
    protected function tenantId(): int
    {
        return request()->user()->tenant->id;
    }

    /**
     * The branch the owner has selected in the session, if any.
     */
    protected function selectedBranchId(): ?int
    {
        $id = session(self::SELECTED_BRANCH_SESSION_KEY);

        return $id !== null ? (int) $id : null;
    }

    /**
     * Default the request's branch_id filter to the session-selected branch.
     */
    protected function applySelectedBranch(Request $request): void
    {
        if (! $request->filled('branch_id') && $id = session(self::SELECTED_BRANCH_SESSION_KEY)) {
            $request->merge(['branch_id' => $id]);
        }
    }

    /**
     * Resolve the selected branch model, clearing the session when it no longer
     * belongs to the tenant (or is inactive).
     */
    protected function resolveSelectedBranch(int $tenantId): ?Branch
    {
        $id = session(self::SELECTED_BRANCH_SESSION_KEY);

        if (! $id) {
            return null;
        }

        $branch = Branch::forTenant($tenantId)->active()->find($id);

        if (! $branch) {
            session()->forget(self::SELECTED_BRANCH_SESSION_KEY);
        }

        return $branch;
    }

    /**
     * 404 when the given record does not belong to the current tenant.
     */
    protected function abortIfNotTenant(Model $model): void
    {
        abort_if($model->getAttribute('tenant_id') !== $this->tenantId(), 404);
    }

    /**
     * Build a CSV file download response with the standard headers.
     */
    protected function csvDownload(string $csv, string $filename): Response
    {
        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }
}
