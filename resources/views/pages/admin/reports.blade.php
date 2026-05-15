@extends('layouts.admin')

@section('content')
<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow">Reports</span>
                <h1 class="mt-4 text-4xl sm:text-5xl text-stone-950">Insight-driven analytics with calm presentation.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Track revenue, occupancy, and trends with minimal clutter and a clear hierarchy.</p>
            </div>
            <form method="GET" action="{{ route('admin.reports') }}" class="flex items-center gap-3">
                <label class="sr-only" for="report_range">Range</label>
                <select id="report_range" name="range" class="form-input w-40" onchange="this.form.submit()">
                    <option value="7d" @selected(($reportRange ?? '30d') === '7d')>7d</option>
                    <option value="30d" @selected(($reportRange ?? '30d') === '30d')>30d</option>
                    <option value="90d" @selected(($reportRange ?? '30d') === '90d')>90d</option>
                </select>
                <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Overview</a>
            </form>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
        <div class="surface p-6 sm:p-8">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-3xl font-semibold text-stone-950">Revenue trend</h2>
                    <p class="mt-2 text-sm text-stone-600">A simple visual summary for weekly performance.</p>
                </div>
                <span class="badge-primary">₱{{ number_format($totalRevenue ?? 0, 0) }}</span>
            </div>
            <div class="mt-8 grid h-72 grid-cols-7 items-end gap-3 rounded-[1.5rem] bg-stone-50 p-5">
                @foreach (($chartValues ?? [18,24,20,32,29,35,28]) as $value)
                    @php
                        $barClass = $value >= 35 ? 'h-40' : ($value >= 32 ? 'h-36' : ($value >= 28 ? 'h-32' : ($value >= 24 ? 'h-28' : 'h-24')));
                    @endphp
                    <div class="flex h-full flex-col items-center justify-end gap-2">
                        <div class="w-full rounded-t-2xl bg-brand-primary/90 {{ $barClass }}"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex flex-wrap gap-3 text-xs uppercase tracking-[0.18em] text-stone-400">
                @foreach (($chartLabels ?? ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']) as $label)
                    <span>{{ $label }}</span>
                @endforeach
            </div>
        </div>

        <aside class="surface p-6 sm:p-8 space-y-4">
            <h2 class="text-3xl font-semibold text-stone-950">Key metrics</h2>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                <div class="rounded-[1.5rem] bg-stone-50 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Occupancy rate</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-950">{{ $occupancyRate ?? 0 }}%</p>
                </div>
                <div class="rounded-[1.5rem] bg-stone-50 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Confirmed bookings</p>
                    <p class="mt-2 text-3xl font-semibold text-stone-950">{{ number_format($confirmedCount ?? 0) }}</p>
                </div>
                <div class="rounded-[1.5rem] bg-stone-50 p-5 sm:col-span-2 xl:col-span-1">
                    <p class="text-xs uppercase tracking-[0.22em] text-stone-400">Insight</p>
                    <p class="mt-2 text-sm leading-7 text-stone-600">Revenue is strongest midweek, while occupancy stays steady across the weekend. Use this view to plan pricing and availability promotions.</p>
                </div>
            </div>
        </aside>
    </section>
</div>
@endsection
