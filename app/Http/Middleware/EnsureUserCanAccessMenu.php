<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian: ->middleware('menu:surat_tugas') atau ->middleware('menu:surat_tugas,tugas_saya')
 * Dengan beberapa key, user lolos kalau punya salah satunya.
 */
class EnsureUserCanAccessMenu
{
    public function handle(Request $request, Closure $next, string ...$keys): Response
    {
        $user = $request->user();

        abort_unless(
            $user && collect($keys)->contains(fn (string $key) => $user->bolehMenu($key)),
            403
        );

        return $next($request);
    }
}
