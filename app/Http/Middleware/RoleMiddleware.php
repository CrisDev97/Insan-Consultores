<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = strtolower(trim((string) auth()->user()->role));

        if ($userRole === $role) {
            return $next($request);
        }

        if ($userRole === 'administrador') {
            return redirect()->route('admin.dashboard');
        }

        if ($userRole === 'estudiante') {
            return redirect()->route('student.dashboard');
        }

        abort(403);
    }
}