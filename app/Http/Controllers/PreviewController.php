<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Aperçu du code de l'utilisateur (DevLab, cours). Le HTML est servi depuis une route dédiée,
 * isolée par sa propre CSP et par `sandbox` : il ne partage ni origine, ni cookies, ni la
 * politique stricte de l'application (un iframe srcdoc hériterait de cette dernière).
 */
class PreviewController extends Controller
{
    private const MAX_HTML = 600_000;

    private const TTL_SECONDS = 600;

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['html' => ['required', 'string', 'max:'.self::MAX_HTML]]);

        $token = Str::random(40);
        Cache::put($this->key($token), ['user' => $request->user()->id, 'html' => $data['html']], self::TTL_SECONDS);

        return response()->json(['url' => route('preview.show', $token, false)]);
    }

    public function show(Request $request, string $token): Response
    {
        $entry = Cache::get($this->key($token));
        abort_unless(is_array($entry) && $entry['user'] === $request->user()->id, 404);

        return response($entry['html'], 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "sandbox allow-scripts; default-src 'none'; style-src 'unsafe-inline'; script-src 'unsafe-inline'; img-src data: blob: https:; font-src data: https:; connect-src 'none'; object-src 'none'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'",
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function key(string $token): string
    {
        return 'preview:'.preg_replace('/[^A-Za-z0-9]/', '', $token);
    }
}
