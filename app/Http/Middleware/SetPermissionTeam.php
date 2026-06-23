<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetPermissionTeam
{
    public function handle(Request $request, Closure $next)
    {
        if (function_exists('setPermissionsTeamId')) {
            setPermissionsTeamId($request->user()?->tenant_id);
        }

        return $next($request);
    }
}
