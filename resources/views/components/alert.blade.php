@props([ 'type' => 'success', 'message' => '', 'dismiss' => true ])

@php
$styles = [
    'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
    'error'   => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
    'info'    => 'bg-sky-50 border-sky-200 text-sky-800',
];
@endphp

<div class="flex items-start gap-3 rounded-2xl border px-4 py-4 text-sm {{ $styles[$type] }}">
    <div class="flex-shrink-0 mt-0.5">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z"/></svg>
    </div>
    <div class="flex-1">{{ $message }}</div>
    @if ($dismiss)
        <button type="button" onclick="this.closest('div').style.display='none'" class="flex-shrink-0 text-slate-600 hover:text-slate-900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
