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
    <script>document.documentElement.classList.add('is-loading');</script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-50 font-sans text-stone-900">
    <x-global-loader />

    <div id="mobile-menu" class="fixed inset-0 z-40 hidden md:hidden">
        <div class="absolute inset-0 bg-stone-950/45" data-mobile-close></div>
        <aside class="relative z-10 h-full w-80 max-w-[85vw] bg-white shadow-[0_24px_60px_rgba(0,0,0,0.2)]">
            <div class="flex items-center justify-between border-b border-stone-200/80 px-5 py-5">
                <div class="flex items-center">
                    <div>
                        <p class="font-display text-lg font-semibold tracking-[0.08em] text-stone-950 sm:text-xl">{{ config('app.name') }}</p>
                        <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Admin panel</p>
                    </div>
                </div>
                <button data-mobile-close class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-stone-200 text-stone-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="px-4 py-4">
                <div class="space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                    <a href="{{ route('admin.bookings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.bookings') ? 'nav-link-active' : '' }}">Bookings</a>
                    <a href="{{ route('admin.rooms') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.rooms') ? 'nav-link-active' : '' }}">Room Performance</a>
                    <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">Guests</a>
                    <a href="{{ route('admin.amenities') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.amenities') ? 'nav-link-active' : '' }}">Amenities</a>
                    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">Reports</a>
                    <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">Messages</a>
                    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">Settings</a>
                </div>
            </nav>
        </aside>
    </div>

    <!-- Global generic modal for Create/Edit/Delete actions -->
    <x-modal id="generic-action-modal" title="Action">
        <form id="generic-action-form">
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4zM9 18v-6h6v6"></path></svg>
                        Room Performance
                    </a>
                    <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 0 0-5-3.87M9 20H4v-2a4 4 0 0 1 5-3.87m9-5a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm-8-1a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"></path></svg>
                        Guests
                    </a>
                    <a href="{{ route('admin.amenities') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.amenities') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h16"></path></svg>
                        Amenities
                    </a>
                    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18v10H3zM7 11h3"></path></svg>
                        Reports
                    </a>
                    <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 17.3 6.5 20l1-6.1-4.4-4.3 6.2-1L12 3l2.7 5.6 6.2 1-4.4 4.3 1 6.1-5.5-2.7Z"></path></svg>
                        Messages
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5h16M4 12h16M4 19h16"></path></svg>
                        Settings
                    </a>
                </div>

                <div class="mt-6 px-2">
                    <a href="{{ route('home') }}" class="btn-secondary w-full">View site</a>
                </div>
            </nav>

            <div class="mt-auto hidden md:block border-t border-stone-200/80 p-4">
                <div class="rounded-[1.5rem] bg-stone-50 p-4">
                    <p class="text-xs uppercase tracking-[0.25em] text-stone-500">Quick tips</p>
                    <p class="mt-2 text-sm leading-6 text-stone-700">Use the section links to jump between bookings, rooms, payments, and content.</p>
                </div>
            </div>
        </aside>

        <div class="flex-1 min-w-0">
            <header class="sticky top-0 z-20 border-b border-stone-200/80 bg-white/90 backdrop-blur">
                <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div>
                        <button id="mobile-menu-btn" class="mb-3 inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 md:hidden">
                            <svg id="menu-open-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            <svg id="menu-close-icon" class="h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Menu
                        </button>
                        <p class="text-sm font-semibold text-stone-500">Hello Jack</p>
                        <h1 class="font-display text-3xl font-bold text-stone-950">Staff dashboard</h1>
                    </div>

                    <div class="flex items-center gap-3 sm:gap-4">
                            <!-- Topbar: restored notifications dropdown and profile dropdown; other quick controls removed -->
                            <div class="relative">
                                <button id="notif-btn" data-dropdown-toggle="notif-menu" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary" aria-label="Notifications">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5"></path></svg>
                                </button>

                                <div id="notif-menu" class="hidden absolute right-0 mt-2 w-80 rounded-xl bg-white border border-stone-200 shadow-lg py-2">
                                    <div class="px-4 py-3 border-b text-sm font-semibold">Notifications</div>
                                    <div class="max-h-56 overflow-auto">
                                        <a class="block px-4 py-3 text-sm hover:bg-stone-50">No new notifications</a>
                                    </div>
                                    <div class="px-4 py-2 border-t text-center text-xs text-stone-500">You’re all caught up</div>
                                </div>
                            </div>
                        <div class="relative">
                            <button data-dropdown-toggle="profile-menu" class="hidden sm:inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()?->name ?? 'Admin') }}&background=B6424F&color=fff" class="h-7 w-7 rounded-full" alt="Admin profile">
                                <span class="hidden lg:inline">{{ Auth::user()?->name ?? 'Admin' }}</span>
                            </button>

                            <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white border border-stone-200 shadow-lg py-2">
                                <a href="{{ route('home') }}" class="block px-4 py-2 text-sm hover:bg-stone-50">View site</a>
                                <a href="#" class="block px-4 py-2 text-sm hover:bg-stone-50">Account</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-stone-50">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
