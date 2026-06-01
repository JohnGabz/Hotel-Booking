@props(['section'])

@php
    $imageUrl = fn (?string $value): string => \App\Support\ImageStorage::url($value, '');
@endphp

<x-modal id="landing-section-modal-{{ $section['id'] }}" :title="'Edit ' . $section['title']" size="max-w-4xl">
    @if (count($section['modal_fields'] ?? $section['fields']) < count($section['fields']))
        <div class="mb-4 rounded-lg border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-600">
            Showing the most-used fields for quick edits. Other section content is preserved when you save.
        </div>
    @endif
    @include('components.admin.landing-section-form', ['section' => $section])
</x-modal>
