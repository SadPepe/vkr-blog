<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Заблокированный пользователь теряет доступ к закрытым разделам сразу,
 * даже если сессия ещё жива.
 */
class EnsureNotBlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->isBlocked()) {
            abort(403, 'Аккаунт заблокирован.');
        }

        return $next($request);
    }
}
