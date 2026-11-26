<?php

namespace App\Http\Middleware;

use App\Support\Access;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Usage: ->middleware('role:hod,koor') */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(Access::is(...$roles), 403);

        return $next($request);
    }
}
