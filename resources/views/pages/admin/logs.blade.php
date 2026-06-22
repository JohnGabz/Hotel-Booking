@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <span class="eyebrow">Audit trail</span>
            <h1 class="mt-2 text-2xl font-bold text-stone-950">System logs</h1>
            <p class="mt-1 text-sm text-stone-600">Track booking, payment, room, and admin activity across the system.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.logs') }}" class="surface grid gap-4 p-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="form-group lg:col-span-2">
            <label class="form-label" for="log-search">Search</label>
            <input id="log-search" name="search" value="{{ $filters['search'] }}" class="form-input" placeholder="Search details, action, or user">
        </div>
        <div class="form-group">
            <label class="form-label" for="log-module">Module</label>
            <select id="log-module" name="module" class="form-input">
                <option value="">All modules</option>
                @foreach ($moduleOptions as $moduleOption)
                    <option value="{{ $moduleOption }}" @selected($filters['module'] === $moduleOption)>{{ ucfirst($moduleOption) }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label class="form-label" for="log-action">Action</label>
            <select id="log-action" name="action" class="form-input">
                <option value="">All actions</option>
                @foreach ($actionOptions as $actionOption)
                    <option value="{{ $actionOption }}" @selected($filters['action'] === $actionOption)>{{ $actionOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4">
            <button type="submit" class="btn-primary">Apply filters</button>
            <a href="{{ route('admin.logs') }}" class="btn-secondary">Reset</a>
        </div>
    </form>

    <div class="surface overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Timestamp</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Module</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-stone-500">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 bg-white">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-stone-50/70">
                            <td class="px-4 py-3 whitespace-nowrap text-stone-600">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-stone-800">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex rounded-full bg-stone-100 px-2 py-0.5 text-xs font-semibold text-stone-700">{{ ucfirst($log->module) }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap font-medium text-stone-900">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-stone-600">{{ $log->details }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-stone-500">No log entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-stone-200 px-4 py-3">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
