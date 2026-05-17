<div id="error-modal" class="fixed inset-0 z-[80] hidden items-end justify-center px-0 sm:items-center sm:px-4" hidden aria-hidden="true" role="alertdialog" aria-modal="true" aria-labelledby="error-modal-title" aria-describedby="error-modal-message" tabindex="-1">
    <div class="absolute inset-0 bg-stone-950/45" data-error-modal-close></div>
    <section class="modal-panel max-w-lg p-5 sm:p-6">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 id="error-modal-title" class="text-lg font-semibold text-stone-950">Something went wrong</h2>
                <p id="error-modal-message" class="mt-2 text-sm leading-6 text-stone-600">Please try again in a moment.</p>
                <ul id="error-modal-list" class="mt-4 hidden list-disc space-y-1 pl-5 text-sm text-red-700"></ul>
            </div>
        </div>
        <div class="mt-6 flex justify-end">
            <button type="button" class="btn-primary" data-error-modal-close>Okay</button>
        </div>
    </section>
</div>
