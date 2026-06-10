@php
    $brandPrimary = '#B6424F';
    $brandSecondary = '#B57D59';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
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

    <div id="mobile-menu" class="fixed inset-0 z-40 hidden md:hidden flex justify-end">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-all duration-300" data-mobile-close></div>
        <aside class="relative z-10 h-full w-80 max-w-[85vw] bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-6">
                <div class="flex items-center">
                    <div>
                        <p class="font-display text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ config('app.name') }}</p>
                        <p class="text-xs font-medium uppercase tracking-widest text-gray-500">Admin panel</p>
                    </div>
                </div>
                <button type="button" data-mobile-close class="btn-icon" aria-label="Close admin menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="px-4 py-6 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.bookings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.bookings') ? 'nav-link-active' : '' }}">Bookings</a>
                <a href="{{ route('admin.rooms') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.rooms') ? 'nav-link-active' : '' }}">Rooms</a>
                <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">Guests</a>
                <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">Reports</a>
                <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">Messages</a>
                <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">Settings</a>
            </nav>
        </aside>
    </div>

    <!-- Global generic modal for Create/Edit/Delete actions -->
    <x-modal id="generic-action-modal" title="Action">
        <form id="generic-action-form" method="POST" action="#">
            @csrf
            <div class="space-y-4">
                <p class="text-sm text-stone-600">Use this form to perform the requested action. Replace with your form fields as needed.</p>
                <div class="grid gap-3">
                    <input type="text" name="title" placeholder="Title" class="form-input" />
                    <textarea name="notes" rows="4" placeholder="Notes" class="form-input"></textarea>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-end gap-3">
                <button type="button" data-modal-close class="btn-secondary">Cancel</button>
                <button type="submit" class="btn-primary">Confirm</button>
            </div>
        </form>
    </x-modal>
    <div class="min-h-screen flex flex-col md:flex-row">
        <aside class="hidden md:block md:w-72 lg:w-80 bg-white border-b md:border-b-0 md:border-r border-stone-200/80 shadow-[0_18px_40px_rgba(80,61,30,0.05)]">
            <div class="flex items-center px-5 py-5 border-b border-stone-200/80">
                <div>
                    <p class="font-display text-lg font-semibold tracking-[0.08em] text-stone-950 sm:text-xl">{{ config('app.name') }}</p>
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Admin panel</p>
                </div>
            </div>

            <nav class="px-4 py-4">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-[0.25em] text-stone-400">Navigation</p>
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM14 14h7v7h-7zM3 14h7v7H3z"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.bookings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.bookings') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M5 11h14M6 21h12a1 1 0 0 0 1-1V6a1 1 0 0 0-1-1H6a1 1 0 0 0-1 1v14a1 1 0 0 0 1 1Z"></path></svg>
                        Bookings
                    </a>
                    <a href="{{ route('admin.rooms') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.rooms') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6m0 0V9a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v10m0 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v14"></path></svg>
                        Rooms
                    </a>
                    <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 0 0-5-3.87M9 20H4v-2a4 4 0 0 1 5-3.87m9-5a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm-8-1a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"></path></svg>
                        Guests
                    </a>
                    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Reports
                    </a>
                    <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"></path></svg>
                        Messages
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Settings
                    </a>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('home') }}" class="btn-secondary w-full justify-center">View site</a>
                </div>
            </nav>

        </aside>

        <div class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur-sm">
                <div class="px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between gap-4">
                        <!-- Left: Hello and Staff Dashboard title -->
                        <div>
                            <p class="text-sm font-medium text-stone-600">Hello {{ Auth::user()?->name ?? 'Jack' }}</p>
                            <h1 class="font-display text-2xl sm:text-3xl font-bold leading-tight text-stone-950">Staff dashboard</h1>
                        </div>

                        <!-- Right: Notifications, Profile, and Mobile Menu -->
                        <div class="flex items-center gap-3 sm:gap-4">
                            @include('partials.notifications-bell')

                            <!-- Profile -->
                            <div class="relative hidden sm:block">
                                <button type="button" data-dropdown-toggle="profile-menu" class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()?->name ?? 'Admin') }}&background=B6424F&color=fff" class="h-7 w-7 rounded-full" alt="Admin profile">
                                    <span class="hidden lg:inline">{{ Auth::user()?->name ?? 'Admin' }}</span>
                                </button>

                                <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white border border-stone-200 shadow-lg py-2">
                                    <a href="{{ route('home') }}" class="block px-4 py-2 text-sm hover:bg-stone-50">View site</a>
                                    <a href="{{ route('admin.settings', ['tab' => 'account']) }}" class="block px-4 py-2 text-sm hover:bg-stone-50">Account</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-stone-50">Sign out</button>
                                    </form>
                                </div>
                            </div>

                            <!-- Mobile Menu Button (right side) -->
                            <button type="button" id="mobile-menu-btn" class="inline-flex min-h-[44px] items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-semibold text-gray-700 shadow-sm transition-all duration-300 hover:bg-gray-50 md:hidden" aria-controls="mobile-menu" aria-expanded="false">
                                <svg id="menu-open-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                <svg id="menu-close-icon" class="h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </header>

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
    @if ($errors->any())
        <script>
            window.addEventListener('DOMContentLoaded', () => {
                window.VillaError?.show(
                    'Some details need attention',
                    'Please fix the highlighted fields and try again.',
                    @json($errors->messages())
                );
            });
        </script>
    @endif
</body>
</html>
