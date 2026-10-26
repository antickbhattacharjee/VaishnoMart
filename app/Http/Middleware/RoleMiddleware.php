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
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->status === 'suspended') {
            auth()->logout();
            return redirect()->route('login')->withErrors(['email' => 'Your account has been suspended. Please contact platform support.']);
        }

        if (!empty($roles) && !in_array($user->role, $roles, true)) {
            // If super_admin, grant full access
            if ($user->role === 'super_admin') {
                return $next($request);
            }

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized action.'], 403);
            }

            abort(403, 'Unauthorized access to this section.');
        }

        return $next($request);
    }
}
