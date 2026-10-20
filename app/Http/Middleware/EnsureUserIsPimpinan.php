<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya pimpinan atau admin yang boleh lewat.
 * Admin ikut diizinkan supaya bisa mengecek tampilan monitoring.
 */
class EnsureUserIsPimpinan
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->bolehMonitoring(), 403);

        return $next($request);
    }
}
