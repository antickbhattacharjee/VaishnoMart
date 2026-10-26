<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and attach enterprise security headers.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // 1. Prevent Clickjacking / UI Redressing attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // 2. Prevent MIME-type sniffing confusion attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // 3. Legacy XSS filter enforcement for older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // 4. Protect sensitive URL query strings from leaking across origins
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // 5. Restrict device capabilities and sensor access
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(self)');

        // 6. Content Security Policy allowing required application CDNs (Bootstrap, Google Fonts, Icons)
        $csp = "default-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.gstatic.com data: blob:;";
        $response->headers->set('Content-Security-Policy', $csp);

        return $response;
    }
}
