<div class="space-y-6">
    <section class="surface p-6 sm:p-8">
        <span class="eyebrow">Users</span>
        <h2 class="mt-3 text-2xl font-bold text-stone-950">Registered accounts</h2>
    </section>

    <div class="surface overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                <tr>
                    <th class="px-4 py-3 text-left">Name</th>
                    <th class="px-4 py-3 text-left">Email</th>
                    <th class="px-4 py-3 text-left">Contact number</th>
                    <th class="px-4 py-3 text-left">Role</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">{{ $user->contact_number ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $user->is_admin ? 'Admin' : 'Guest' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-stone-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
