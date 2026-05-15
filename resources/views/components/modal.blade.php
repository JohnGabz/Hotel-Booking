@props(['id','title'=>'','size'=>'max-w-2xl'])
<div id="{{ $id }}" class="fixed inset-0 z-50 hidden items-center justify-center px-4" aria-hidden="true">
    <div class="absolute inset-0 bg-stone-900/40" data-modal-close></div>
    <div class="relative w-full {{ $size }} max-h-[90vh] overflow-auto">
        <div class="rounded-xl bg-white shadow-lg">
            <div class="flex items-center justify-between border-b px-4 py-3">
                <h3 class="text-lg font-semibold">{{ $title }}</h3>
                <button type="button" data-modal-close class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-stone-200 text-stone-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="p-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
