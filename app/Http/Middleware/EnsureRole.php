<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (! $request->user() || ! in_array($request->user()->role, $roles)) {
            abort(403, 'This action requires the ' . implode(' or ', $roles) . ' role.');
        }
        return $next($request);
    }
}