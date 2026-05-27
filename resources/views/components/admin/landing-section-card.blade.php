@props(['section'])

@php
    $statusTone = $section['status']['tone'] ?? 'warning';
    $statusClass = $statusTone === 'success'
        ? 'border-emerald-200 bg-emerald-50 text-emerald-700'
        : 'border-amber-200 bg-amber-50 text-amber-700';
@endphp

<article class="card flex h-full flex-col overflow-hidden p-0" data-landing-section-card="{{ $section['id'] }}" tabindex="0">
    <div class="relative h-36 overflow-hidden bg-stone-100">
        <img src="{{ $section['thumbnail'] }}" alt="{{ $section['title'] }} preview" class="{{ empty($section['thumbnail']) ? 'hidden' : '' }} h-full w-full object-cover" loading="lazy" decoding="async" data-landing-section-thumbnail>
        <div class="{{ empty($section['thumbnail']) ? '' : 'hidden' }} flex h-full items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200 text-stone-400" data-landing-section-thumbnail-empty>
            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.6-4.6a2 2 0 0 1 2.8 0L16 16m-2-2 1.6-1.6a2 2 0 0 1 2.8 0L20 14m-16 6h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Zm4-12h.01"/></svg>
        </div>
        <div class="{{ empty($section['thumbnail']) ? 'hidden' : '' }} absolute inset-0 bg-gradient-to-t from-stone-950/55 to-transparent" data-landing-section-thumbnail-overlay></div>

        <span class="absolute left-4 top-4 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusClass }}" data-landing-section-status>
            {{ $section['status']['label'] ?? 'Needs review' }}
        </span>
    </div>

    <div class="flex flex-1 flex-col justify-between gap-5 p-5">
        <div class="space-y-3">
            <div class="flex items-start justify-between gap-3">
                <h3 class="text-xl font-semibold text-stone-950">{{ $section['title'] }}</h3>
                <span class="badge-secondary">{{ count($section['fields']) }} fields</span>
            </div>
            <p class="text-sm leading-7 text-stone-600">{{ $section['description'] }}</p>
            <p class="line-clamp-3 text-sm leading-7 text-stone-500" data-landing-section-preview>{{ $section['preview'] }}</p>
        </div>

        <div class="flex flex-col gap-3 border-t border-stone-100 pt-4">
            <p class="text-xs uppercase tracking-[0.18em] text-stone-500" data-landing-section-updated>
                {{ $section['updated_label'] ?? 'Using default content' }}
            </p>
            <a href="{{ route('admin.settings.landing.edit', $section['id']) }}" class="btn-primary w-full inline-flex items-center justify-center" aria-label="Edit {{ $section['title'] }} section">
                Edit Section
            </a>
        </div>
    </div>
</article>
