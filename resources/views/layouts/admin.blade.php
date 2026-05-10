@php
    $brandPrimary = '#FF7551';
    $brandSecondary = '#FF7551';
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

    <div id="mobile-menu" class="fixed inset-0 z-40 hidden md:hidden">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-all duration-300" data-mobile-close></div>
        <aside class="relative z-10 h-full w-80 max-w-[85vw] bg-white shadow-2xl transition-all duration-300">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-6">
                <div class="flex items-center">
                    <div>
                        <p class="font-display text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ config('app.name') }}</p>
                        <p class="text-xs font-medium uppercase tracking-widest text-gray-500">Admin panel</p>
                    </div>
                </div>
                <button data-mobile-close class="btn-icon">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="px-4 py-6 space-y-2">
                <a href="{{ route('admin.dashboard') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.bookings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.bookings') ? 'nav-link-active' : '' }}">Bookings</a>
                <a href="{{ route('admin.rooms') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.rooms') ? 'nav-link-active' : '' }}">Room Performance</a>
                <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">Guests</a>
                <a href="{{ route('admin.amenities') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.amenities') ? 'nav-link-active' : '' }}">Amenities</a>
                <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">Reports</a>
                <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">Messages</a>
                <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">Settings</a>
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
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v6m0 0V9a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v10m0 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v14"></path></svg>
                        Room Performance
                    </a>
                    <a href="{{ route('admin.guests') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.guests') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 0 0-5-3.87M9 20H4v-2a4 4 0 0 1 5-3.87m9-5a4 4 0 1 0-8 0 4 4 0 0 0 8 0Zm-8-1a4 4 0 1 0-8 0 4 4 0 0 0 8 0Z"></path></svg>
                        Guests
                    </a>
                    <a href="{{ route('admin.amenities') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.amenities') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 3.104v5.714a2.409 2.409 0 0 0 2.427 2.409h3.882c1.518.003 2.674 1.186 2.674 2.704v6.228m-16.5-12.75a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0Zm16.5 0a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0ZM9 18.75a.75.75 0 1 1 1.5 0 .75.75 0 0 1-1.5 0Z"></path></svg>
                        Amenities
                    </a>
                    <a href="{{ route('admin.reports') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.reports') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75Zm9.75 0c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-6.75Zm9.75 0c0-.621.504-1.125 1.125-1.125h2.25C23.496 12 24 12.504 24 13.125v6.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125v-6.75ZM3 4.875c0-.621.504-1.125 1.125-1.125h2.25C7.496 3.75 8 4.254 8 4.875v2.25c0 .621-.504 1.125-1.125 1.125h-2.25A1.125 1.125 0 0 1 3 7.125V4.875Zm9.75 0c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.875Zm9.75 0c0-.621.504-1.125 1.125-1.125h2.25C23.496 3.75 24 4.254 24 4.875v2.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.875Z"></path></svg>
                        Reports
                    </a>
                    <a href="{{ route('admin.messages') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.messages') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z"></path></svg>
                        Messages
                    </a>
                    <a href="{{ route('admin.settings') }}" class="nav-link flex items-center gap-3 {{ request()->routeIs('admin.settings') ? 'nav-link-active' : '' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.592c.55 0 1.02.398 1.11.94a6.474 6.474 0 0 0 1.629 1.31c.476.33 1.04.446 1.576.271l.556-.182c.492-.16 1.022.062 1.321.446l1.83 2.332c.3.384.277.991-.06 1.510a6.334 6.334 0 0 0 .916 2.4c.331.483.645 1.035.916 1.4.271.364.586.916 1.11.916.55 0 1.02.398 1.11.94v2.592c0 .55-.398 1.02-.94 1.11a6.474 6.474 0 0 0-1.31 1.629c-.33.476-.446 1.04-.271 1.576l.182.556c.16.492-.062 1.022-.446 1.321l-2.332 1.83c-.384.3-.991.277-1.51-.06a6.334 6.334 0 0 0-2.4.916c-.483.331-1.035.645-1.4.916-.364.271-.916.586-.916 1.11 0 .55-.398 1.02-.94 1.11h-2.592c-.55 0-1.02-.398-1.11-.94a6.474 6.474 0 0 0-1.629-1.31c-.476-.33-1.04-.446-1.576-.271l-.556.182c-.492.16-1.022-.062-1.321-.446l-1.83-2.332c-.3-.384-.277-.991.06-1.51a6.334 6.334 0 0 0-.916-2.4c-.331-.483-.645-1.035-.916-1.4-.271-.364-.586-.916-1.11-.916-.55 0-1.02-.398-1.11-.94V9.594c0-.55.398-1.02.94-1.11a6.474 6.474 0 0 0 1.31-1.629c.33-.476.446-1.04.271-1.576l-.182-.556c-.16-.492.062-1.022.446-1.321l2.332-1.83c.384-.3.991-.277 1.51.06a6.334 6.334 0 0 0 2.4-.916c.483-.331 1.035-.645 1.4-.916.364-.271.916-.586.916-1.11 0-.55.398-1.02.94-1.11h2.592ZM12 15a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z"></path></svg>
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
                <div class="flex items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div class="flex-1">
                        <button id="mobile-menu-btn" class="mb-3 inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2 min-h-[44px] text-sm font-semibold text-gray-700 transition-all duration-300 hover:bg-gray-50 md:hidden">
                            <svg id="menu-open-icon" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                            <svg id="menu-close-icon" class="h-4 w-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Menu
                        </button>
                        <p class="text-sm font-medium text-gray-600">Hello Jack</p>
                        <h1 class="font-display text-3xl font-bold text-gray-900">Staff dashboard</h1>
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
