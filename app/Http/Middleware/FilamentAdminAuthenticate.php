<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class FilamentAdminAuthenticate extends BaseAuthenticate
{
    /**
     * Authenticate the request and redirect students to the exam dashboard.
     *
     * @param  Request  $request
     * @param  array<string>  $guards
     */
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        if (! $guard->check()) {
            $this->unauthenticated($request, $guards);

            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        $user = $guard->user();

        // If a student user accesses /admin, seamlessly redirect to the exam dashboard
        if ($user && method_exists($user, 'hasRole') && $user->hasRole('student') && ! $user->hasAnyRole(['admin', 'proctor', 'teacher'])) {
            throw new HttpResponseException(redirect()->route('exam.dashboard'));
        }

        parent::authenticate($request, $guards);
    }
}
