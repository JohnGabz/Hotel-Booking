@props(['section'])

@php
    $imageUrl = fn (?string $value): string => \App\Support\ImageStorage::url($value, '');
@endphp

<x-modal id="landing-section-modal-{{ $section['id'] }}" :title="'Edit ' . $section['title']" size="max-w-4xl">
    @include('components.admin.landing-section-form', ['section' => $section])
</x-modal>
