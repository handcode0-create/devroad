<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité HTTP appliqués à toutes les réponses web.
 *
 * La CSP est désactivée en environnement local : le serveur Vite (HMR) charge des scripts depuis
 * un autre port. Les scripts inline restent autorisés car l'aperçu DevLab (iframe srcdoc) hérite
 * de la politique de la page ; cet iframe est de toute façon isolé (sandbox + CSP propre).
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        if (! app()->environment('local', 'testing')) {
            $headers['Content-Security-Policy'] = $this->csp($request->isSecure());
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function csp(bool $secure): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: blob: https:",
            "connect-src 'self'",
            "frame-src 'self' blob: data:",
            "worker-src 'self' blob:",
            "manifest-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        if ($secure) {
            $directives[] = 'upgrade-insecure-requests';
        }

        return implode('; ', $directives);
    }
}
