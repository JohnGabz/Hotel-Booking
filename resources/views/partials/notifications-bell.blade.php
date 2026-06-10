<div class="relative">
    <button type="button" id="notif-btn" data-dropdown-toggle="notif-menu" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
        <span id="notif-badge" class="absolute -top-1 -right-1 hidden min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none ring-2 ring-white">0</span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a3 3 0 0 0 6 0"></path>
        </svg>
    </button>

    <div id="notif-menu" class="hidden absolute right-0 mt-2 w-[min(20rem,calc(100vw-2rem))] rounded-xl bg-white border border-stone-200 shadow-lg py-2 z-50">
        <div class="px-4 py-3 border-b text-sm font-semibold flex justify-between items-center">
            <span>Notifications</span>
            <button type="button" id="notif-mark-all" class="text-xs text-brand-primary hover:underline font-semibold hidden">Mark all as read</button>
        </div>
        <div id="notif-list" class="max-h-56 overflow-auto">
            <div class="px-4 py-6 text-sm text-stone-500 text-center">No new notifications</div>
        </div>
        <div id="notif-footer" class="px-4 py-2 border-t text-center text-xs text-stone-500">You're all caught up</div>
    </div>
</div>
