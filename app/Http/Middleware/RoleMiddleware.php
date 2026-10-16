<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda dinonaktifkan. Silakan hubungi admin.']);
        }

        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        \App\Models\AuditLog::log('ACCESS_DENIED', 'Route', null, null, [
            'attempted_url' => $request->path(),
            'method' => $request->method(),
            'user_role' => $user->role,
            'required_roles' => $roles,
        ]);

        abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
