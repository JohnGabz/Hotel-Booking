<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    <meta name="description" content="{{ $seo['description'] ?? config('seo.default_description') }}">
    <meta name="keywords" content="{{ $seo['keywords'] ?? config('seo.default_keywords') }}">
    <link rel="canonical" href="{{ $seo['canonical'] ?? url()->current() }}">

    <meta property="og:title" content="{{ $seo['og_title'] ?? $seo['title'] ?? config('app.name') }}">
    <meta property="og:description" content="{{ $seo['og_description'] ?? $seo['description'] ?? config('seo.default_description') }}">
    <meta property="og:image" content="{{ $seo['og_image'] ?? asset('images/og-villa-estella.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700|cormorant-garamond:500,600,700&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('is-loading');</script>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @stack('head')
</head>
<body class="relative min-h-screen overflow-x-hidden bg-stone-50 text-stone-900 selection:bg-amber-500 selection:text-white">
    <div class="pointer-events-none fixed inset-0 -z-10 bg-[radial-gradient(circle_at_top_left,_rgba(255,255,255,0.92),_transparent_34%),radial-gradient(circle_at_85%_15%,rgba(236,196,127,0.28),transparent_22%),linear-gradient(180deg,#fbf7f1_0%,#f4ede1_44%,#efe6d8_100%)]"></div>

    <x-global-loader />

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-stone-950 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to content
    </a>

    @include('components.navbar')

    @if (session('success') || session('error') || session('warning'))
        <div class="site-shell pt-4">
            @if (session('success'))
                <x-alert type="success" :message="session('success')" />
            @endif
            @if (session('error'))
                <x-alert type="error" :message="session('error')" />
            @endif
            @if (session('warning'))
                <x-alert type="warning" :message="session('warning')" />
            @endif
        </div>
    @endif

    <main id="main-content">
        @yield('content')
    </main>

    @include('components.footer')
    @stack('scripts')
</body>
</html>
