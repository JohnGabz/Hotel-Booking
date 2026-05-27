@extends('layouts.admin')

@section('content')
@php
    $tab = $activeSettingsTab ?? 'general';
@endphp

<div id="top" class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <span class="eyebrow">Settings</span>
        <h1 class="mt-4 responsive-title lg:text-5xl">Tabbed controls for general, account, preferences, and the landing page.</h1>
        <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Keep admin configuration grouped and easy to scan without overwhelming the page.</p>

        <div class="mt-6 flex flex-wrap gap-3" role="tablist" aria-label="Settings sections">
            <a href="{{ route('admin.settings', ['tab' => 'general']) }}" class="{{ $tab === 'general' ? 'btn-primary' : 'btn-secondary' }}">General</a>
            <a href="{{ route('admin.settings', ['tab' => 'account']) }}" class="{{ $tab === 'account' ? 'btn-primary' : 'btn-secondary' }}">Account</a>
            <a href="{{ route('admin.settings', ['tab' => 'preferences']) }}" class="{{ $tab === 'preferences' ? 'btn-primary' : 'btn-secondary' }}">Preferences</a>
            <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="{{ $tab === 'landing' ? 'btn-primary' : 'btn-secondary' }}">Landing page</a>
        </div>
    </section>

    @if ($tab === 'general')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">General settings</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <div class="form-group xl:col-span-2"><label class="form-label">Hotel name</label><input class="form-input" value="Villa Estella Fine Inn"></div>
                <div class="form-group"><label class="form-label">Primary color</label><input class="form-input" value="#B6424F"></div>
                <div class="form-group"><label class="form-label">Secondary color</label><input class="form-input" value="#B57D59"></div>
            </div>
        </section>
    @elseif ($tab === 'account')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Account settings</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <div class="form-group"><label class="form-label">Name</label><input class="form-input" value="{{ Auth::user()?->name ?? 'Admin' }}"></div>
                <div class="form-group"><label class="form-label">Email</label><input class="form-input" value="{{ Auth::user()?->email ?? '' }}"></div>
                <div class="form-group xl:col-span-2"><label class="form-label">Password</label><input class="form-input" type="password" value="********"></div>
            </div>
        </section>
    @elseif ($tab === 'landing')
        <section class="surface p-6 sm:p-8 space-y-8">
            <div>
                <span class="eyebrow">Landing page</span>
                <h2 class="mt-4 text-3xl sm:text-4xl font-semibold text-stone-950">Edit the homepage sections from one tab.</h2>
                <p class="mt-3 max-w-2xl text-sm leading-7 text-stone-600">Update the words, background images, and contact details that appear on the public landing page.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($landingSections as $section)
                    <x-admin.landing-section-card :section="$section" />
                @endforeach
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <a href="{{ route('home') }}" target="_blank" rel="noreferrer" class="btn-secondary">Preview site</a>
                <a href="#top" class="btn-secondary">Back to top</a>
            </div>

            @foreach ($landingSections as $section)
                <x-admin.landing-edit-modal :section="$section" />
            @endforeach
        </section>
    @else
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Preferences</h2>
            <div class="mt-6 grid gap-4 xl:grid-cols-2">
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Dark mode</span><input type="checkbox"></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Email alerts</span><input type="checkbox" checked></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Mobile summary</span><input type="checkbox" checked></label>
                <label class="flex items-center justify-between rounded-2xl border border-stone-200 bg-white p-4"><span>Auto-approve reviews</span><input type="checkbox"></label>
            </div>
        </section>
    @endif

    @if ($tab !== 'landing')
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Back to dashboard</a>
        </div>
    @endif
</div>
@endsection
