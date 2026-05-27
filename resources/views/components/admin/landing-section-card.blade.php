@props(['section'])

<article class="card flex h-full flex-col justify-between gap-5" data-landing-section-card="{{ $section['id'] }}">
    <div class="space-y-3">
        <div class="flex items-start justify-between gap-3">
            <h3 class="text-xl font-semibold text-stone-950">{{ $section['title'] }}</h3>
            <span class="badge-secondary">{{ count($section['fields']) }} fields</span>
        </div>
        <p class="text-sm leading-7 text-stone-600">{{ $section['description'] }}</p>
        <p class="line-clamp-3 text-sm leading-7 text-stone-500" data-landing-section-preview>{{ $section['preview'] }}</p>
    </div>

    <button type="button" class="btn-primary w-full sm:w-auto" data-modal-open="landing-section-modal-{{ $section['id'] }}">
        Edit Section
    </button>
</article>
