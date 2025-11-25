<?php

namespace App\Core\Http\Middleware;

use App\Core\Exceptions\UnauthenticatedException;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class AuthenticateApi extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \App\Core\Exceptions\UnauthenticatedException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Determine if the user is logged in to any of the given guards.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @return void
     *
     * @throws \App\Core\Exceptions\UnauthenticatedException
     */
    protected function authenticate($request, array $guards)
    {
        if (empty($guards)) {
            $guards = [null];
        }

        foreach ($guards as $guard) {
            if ($this->auth->guard($guard)->check()) {
                $this->auth->shouldUse($guard);
                return;
            }
        }

        // For API requests, throw custom exception
        if ($request->is('api/*') || $request->expectsJson()) {
            throw UnauthenticatedException::tokenMissing();
        }

        // For web requests, redirect to login
        $this->unauthenticated($request, $guards);
    }
}
