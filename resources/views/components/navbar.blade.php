<header class="sticky top-0 z-50 border-b border-stone-200/70 bg-white/82 backdrop-blur-xl">
    <nav class="site-shell">
        <div class="flex h-20 items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 text-stone-900 transition hover:text-brand-primary">
                <span class="inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-primary text-sm font-semibold tracking-[0.25em] text-white shadow-lg shadow-brand-primary/20">VE</span>
                <span class="flex flex-col leading-none">
                    <span class="text-lg font-semibold tracking-[0.18em] uppercase">{{ config('app.name') }}</span>
                    <span class="text-xs font-medium text-stone-500">Fine Inn</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 lg:flex">
                @foreach (config('nav.main', []) as $item)
                    <a href="{{ $item['url'] }}" class="nav-link">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="hidden items-center gap-3 lg:flex">
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="btn-secondary px-4 py-2 text-sm">Admin</a>
                    @endif
                    <a href="{{ auth()->user()->hasVerifiedEmail() ? route('dashboard') : route('verification.notice') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-950">My Reservations</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary px-4 py-2 text-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-stone-600 transition hover:text-stone-950">Login</a>
                    <a href="{{ route('register') }}" class="btn-secondary px-4 py-2 text-sm">Register</a>
                @endauth
                <a href="{{ route('rooms.index') }}" class="bg-brand-primary hover:bg-brand-secondary text-white px-5 py-2.5 text-sm rounded-md font-semibold transition-colors">Book Now</a>
            </div>

            <button id="mobile-menu-btn" class="inline-flex items-center justify-center rounded-full border border-stone-200 bg-white p-3 text-stone-700 shadow-sm transition hover:border-stone-300 hover:text-stone-950 lg:hidden" aria-label="Toggle menu">
                <svg id="menu-open-icon" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg id="menu-close-icon" class="hidden h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-stone-200/70 pb-5 lg:hidden">
            <div class="grid gap-1 py-4">
                @foreach (config('nav.main', []) as $item)
                    <a href="{{ $item['url'] }}" class="rounded-2xl px-4 py-3 text-sm font-medium text-stone-700 transition hover:bg-stone-100 hover:text-stone-950">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
            <div class="flex flex-col gap-2 border-t border-stone-200 pt-4">
                @auth
                    @if (auth()->user()->is_admin)
                        <a href="{{ route('admin.dashboard') }}" class="btn-secondary text-center text-sm">Admin Dashboard</a>
                    @endif
                    <a href="{{ auth()->user()->hasVerifiedEmail() ? route('dashboard') : route('verification.notice') }}" class="btn-secondary text-center text-sm">My Reservations</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary w-full text-sm">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn-secondary text-center text-sm">Login</a>
                    <a href="{{ route('register') }}" class="btn-secondary text-center text-sm">Register</a>
                @endauth
                <a href="{{ route('rooms.index') }}" class="bg-brand-primary hover:bg-brand-secondary text-white text-center text-sm rounded-md font-semibold py-2.5 transition-colors">Book Now</a>
            </div>
        </div>
    </nav>
</header>
