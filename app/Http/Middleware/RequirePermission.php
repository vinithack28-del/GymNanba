<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * Usage in routes: ->middleware('permission:members.view')
     * OR logic:        ->middleware('permission:payments.collect|payments.history')
     * Owner-only:      ->middleware('permission:owner_only')
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Super-admins and gym owners bypass all permission checks
        if ($user->isSuperAdmin() || $user->isGymOwner()) {
            return $next($request);
        }

        foreach ($permissions as $permissionGroup) {
            // owner_only sentinel — non-owners are denied immediately
            if ($permissionGroup === 'owner_only') {
                abort(403, 'This page is restricted to the gym owner.');
            }

            // OR logic within a group: "payments.collect|payments.history"
            foreach (explode('|', $permissionGroup) as $permission) {
                if ($user->can(trim($permission))) {
                    return $next($request);
                }
            }
        }

        abort(403, 'You do not have permission to access this page.');
    }
}
