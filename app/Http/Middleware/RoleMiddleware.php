<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Admins have access to all routes, or if user matches any required role
        if ($user->hasRole('admin') || $user->hasAnyRole($roles)) {
            return $next($request);
        }

        // If a student tries to access non-student areas, redirect them to their exam dashboard
        if ($user->hasRole('student')) {
            return redirect()->route('exam.dashboard');
        }

        // If a teacher or proctor tries to access unauthorized areas, redirect them to admin panel
        if ($user->hasAnyRole(['teacher', 'proctor'])) {
            return redirect()->to('/admin');
        }

        abort(403, 'Unauthorized. Required roles: '.implode(', ', $roles));

        return $next($request);
    }
}
