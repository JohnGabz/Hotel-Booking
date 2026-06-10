@php
    $brandPrimary = '#B6424F';
    $brandSecondary = '#B57D59';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $seo['title'] ?? config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Satoshi:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>document.documentElement.classList.add('is-loading');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 font-sans text-gray-900">
    <x-global-loader />
    <x-error-modal />

    <div class="min-h-screen flex flex-col md:flex-row">
        <aside class="hidden md:block md:w-72 lg:w-80 bg-white border-b md:border-b-0 md:border-r border-stone-200/80">
            <div class="flex items-center px-5 py-5 border-b border-stone-200/80">
                <div>
                    <p class="font-display text-lg font-semibold tracking-[0.08em] text-stone-950 sm:text-xl">{{ config('app.name') }}</p>
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-500">{{ $sidebarLabel ?? 'Admin panel' }}</p>
                </div>
            </div>

            <nav class="px-4 py-4 space-y-1">
                @yield('sidebar-nav')
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('home') }}" class="btn-secondary w-full justify-center">View site</a>
                </div>
            </nav>
        </aside>

        <div class="flex-1 min-w-0">
            @include('partials.dashboard-header')

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @if (session('success') || session('error') || session('warning') || $errors->any())
                    <div class="mb-6 space-y-3">
                        @if (session('success'))
                            <x-alert type="success" :message="session('success')" />
                        @endif
                        @if (session('error'))
                            <x-alert type="error" :message="session('error')" />
                        @endif
                        @if (session('warning'))
                            <x-alert type="warning" :message="session('warning')" />
                        @endif
                        @if ($errors->any() && ! session('error'))
                            <x-alert type="error" message="Please fix the highlighted fields and try again." />
                        @endif
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    @include('partials.image-input-toggle-script')
    @stack('scripts')
</body>
</html>
