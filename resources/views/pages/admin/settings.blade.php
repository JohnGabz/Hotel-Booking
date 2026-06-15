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
        @if (app()->environment(['local', 'testing']))
            <section class="surface border border-red-200 bg-red-50/40 p-6 sm:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <span class="eyebrow text-red-700">Development reset</span>
                        <h2 class="mt-3 text-2xl font-semibold text-stone-950">Reset dummy data and apply latest schema</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-7 text-stone-600">This runs Laravel's migration refresh with seed data. It is available only in local/testing environments.</p>
                    </div>
                </div>

                <form method="POST" action="/admin/dev-reset" class="mt-5 grid gap-4 lg:grid-cols-[1fr_auto]" onsubmit="return confirm('Reset the database and reseed demo data? This cannot be undone.');">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="confirmation_token">Type RESET-DUMMY-DATA to confirm</label>
                        <input id="confirmation_token" name="confirmation_token" type="text" class="form-input @error('confirmation_token') error @enderror" autocomplete="off" required>
                        @error('confirmation_token') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn-primary bg-red-700 hover:bg-red-800">Reset database</button>
                    </div>
                </form>
            </section>
        @endif
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

        </section>

        @foreach ($landingSections as $section)
            <x-admin.landing-edit-modal :section="$section" />
        @endforeach
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
