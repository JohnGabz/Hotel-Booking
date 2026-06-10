@php
    $resolveRoomImage = fn (?string $image) => \App\Support\ImageStorage::url($image, '');
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <span class="eyebrow">Rooms</span>
                <h2 class="mt-3 text-2xl font-bold text-stone-950">Room types and physical inventory</h2>
                <p class="mt-2 text-sm text-stone-600">Manage room types guests book and the physical units that drive availability.</p>
            </div>
            <button type="button" id="open-room-type-modal" class="btn-primary">Add Room Type</button>
        </div>
    </section>

    <div id="room-type-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-stone-950/50 p-4">
        <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-2xl bg-white p-6 shadow-xl">
            <div class="flex items-center justify-between gap-4">
                <h3 class="text-xl font-semibold text-stone-950">Add Room Type</h3>
                <button type="button" id="close-room-type-modal" class="text-stone-500 hover:text-stone-900">&times;</button>
            </div>

            <form method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="modal_name" class="block text-sm font-medium text-gray-700">Name</label>
                        <input type="text" id="modal_name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="modal_capacity" class="block text-sm font-medium text-gray-700">Max guests</label>
                        <input type="number" id="modal_capacity" name="capacity" min="1" max="20" value="{{ old('capacity', 2) }}" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                        @error('capacity') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="modal_price" class="block text-sm font-medium text-gray-700">Price</label>
                        <input type="number" id="modal_price" name="price" min="0" step="0.01" value="{{ old('price') }}" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                        @error('price') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="modal_status" class="block text-sm font-medium text-gray-700">Availability</label>
                        <select id="modal_status" name="status" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="available" @selected(old('status', 'available') === 'available')>Available</option>
                            <option value="maintenance" @selected(old('status') === 'maintenance')>Maintenance</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label for="modal_description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="modal_description" name="description" rows="4" required class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
                        @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="modal_amenities" class="block text-sm font-medium text-gray-700">Amenities (comma-separated)</label>
                        <input type="text" id="modal_amenities" name="amenities" value="{{ old('amenities') }}" placeholder="Wi-Fi, Breakfast" class="mt-1 block w-full border border-gray-300 rounded-lg px-3 py-2">
                    </div>
                    <div class="md:col-span-2">
                        @include('partials.image-input-toggle', [
                            'prefix' => 'modal_image',
                            'fileId' => 'modal_image',
                            'urlId' => 'modal_image_url',
                            'multiple' => true,
                            'label' => 'Room images',
                        ])
                    </div>
                    <div class="md:col-span-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-medium text-gray-700">Physical Room Inventory</label>
                            <button type="button" id="add-physical-unit-row" class="btn-secondary text-xs py-1.5 px-3">+ Add Unit</button>
                        </div>
                        <div id="physical-units-container" class="mt-3 space-y-3"></div>
                        @error('physical_rooms') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('physical_rooms.*.name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        @error('physical_rooms.*.code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" id="cancel-room-type-modal" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary">Create room type</button>
                </div>
            </form>
        </div>
    </div>

    <section class="grid gap-6 lg:grid-cols-2">
        @foreach ($rooms as $room)
            <article class="surface p-6">
                @php $roomImage = $resolveRoomImage(collect($room->images ?? [])->first()); @endphp
                @if ($roomImage)
                    <img src="{{ $roomImage }}" alt="{{ $room->type_label }}" class="mb-4 h-40 w-full rounded-xl object-cover">
                @endif
                <h3 class="text-xl font-semibold text-stone-950">{{ $room->name }}</h3>
                <p class="mt-1 text-sm text-stone-500">{{ $room->type_label }} · ₱{{ number_format($room->price, 0) }} · {{ $room->capacity }} guests</p>
                <p class="mt-3 text-sm text-stone-600">{{ Str::limit($room->description, 120) }}</p>

                <div class="mt-4 space-y-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-400">Physical rooms ({{ $room->physical_rooms_count }})</p>
                    @forelse ($room->physicalRooms as $physicalRoom)
                        <div class="flex items-center justify-between rounded-lg border border-stone-200 px-3 py-2 text-sm">
                            <span>{{ $physicalRoom->name }} <span class="text-stone-400">({{ $physicalRoom->code }})</span></span>
                            <form method="POST" action="{{ route('physical-rooms.destroy', $physicalRoom) }}" onsubmit="return confirm('Remove this physical room?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-red-600 hover:underline">Remove</button>
                            </form>
                        </div>
                    @empty
                        <p class="text-sm text-stone-500">No physical units yet.</p>
                    @endforelse
                </div>

                <form method="POST" action="{{ route('physical-rooms.store', $room) }}" class="mt-4 grid gap-2 sm:grid-cols-3">
                    @csrf
                    <input type="text" name="name" placeholder="Unit name" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <input type="text" name="code" placeholder="Unit code" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    <div class="flex items-center gap-2">
                        <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="is_available" value="1" checked> Available</label>
                        <button type="submit" class="btn-secondary text-xs py-2 px-3">Add Physical Room</button>
                    </div>
                </form>
            </article>
        @endforeach
    </section>
</div>

<script>
    window.initRoomTypeModal = function (root) {
        const scope = root || document;
        const modal = scope.querySelector('#room-type-modal');
        const openBtn = scope.querySelector('#open-room-type-modal');
        const closeBtn = scope.querySelector('#close-room-type-modal');
        const cancelBtn = scope.querySelector('#cancel-room-type-modal');
        const container = scope.querySelector('#physical-units-container');
        const addRowBtn = scope.querySelector('#add-physical-unit-row');

        if (!modal || modal.getAttribute('data-modal-ready') === '1') {
            return;
        }
        modal.setAttribute('data-modal-ready', '1');

        let unitIndex = 0;

        const addUnitRow = () => {
            const row = document.createElement('div');
            row.className = 'grid gap-2 sm:grid-cols-4 items-end border border-gray-200 rounded-lg p-3';
            row.innerHTML = `
                <div>
                    <label class="text-xs text-gray-600">Name</label>
                    <input type="text" name="physical_rooms[${unitIndex}][name]" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="text-xs text-gray-600">Code</label>
                    <input type="text" name="physical_rooms[${unitIndex}][code]" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                </div>
                <div class="flex items-center gap-2 pt-5">
                    <label class="flex items-center gap-1 text-sm"><input type="checkbox" name="physical_rooms[${unitIndex}][is_available]" value="1" checked> Available</label>
                </div>
                <div class="pt-5 text-right">
                    <button type="button" class="remove-unit-row text-xs text-red-600 hover:underline">Remove</button>
                </div>
            `;
            row.querySelector('.remove-unit-row').addEventListener('click', () => row.remove());
            container.appendChild(row);
            unitIndex++;
        };

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            if (typeof window.initImageInputToggles === 'function') {
                window.initImageInputToggles(modal);
            }
        };

        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openBtn?.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
        addRowBtn?.addEventListener('click', addUnitRow);

        @if (old('name'))
            openModal();
            addUnitRow();
        @endif
    };

    window.initRoomTypeModal();
</script>
