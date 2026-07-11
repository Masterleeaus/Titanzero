<?php

namespace Modules\TitanZero\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireTitanZeroUse
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && method_exists($user, 'hasPermission')) {
            if (!$user->hasPermission('titanzero.use')) {
                abort(403, 'Titan Zero permission required.');
            }
        }
        return $next($request);
    }
}
