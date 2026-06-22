<div class="relative">
    <button type="button" id="notif-btn" data-dropdown-toggle="notif-menu" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-stone-200 bg-white text-stone-700 shadow-sm transition hover:border-brand-primary hover:text-brand-primary focus:outline-none focus:ring-2 focus:ring-brand-primary focus:ring-offset-2" aria-label="Notifications" aria-haspopup="true" aria-expanded="false">
        <span id="notif-badge" class="absolute -top-1 -right-1 hidden min-w-[1.25rem] h-5 px-1.5 flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-bold leading-none ring-2 ring-white">0</span>
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2a2 2 0 0 1-.6 1.4L4 17h5"></path>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a3 3 0 0 0 6 0"></path>
        </svg>
    </button>

    <div id="notif-menu" class="hidden absolute right-0 mt-2 w-[min(24rem,calc(100vw-2rem))] rounded-xl bg-white border border-stone-200 shadow-xl py-2 z-50 overflow-hidden transform origin-top-right transition-all">
        <div class="px-3 py-2 border-b border-stone-100 text-xs font-semibold text-stone-900 flex justify-between items-center bg-stone-50/50">
            <span>Notifications</span>
            <button type="button" id="notif-mark-all" class="text-[11px] text-brand-primary hover:text-brand-secondary hover:underline font-semibold hidden">Mark all as read</button>
        </div>
        <div id="notif-list" class="max-h-[22rem] overflow-y-auto divide-y divide-stone-100">
            <div class="px-3 py-5 text-xs text-stone-500 text-center">No new notifications</div>
        </div>
        <div id="notif-footer" class="px-3 py-2 border-t border-stone-100 text-center text-[11px] text-stone-500 font-medium bg-stone-50/30">You're all caught up</div>
    </div>
</div>
