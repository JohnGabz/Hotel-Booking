@props(['id','title'=>'','size'=>'max-w-2xl'])
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-end justify-center bg-stone-950/40 px-0 sm:items-center sm:px-4" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="{{ $id }}-title" tabindex="-1">
    <div class="absolute inset-0 bg-stone-900/40" data-modal-close></div>
    <div class="modal-panel safe-scroll {{ $size }}">
        <div>
            <div class="sticky top-0 z-10 flex items-center justify-between border-b bg-white px-4 py-3">
                <h3 id="{{ $id }}-title" class="text-lg font-semibold">{{ $title }}</h3>
                <button type="button" data-modal-close class="btn-icon border border-stone-200" aria-label="Close {{ $title ?: 'modal' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-4 sm:p-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
