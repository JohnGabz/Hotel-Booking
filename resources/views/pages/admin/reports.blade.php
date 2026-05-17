@extends('layouts.admin')

@section('content')
@php
    $filters = $reportFilters;
    $statusOptions = [
        'all' => 'All statuses',
        'confirmed' => 'Confirmed',
        'pending' => 'Pending',
        'failed' => 'Failed',
        'refunded' => 'Refunded',
    ];
    $rangeOptions = [
        'this_month' => 'This month',
        'last_30' => 'Last 30 days',
        'this_year' => 'This year',
        'all_time' => 'All time',
        'custom' => 'Custom range',
    ];
    $sortUrl = function (string $sort) use ($filters) {
        $direction = ($filters['sort'] === $sort && $filters['direction'] === 'desc') ? 'asc' : 'desc';

        return route('admin.reports', array_merge(request()->query(), [
            'sort' => $sort,
            'direction' => $direction,
        ]));
    };
    $exportQuery = request()->query();
    $maxChartValue = max($chartValues ?: [1]);
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 xl:flex-row xl:items-end xl:justify-between">
            <div>
                <span class="eyebrow">Reports</span>
                <h1 class="mt-4 responsive-title lg:text-5xl">Payment transactions</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Review every booking payment, search references, and export filtered transaction reports without exposing sensitive card data.</p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('admin.reports.export', array_merge($exportQuery, ['format' => 'csv'])) }}" class="btn-primary">Export CSV</a>
                <a href="{{ route('admin.reports.export', array_merge($exportQuery, ['format' => 'pdf'])) }}" class="btn-secondary">Export PDF</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="card">
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Transactions</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($transactionSummary['count']) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Confirmed revenue</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">₱{{ number_format($transactionSummary['confirmed'], 0) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Pending amount</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">₱{{ number_format($transactionSummary['pending'], 0) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-[0.22em] text-stone-500">Failed payments</p>
            <p class="mt-3 text-3xl font-semibold text-stone-950">{{ number_format($transactionSummary['failed_count']) }}</p>
        </article>
    </section>

    <section class="surface p-6 sm:p-8">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-stone-950">Filters</h2>
                <p class="mt-2 text-sm text-stone-600">Exports use the same filters shown here, including range, status, method, and search.</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="btn-secondary">Reset filters</a>
        </div>

        <form method="GET" action="{{ route('admin.reports') }}" class="mt-6 grid gap-4 lg:grid-cols-6">
            <input type="hidden" name="sort" value="{{ $filters['sort'] }}">
            <input type="hidden" name="direction" value="{{ $filters['direction'] }}">

            <div class="form-group lg:col-span-2">
                <label class="form-label" for="search">Search</label>
                <input id="search" name="search" class="form-input" value="{{ $filters['search'] }}" placeholder="Reference, guest, booking, room">
            </div>

            <div class="form-group">
                <label class="form-label" for="range">Date range</label>
                <select id="range" name="range" class="form-input">
                    @foreach ($rangeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['range'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="date_from">From</label>
                <input id="date_from" name="date_from" type="date" class="form-input" value="{{ $filters['date_from_value'] }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="date_to">To</label>
                <input id="date_to" name="date_to" type="date" class="form-input" value="{{ $filters['date_to_value'] }}">
            </div>

            <div class="form-group">
                <label class="form-label" for="status">Status</label>
                <select id="status" name="status" class="form-input">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group lg:col-span-2">
                <label class="form-label" for="payment_method">Payment method</label>
                <select id="payment_method" name="payment_method" class="form-input">
                    <option value="all">All methods</option>
                    @foreach ($paymentMethods as $method)
                        <option value="{{ $method }}" @selected($filters['payment_method'] === $method)>{{ strtoupper(str_replace('_', ' ', $method)) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end lg:col-span-4">
                <button type="submit" class="btn-primary w-full sm:w-auto">Apply filters</button>
            </div>
        </form>
    </section>

    <section class="surface p-6 sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-semibold text-stone-950">Transaction list</h2>
                    <p class="mt-2 text-sm text-stone-600">Showing {{ $transactions->count() }} of {{ number_format($transactions->total()) }} records.</p>
                </div>
                <p class="text-xs uppercase tracking-[0.18em] text-stone-500">50 per page</p>
            </div>

            <div class="mt-6 rounded-lg border border-stone-200 bg-white p-3 md:overflow-x-auto md:p-0">
                <table class="mobile-card-table md:min-w-[980px]">
                    <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Transaction</th>
                            <th class="px-5 py-4">Booking</th>
                            <th class="px-5 py-4">Guest</th>
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4"><a href="{{ $sortUrl('amount') }}" class="font-semibold text-stone-600 hover:text-brand-primary">Amount</a></th>
                            <th class="px-5 py-4"><a href="{{ $sortUrl('status') }}" class="font-semibold text-stone-600 hover:text-brand-primary">Status</a></th>
                            <th class="px-5 py-4">Method</th>
                            <th class="px-5 py-4"><a href="{{ $sortUrl('date') }}" class="font-semibold text-stone-600 hover:text-brand-primary">Date</a></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @forelse ($transactions as $booking)
                            @php
                                $status = $booking->report_payment_status;
                                $statusClasses = match ($status) {
                                    'confirmed' => 'bg-emerald-100 text-emerald-700',
                                    'failed' => 'bg-rose-100 text-rose-700',
                                    'refunded' => 'bg-sky-100 text-sky-700',
                                    default => 'bg-amber-100 text-amber-800',
                                };
                            @endphp
                            <tr class="align-top transition hover:bg-stone-50/80">
                                <td class="px-5 py-4 font-semibold text-stone-950" data-label="Transaction">{{ $booking->transaction_id }}</td>
                                <td class="px-5 py-4" data-label="Booking">
                                    <a href="{{ route('admin.bookings', ['room' => $booking->room_id, 'date_from' => $booking->check_in?->toDateString(), 'date_to' => $booking->check_out?->toDateString()]) }}" class="font-semibold text-brand-primary">#{{ $booking->id }}</a>
                                </td>
                                <td class="px-5 py-4 text-stone-700" data-label="Guest">{{ $booking->contact_name ?: $booking->user?->name ?: 'Guest' }}</td>
                                <td class="px-5 py-4 text-stone-700" data-label="Room">{{ $booking->room?->name ?? 'Room' }}</td>
                                <td class="px-5 py-4 font-semibold text-stone-950" data-label="Amount">₱{{ number_format($booking->total, 2) }}</td>
                                <td class="px-5 py-4" data-label="Status">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses }}">{{ ucfirst($status) }}</span>
                                </td>
                                <td class="px-5 py-4 text-stone-600" data-label="Method">{{ strtoupper(str_replace('_', ' ', $booking->payment_method ?: 'n/a')) }}</td>
                                <td class="px-5 py-4 text-stone-600" data-label="Date">{{ optional($booking->transaction_date)->format('M j, Y g:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-10 text-center text-stone-500">No payment transactions match the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $transactions->links() }}
            </div>
        </section>
    </div>
    @endsection
