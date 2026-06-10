<div class="space-y-8">
    <section>
        <span class="eyebrow">Dashboard</span>
        <h2 class="mt-3 text-3xl font-bold text-stone-950">Welcome back</h2>
        <p class="mt-2 text-sm text-stone-600">Monitor performance and manage operations at a glance.</p>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <article class="card">
            <p class="text-xs uppercase tracking-wider text-stone-500">Total bookings</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($bookingsCount ?? 0) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-wider text-stone-500">Revenue</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">₱{{ number_format($totalRevenue ?? 0, 0) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-wider text-stone-500">Available rooms</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($availableRooms ?? 0) }}</p>
        </article>
        <article class="card">
            <p class="text-xs uppercase tracking-wider text-stone-500">Physical units</p>
            <p class="mt-3 text-3xl font-bold text-stone-950">{{ number_format($physicalRoomCount ?? 0) }}</p>
        </article>
    </section>
</div>
