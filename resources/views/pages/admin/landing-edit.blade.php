@extends('layouts.admin')

@section('content')
@php $section = $section ?? null; @endphp

<div class="site-shell">
    <div class="surface p-6 sm:p-8">
        <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="text-sm text-stone-600 hover:underline">← Back to Landing settings</a>
        <h1 class="mt-4 text-3xl font-semibold text-stone-950">Edit: {{ $section['title'] }}</h1>
        <p class="mt-2 text-sm text-stone-600">{{ $section['description'] }}</p>
    </div>

    <div class="mt-6 site-shell">
        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                @include('components.admin.landing-section-form', ['section' => $section])
            </div>
            <aside class="lg:col-span-1">
                <div class="card p-4">
                    <h3 class="font-semibold">Preview</h3>
                    @if(! empty($section['thumbnail']))
                        <img src="{{ $section['thumbnail'] }}" alt="{{ $section['title'] }} preview" class="mt-4 w-full h-40 object-cover rounded-lg" />
                    @else
                        <div class="mt-4 rounded-lg border border-dashed border-stone-200 p-6 text-sm text-stone-500">No image</div>
                    @endif
                    <p class="mt-4 text-sm text-stone-600">{{ $section['preview'] }}</p>
                </div>
            </aside>
        </div>
    </div>
</div>

@endsection
