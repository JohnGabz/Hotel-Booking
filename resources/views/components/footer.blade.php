<footer class="mt-8 border-t border-stone-200/70 bg-stone-950 text-stone-300">
    <div class="site-shell py-14 sm:py-16">
        <div class="grid gap-10 lg:grid-cols-[1.4fr_0.8fr_0.8fr]">
            <div>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 text-white">
                    <span class="flex flex-col leading-none">
                        <span class="text-lg font-semibold tracking-[0.18em] uppercase">{{ config('app.name') }}</span>
                        <span class="text-xs font-medium text-stone-400">Luxury hospitality with a calm, modern edge.</span>
                    </span>
                </a>
                <p class="mt-5 max-w-lg text-sm leading-7 text-stone-400">
                    {{ config('seo.tagline') }} Book elegant rooms, manage guest stays, and keep every reservation flow intuitive from discovery to checkout.
                </p>
                <div class="mt-6 flex flex-wrap gap-3">
                    @foreach (config('nav.social', []) as $social)
                        <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-stone-800 bg-stone-900/80 text-stone-300 transition hover:border-amber-400 hover:text-white" aria-label="{{ $social['label'] }}">
                            {!! $social['icon'] !!}
                        </a>
                    @endforeach
                </div>
            </div>

            @foreach (config('nav.footer', []) as $section)
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-[0.3em] text-stone-400">{{ $section['title'] }}</h3>
                    <ul class="mt-5 space-y-3">
                        @foreach ($section['links'] as $link)
                            <li>
                                <a href="{{ $link['url'] }}" class="text-sm text-stone-300 transition hover:text-white">{{ $link['label'] }}</a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="mt-12 flex flex-col gap-4 border-t border-stone-800 pt-6 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs text-stone-500">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <div class="flex flex-wrap gap-4 text-xs text-stone-500">
                <a href="/privacy" class="transition hover:text-white">Privacy Policy</a>
                <a href="/terms" class="transition hover:text-white">Terms of Service</a>
            </div>
        </div>
    </div>
</footer>
