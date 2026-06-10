<header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur-sm">
    <div class="px-4 py-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-sm font-medium text-stone-600">Hello {{ Auth::user()?->name ?? 'Admin' }}</p>
                <h1 class="font-display text-2xl sm:text-3xl font-bold leading-tight text-stone-950">{{ $dashboardTitle ?? 'Staff dashboard' }}</h1>
            </div>

            <div class="flex items-center gap-3 sm:gap-4">
                @include('partials.notifications-bell')

                <div class="relative hidden sm:block">
                    <button type="button" data-dropdown-toggle="profile-menu" class="inline-flex items-center gap-2 rounded-full border border-stone-200 bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()?->name ?? 'Admin') }}&background=B6424F&color=fff" class="h-7 w-7 rounded-full" alt="Profile">
                        <span class="hidden lg:inline">{{ Auth::user()?->name ?? 'Admin' }}</span>
                    </button>

                    <div id="profile-menu" class="hidden absolute right-0 mt-2 w-48 rounded-xl bg-white border border-stone-200 shadow-lg py-2 z-50">
                        <a href="{{ route('home') }}" class="block px-4 py-2 text-sm hover:bg-stone-50">View site</a>
                        <a href="{{ route('admin.settings', ['tab' => 'account']) }}" class="block px-4 py-2 text-sm hover:bg-stone-50">Account</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-stone-50">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
