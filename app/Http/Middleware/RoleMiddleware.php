<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect()->route('student.login');
        }

        if (auth()->user()->role !== $role) {
            // Si es admin, lo mandamos al panel admin
            if (auth()->user()->role === 'administrador') {
                return redirect()->route('admin.dashboard');
            }

            abort(403);
        }

        return $next($request);
    }
}
