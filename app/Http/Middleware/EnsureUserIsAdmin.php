<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Необходима авторизация');
        }

        if (auth()->user()->role !== 'admin') {
            abort(403, 'Доступ запрещён. Требуется роль администратора.');
        }

        return $next($request);
    }
}
