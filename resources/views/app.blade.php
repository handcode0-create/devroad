<!DOCTYPE html>
@php
    $uiTheme = auth()->user()?->theme;
    $uiTheme = in_array($uiTheme, \App\Models\User::THEMES, true) ? $uiTheme : 'nuit';
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-ui-theme="{{ $uiTheme === 'auto' ? 'nuit' : $uiTheme }}" data-ui-theme-pref="{{ $uiTheme }}" data-theme="{{ in_array($uiTheme, \App\Models\User::LIGHT_THEMES, true) ? 'light' : 'dark' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- Current Laravel CSRF token for AJAX clients. --}}
        <meta name="theme-color" content="#08111F">
        <script>
            // Thème « Automatique » et « Réduire les animations », résolus avant l'affichage (pas de flash).
            (function () {
                var root = document.documentElement;
                if (root.dataset.uiThemePref === 'auto' && window.matchMedia('(prefers-color-scheme: light)').matches) {
                    root.dataset.uiTheme = 'clair';
                    root.dataset.theme = 'light';
                }
                try { if (localStorage.getItem('devroad:motion') === 'reduce') root.dataset.motion = 'reduce'; } catch (e) {}
                var bg = { nuit: '#08111F', minuit: '#000000', ardoise: '#1A1F27', clair: '#F4F6F9', sable: '#F3EEE5' }[root.dataset.uiTheme];
                if (bg) document.querySelector('meta[name="theme-color"]').setAttribute('content', bg);
            })();
        </script>
        <meta name="application-name" content="{{ config('app.name', 'DevRoad') }}">
        <meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'DevRoad') }}">
        <meta name="description" content="DevRoad — apprenez, planifiez et construisez vos projets de développement.">
        <link rel="icon" type="image/png" href="/icondevroad.png">
        <link rel="apple-touch-icon" href="/icondevroad.png">
        <link rel="manifest" href="/manifest.webmanifest">

        <title inertia>{{ config('app.name', 'DevRoad') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|manrope:800&display=swap" rel="stylesheet" />
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Lora:wght@400;500;600;700&family=Manrope:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @viteReactRefresh
        @vite('resources/js/app.jsx')
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
