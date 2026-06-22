@extends('layouts.admin')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80',
    ];
    $resolveRoomImage = fn (?string $image) => \App\Support\ImageStorage::url($image, '');
    $uploadUrlBase = rtrim(\Illuminate\Support\Facades\Storage::disk(config('filesystems.uploads_disk'))->url(''), '/');
    $viewMode = $viewMode ?? 'grid';
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow">Rooms</span>
                <h1 class="mt-4 responsive-title lg:text-5xl">Room types and physical room inventory.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Manage the public room types guests book, plus the individual physical rooms that determine real availability.</p>
            </div>
            <a href="#" data-modal-open="add-room-modal" data-modal-title="Add room type" class="btn-primary">Add room type</a>
        </div>

        <div class="mt-6 flex flex-wrap gap-3">
            <a href="{{ route('admin.rooms', ['view' => 'grid']) }}" class="{{ $viewMode === 'grid' ? 'btn-primary' : 'btn-secondary' }}">Grid view</a>
            <a href="{{ route('admin.rooms', ['view' => 'table']) }}" class="{{ $viewMode === 'table' ? 'btn-primary' : 'btn-secondary' }}">Table view</a>
        </div>
    </section>

    @if ($viewMode === 'table')
        <section class="surface p-6 sm:p-8">
            <h2 class="text-3xl font-semibold text-stone-950">Room table</h2>
            <div class="mt-6 rounded-lg border border-stone-200 bg-white p-3 md:overflow-x-auto md:p-0">
                <table class="mobile-card-table md:min-w-full">
                    <thead class="bg-stone-50 text-xs uppercase tracking-[0.18em] text-stone-500">
                        <tr>
                            <th class="px-5 py-4">Room type</th>
                            <th class="px-5 py-4">Physical rooms</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Capacity</th>
                            <th class="px-5 py-4">Rate</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($rooms as $room)
                            <tr class="transition hover:bg-stone-50/80">
                                <td class="px-5 py-4 font-semibold text-stone-950" data-label="Room type">{{ $room->name }}</td>
                                <td class="px-5 py-4 text-stone-600" data-label="Physical rooms">
                                    <div class="flex flex-col gap-1">
                                        <span>{{ $room->physical_rooms_count }} {{ Str::plural('room', $room->physical_rooms_count) }}</span>
                                        <span class="text-xs text-stone-500">{{ $room->physicalRooms->where('status', 'available')->count() }} available</span>
                                    </div>
                                </td>
                                <td class="px-5 py-4" data-label="Status">
                                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $room->status === 'available' ? 'bg-emerald-100 text-emerald-700' : ($room->status === 'maintenance' ? 'bg-amber-200 text-amber-950 ring-1 ring-amber-400' : 'bg-amber-100 text-amber-800') }}">
                                        {{ $room->status === 'maintenance' ? 'Maintenance' : ucfirst($room->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-stone-600" data-label="Capacity">{{ $room->capacity }}</td>
                                <td class="px-5 py-4 text-stone-600" data-label="Rate">₱{{ number_format($room->price, 0) }}</td>
                                <td class="px-5 py-4 table-actions" data-label="Actions">
                                    <div class="flex flex-wrap justify-end gap-2 md:justify-start">
                                        <a href="{{ route('rooms.show', $room->slug) }}" class="btn-secondary text-sm">View</a>
                                        <a href="#"
                                           data-modal-open="edit-room-modal"
                                           data-modal-title="Edit room"
                                           data-room-id="{{ $room->id }}"
                                           data-room-name="{{ e($room->name) }}"
                                           data-room-description="{{ e($room->description) }}"
                                           data-room-capacity="{{ $room->capacity }}"
                                           data-room-price="{{ $room->price }}"
                                           data-room-status="{{ $room->status }}"
                                           data-room-amenities='@json($room->amenities ?? [])'
                                           data-room-images='@json($room->images ?? [])'
                                           data-room-physical-rooms='@json($room->physicalRooms->map(fn ($physicalRoom) => ['id' => $physicalRoom->id, 'name' => $physicalRoom->name, 'status' => $physicalRoom->status])->values())'
                                           data-room-update-url="{{ route('admin.rooms.update', $room) }}"
                                           class="btn-secondary text-sm btn-edit">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="grid gap-6 lg:grid-cols-3">
            @forelse ($rooms as $room)
                <article class="surface overflow-hidden">
                    @php
                        $roomImage = collect($room->images ?? [])->first();
                        $roomImage = $resolveRoomImage($roomImage) ?? $roomImages[$loop->index % count($roomImages)];
                    @endphp
                    <div class="h-56 overflow-hidden bg-stone-200">
                        <img src="{{ $roomImage }}" alt="{{ $room->name }}" class="h-full w-full object-cover transition duration-500 hover:scale-105" loading="lazy" decoding="async" sizes="(min-width: 1024px) 33vw, 100vw">
                    </div>
                    <div class="p-6">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h2 class="text-2xl font-semibold text-stone-950">{{ $room->name }}</h2>
                                <p class="mt-1 text-sm text-stone-500">{{ ucfirst($room->status) }} · Capacity {{ $room->capacity }}</p>
                                <p class="mt-2 text-xs font-semibold uppercase tracking-[0.18em] text-stone-400">
                                    {{ $room->physical_rooms_count }} {{ Str::plural('physical room', $room->physical_rooms_count) }}
                                    · {{ $room->physicalRooms->where('status', 'available')->count() }} available
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="badge-primary">₱{{ number_format($room->price, 0) }}</span>
                                @if ($room->status === 'maintenance')
                                    <span class="rounded-full bg-amber-200 px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-amber-950 ring-1 ring-amber-400">Maintenance</span>
                                @endif
                            </div>
                        </div>
                        <p class="mt-4 text-sm leading-7 text-stone-600">{{ $room->description }}</p>
                        <div class="mt-6 flex flex-wrap gap-2">
                            <a href="{{ route('rooms.show', $room->slug) }}" class="btn-primary">View room</a>
                            <a href="#"
                               data-modal-open="edit-room-modal"
                               data-modal-title="Edit room"
                               data-room-id="{{ $room->id }}"
                               data-room-name="{{ e($room->name) }}"
                               data-room-description="{{ e($room->description) }}"
                               data-room-capacity="{{ $room->capacity }}"
                               data-room-price="{{ $room->price }}"
                               data-room-status="{{ $room->status }}"
                               data-room-amenities='@json($room->amenities ?? [])'
                               data-room-images='@json($room->images ?? [])'
                               data-room-physical-rooms='@json($room->physicalRooms->map(fn ($physicalRoom) => ['id' => $physicalRoom->id, 'name' => $physicalRoom->name, 'status' => $physicalRoom->status])->values())'
                               data-room-update-url="{{ route('admin.rooms.update', $room) }}"
                               class="btn-secondary btn-edit">Edit</a>
                            <a href="#" data-delete-open data-delete-url="{{ route('admin.rooms.destroy', $room) }}" class="btn-secondary btn-delete">Delete</a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="surface p-8 text-center text-stone-500">No rooms found.</div>
            @endforelse
        </section>
    @endif
</div>

<x-modal id="add-room-modal" title="Add room" size="max-w-4xl">
    <form id="add-room-form" method="POST" action="{{ route('admin.rooms.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="add_room_name">Room type name</label>
                <input type="text" id="add_room_name" name="name" class="form-input @error('name') error @enderror" value="{{ old('name') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="add_room_description">Description</label>
                <textarea id="add_room_description" name="description" rows="5" class="form-input @error('description') error @enderror" required>{{ old('description') }}</textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="add_room_capacity">Capacity</label>
                <input type="number" id="add_room_capacity" name="capacity" min="1" max="20" class="form-input @error('capacity') error @enderror" value="{{ old('capacity') }}" required>
                @error('capacity') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="add_room_price">Price</label>
                <input type="number" id="add_room_price" name="price" min="0" step="0.01" class="form-input @error('price') error @enderror" value="{{ old('price') }}" required>
                @error('price') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="add_room_status">Status</label>
                <select id="add_room_status" name="status" class="form-input border border-gray-300 @error('status') error @enderror" required>
                    <option value="available" @selected(old('status', 'available') === 'available')>Available</option>
                    <option value="occupied" @selected(old('status') === 'occupied')>Occupied</option>
                    <option value="maintenance" @selected(old('status') === 'maintenance')>Maintenance</option>
                </select>
                @error('status') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group md:col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <label class="form-label mb-0">Physical room inventory</label>
                    <button type="button" id="add-physical-unit-row" class="btn-secondary py-1.5 px-3 text-xs font-semibold">+ Add Unit</button>
                </div>
                <p class="mt-1 text-xs text-stone-500">Optional. Add individual units with unique codes, or leave empty to auto-create from count below.</p>
                <div id="add-physical-units-container" class="mt-3 space-y-3">
                    @foreach (old('physical_rooms', []) as $index => $unit)
                        @if (! empty($unit['name']) || ! empty($unit['code']))
                            <div data-physical-unit-row class="grid gap-2 sm:grid-cols-4 items-end border border-stone-200 rounded-lg p-3">
                                <div>
                                    <label class="text-xs text-stone-600">Unit name</label>
                                    <input type="text" name="physical_rooms[{{ $index }}][name]" value="{{ $unit['name'] ?? '' }}" placeholder="e.g. Room 101" class="form-input py-1.5 px-3 text-xs w-full border border-gray-300">
                                </div>
                                <div>
                                    <label class="text-xs text-stone-600">Unit code</label>
                                    <input type="text" name="physical_rooms[{{ $index }}][code]" value="{{ $unit['code'] ?? '' }}" placeholder="e.g. room-101" class="form-input py-1.5 px-3 text-xs w-full border border-gray-300">
                                </div>
                                <div class="flex items-center gap-2 pt-5">
                                    <label class="flex items-center gap-1 text-sm text-stone-700">
                                        <input type="checkbox" name="physical_rooms[{{ $index }}][is_available]" value="1" @checked(old("physical_rooms.{$index}.is_available", true)) class="rounded border-stone-300">
                                        Available
                                    </label>
                                </div>
                                <div class="pt-5 text-right">
                                    <button type="button" class="remove-unit-row text-xs font-semibold text-red-600 hover:underline">Remove</button>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                @error('physical_rooms') <p class="form-error">{{ $message }}</p> @enderror
                @error('physical_rooms.*.name') <p class="form-error">{{ $message }}</p> @enderror
                @error('physical_rooms.*.code') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="add_physical_room_count">Auto-create count</label>
                <input type="number" id="add_physical_room_count" name="physical_room_count" min="1" max="100" class="form-input border border-gray-300 @error('physical_room_count') error @enderror" value="{{ old('physical_room_count', 1) }}">
                @error('physical_room_count') <p class="form-error">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-stone-500">Used only when no units are listed above.</p>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="add_room_amenities">Amenities</label>
                <textarea id="add_room_amenities" name="amenities" rows="4" class="form-input @error('amenities') error @enderror" placeholder="Wi-Fi, Air conditioning, Pool, Breakfast">{{ old('amenities') }}</textarea>
                @error('amenities') <p class="form-error">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-stone-500">Separate amenities with commas.</p>
            </div>
            <div class="form-group md:col-span-2">
                @include('partials.image-input-toggle', [
                    'prefix' => 'add_room_image',
                    'fileId' => 'add_room_images',
                    'urlId' => 'add_room_image_url',
                    'fileName' => 'images[]',
                    'multiple' => true,
                    'label' => 'Room images',
                ])
                <input type="hidden" name="image_order" id="add_room_image_order" value="[]">
                <div id="add-room-images-preview" class="mt-4 grid gap-3 grid-cols-2 sm:grid-cols-3 select-none"></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Create room</button>
        </div>
    </form>
</x-modal>

<x-modal id="edit-room-modal" title="Edit room" size="max-w-4xl">
    <form id="edit-room-form" method="POST" action="#" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid gap-4 md:grid-cols-2">
            <div class="form-group">
                <label class="form-label" for="edit_room_name">Room type name</label>
                <input type="text" id="edit_room_name" name="name" class="form-input" required>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="edit_room_description">Description</label>
                <textarea id="edit_room_description" name="description" rows="5" class="form-input" required></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="edit_room_capacity">Capacity</label>
                <input type="number" id="edit_room_capacity" name="capacity" min="1" max="20" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="edit_room_price">Price</label>
                <input type="number" id="edit_room_price" name="price" min="0" step="0.01" class="form-input" required>
            </div>
            <div class="form-group">
                <label class="form-label" for="edit_room_status">Status</label>
                <select id="edit_room_status" name="status" class="form-input" required>
                    <option value="available">Available</option>
                    <option value="occupied">Occupied</option>
                    <option value="maintenance">Maintenance</option>
                </select>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">Physical rooms inventory</label>
                <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
                    <table class="min-w-full divide-y divide-stone-100 text-sm">
                        <thead class="bg-stone-50 text-xs font-semibold uppercase tracking-wider text-stone-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Room Name/Number</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="edit-physical-rooms-table-body" class="divide-y divide-stone-100">
                            <!-- JS will populate rows -->
                        </tbody>
                    </table>
                    <div class="p-3 bg-stone-50 border-t border-stone-100 flex justify-between items-center">
                        <span class="text-xs text-stone-500">Guests book general room type; staff assigns physical rooms.</span>
                        <button type="button" id="edit-physical-rooms-add-row" class="btn-secondary py-1.5 px-3 text-xs flex items-center gap-1 font-semibold">
                            <span>+ Add Room</span>
                        </button>
                    </div>
                </div>
                <textarea id="edit_physical_rooms" name="physical_rooms" class="hidden"></textarea>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="edit_room_amenities">Amenities</label>
                <textarea id="edit_room_amenities" name="amenities" rows="4" class="form-input" placeholder="Wi-Fi, Air conditioning, Pool, Breakfast"></textarea>
                <p class="mt-2 text-xs text-stone-500">Separate amenities with commas.</p>
            </div>
            <div class="form-group md:col-span-2">
                @include('partials.image-input-toggle', [
                    'prefix' => 'edit_room_image',
                    'fileId' => 'edit_room_images',
                    'urlId' => 'edit_room_image_url',
                    'fileName' => 'images[]',
                    'multiple' => true,
                    'label' => 'Room images',
                ])
                <input type="hidden" name="image_order" id="edit_room_image_order" value="[]">
                <div id="edit-room-images-preview" class="mt-4 grid gap-3 grid-cols-2 sm:grid-cols-3 select-none"></div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-2">
            <button type="button" data-modal-close class="btn-secondary">Cancel</button>
            <button type="submit" class="btn-primary">Save changes</button>
        </div>
    </form>
</x-modal>

<script>
    (() => {
        const resolveImageUrl = (path) => {
            if (!path) return '';
            if (path.startsWith('http') || path.startsWith('data:')) return path;

            const normalized = path.replace(/^\/+/, '').replace(/^storage\//, '');
            const uploadBaseUrl = @json($uploadUrlBase);

            return `${uploadBaseUrl}/${normalized}`;
        };

        // File size and count validation helper
        const validateFiles = (files) => {
            if (!files || files.length === 0) return true;
            if (files.length > 5) {
                alert('You can upload a maximum of 5 images.');
                return false;
            }
            let totalSize = 0;
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                if (file.size > 10 * 1024 * 1024) {
                    alert(`File "${file.name}" exceeds the 10MB maximum size limit.`);
                    return false;
                }
                totalSize += file.size;
            }
            if (totalSize > 50 * 1024 * 1024) {
                alert('Total size of uploaded files exceeds the 50MB limit.');
                return false;
            }
            return true;
        };

        // Reusable physical rooms table builder setup
        const setupPhysicalRoomsTable = (tableBody, addRowBtn, textarea, initialRooms = []) => {
            tableBody.innerHTML = '';

            const createRow = (id, name, status) => {
                const tr = document.createElement('tr');
                tr.className = 'transition hover:bg-stone-50/50';

                // Name Column
                const nameTd = document.createElement('td');
                nameTd.className = 'px-4 py-3';
                const nameInput = document.createElement('input');
                nameInput.type = 'text';
                nameInput.value = name;
                nameInput.placeholder = 'e.g. Room 101';
                nameInput.className = 'form-input py-1.5 px-3 text-xs w-full';
                nameInput.required = true;
                if (id) nameInput.dataset.id = id;
                nameInput.addEventListener('input', serialize);
                nameTd.appendChild(nameInput);
                tr.appendChild(nameTd);

                // Status Column
                const statusTd = document.createElement('td');
                statusTd.className = 'px-4 py-3';
                const statusSelect = document.createElement('select');
                statusSelect.className = 'form-input py-1.5 px-3 text-xs w-full';
                statusSelect.innerHTML = `
                    <option value="available">Available</option>
                    <option value="maintenance">Maintenance</option>
                `;
                statusSelect.value = status || 'available';
                statusSelect.addEventListener('change', serialize);
                statusTd.appendChild(statusSelect);
                tr.appendChild(statusTd);

                // Action Column
                const actionTd = document.createElement('td');
                actionTd.className = 'px-4 py-3 text-right';
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'text-red-600 hover:text-red-900 text-xs font-semibold px-2 py-1';
                removeBtn.textContent = 'Remove';
                removeBtn.addEventListener('click', () => {
                    tr.remove();
                    serialize();
                });
                actionTd.appendChild(removeBtn);
                tr.appendChild(actionTd);

                tableBody.appendChild(tr);
                serialize();
            };

            function serialize() {
                const rows = Array.from(tableBody.querySelectorAll('tr'));
                const serialized = rows.map(tr => {
                    const nameInput = tr.querySelector('input');
                    const select = tr.querySelector('select');
                    const id = nameInput.dataset.id;
                    const name = nameInput.value.trim().replace(/\|/g, '');
                    const status = select.value;
                    if (!name) return '';
                    return id ? `${id} | ${name} | ${status}` : `${name} | ${status}`;
                }).filter(Boolean).join('\n');
                
                textarea.value = serialized;
            }

            // Populate initial rooms
            if (initialRooms && initialRooms.length > 0) {
                initialRooms.forEach(room => {
                    createRow(room.id || null, room.name || '', room.status || 'available');
                });
            } else if (textarea.value.trim()) {
                textarea.value.split('\n').forEach(line => {
                    const parts = line.split('|').map(p => p.trim());
                    if (parts.length === 3) {
                        createRow(parts[0], parts[1], parts[2]);
                    } else if (parts.length === 2) {
                        createRow(null, parts[0], parts[1]);
                    }
                });
            }

            addRowBtn.onclick = (e) => {
                e.preventDefault();
                createRow(null, '', 'available');
            };
        };

        // Unified Preview & Drag-and-Drop Image Manager Setup
        const setupRoomImagesPreviewManager = (formId, fileInputId, urlTextareaSelector, previewContainerId, orderInputId) => {
            const form = document.getElementById(formId);
            const fileInput = document.getElementById(fileInputId);
            const previewContainer = document.getElementById(previewContainerId);
            const orderInput = document.getElementById(orderInputId);
            if (!form || !fileInput || !previewContainer || !orderInput) return null;

            const urlTextarea = form.querySelector(urlTextareaSelector);

            let activeFiles = []; // Array of File objects currently active

            const updateOrder = () => {
                const items = Array.from(previewContainer.children).map(child => {
                    return {
                        type: child.dataset.type,
                        value: child.dataset.value
                    };
                });
                
                orderInput.value = JSON.stringify(items);

                // Update UI badges
                const children = Array.from(previewContainer.children);
                children.forEach((child, index) => {
                    const badge = child.querySelector('.order-badge');
                    if (badge) {
                        badge.textContent = index === 0 ? 'Featured' : (index + 1);
                        if (index === 0) {
                            badge.className = 'absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-brand-primary text-white tracking-wide select-none order-badge shadow-sm';
                        } else {
                            badge.className = 'absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-stone-900/80 text-white tracking-wide select-none order-badge';
                        }
                    }
                });
            };

            const createPreviewCard = (type, value, src, name) => {
                const card = document.createElement('div');
                card.className = 'relative overflow-hidden rounded-2xl border border-stone-200 bg-stone-50 p-2 flex flex-col group cursor-move select-none transition hover:border-brand-primary/50';
                card.draggable = true;
                card.dataset.type = type;
                card.dataset.value = value;

                card.innerHTML = `
                    <div class="relative h-28 w-full overflow-hidden rounded-xl bg-stone-100">
                        <img src="${src}" class="h-full w-full object-cover pointer-events-none" loading="lazy">
                        <!-- Order badge -->
                        <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold uppercase rounded-md bg-stone-900/80 text-white tracking-wide select-none order-badge"></span>
                        <!-- Remove button -->
                        <button type="button" class="absolute top-2 right-2 p-1.5 rounded-full bg-red-600/90 text-white opacity-0 group-hover:opacity-100 transition-opacity duration-200 hover:bg-red-700 remove-btn" title="Remove image">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <div class="mt-2 px-1 text-[11px] text-stone-500 truncate w-full" title="${name}">${name}</div>
                `;

                card.querySelector('.remove-btn').addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    card.remove();
                    
                    if (type === 'file') {
                        activeFiles = activeFiles.filter(f => (f.name !== value));
                        syncFileInput();
                    } else if (type === 'url' && urlTextarea) {
                        const urls = urlTextarea.value.split('\n')
                            .map(line => line.trim())
                            .filter(line => line && line !== value);
                        urlTextarea.value = urls.join('\n');
                    }
                    updateOrder();
                });

                // Drag events
                card.addEventListener('dragstart', () => {
                    card.classList.add('dragging', 'opacity-50');
                });

                card.addEventListener('dragend', () => {
                    card.classList.remove('dragging', 'opacity-50');
                    updateOrder();
                });

                return card;
            };

            const syncFileInput = () => {
                const dt = new DataTransfer();
                activeFiles.forEach(file => dt.items.add(file));
                fileInput.files = dt.files;
            };

            // Add newly selected files
            fileInput.addEventListener('change', () => {
                if (fileInput.files.length === 0) return;
                
                if (!validateFiles(fileInput.files)) {
                    fileInput.value = '';
                    return;
                }

                Array.from(fileInput.files).forEach(file => {
                    if (activeFiles.some(f => f.name === file.name)) return;

                    activeFiles.push(file);
                    const src = URL.createObjectURL(file);
                    const card = createPreviewCard('file', file.name, src, file.name);
                    previewContainer.appendChild(card);
                });

                syncFileInput();
                updateOrder();
            });

            // Sync URL paste inputs
            if (urlTextarea) {
                urlTextarea.addEventListener('input', () => {
                    const lines = urlTextarea.value.split('\n')
                        .map(line => line.trim())
                        .filter(line => line && (line.startsWith('http://') || line.startsWith('https://')));
                    
                    // Remove url cards that are no longer in the textarea
                    Array.from(previewContainer.children).forEach(child => {
                        if (child.dataset.type === 'url' && !lines.includes(child.dataset.value)) {
                            child.remove();
                        }
                    });

                    // Add new url cards
                    lines.forEach(url => {
                        const exists = Array.from(previewContainer.children).some(child => child.dataset.type === 'url' && child.dataset.value === url);
                        if (!exists) {
                            const name = url.substring(url.lastIndexOf('/') + 1) || 'Image Link';
                            const card = createPreviewCard('url', url, url, name);
                            previewContainer.appendChild(card);
                        }
                    });
                    
                    updateOrder();
                });
            }

            // Drag over container handler for reordering
            previewContainer.addEventListener('dragover', e => {
                e.preventDefault();
                const draggingEl = previewContainer.querySelector('.dragging');
                if (!draggingEl) return;
                
                const target = e.target.closest('[draggable="true"]:not(.dragging)');
                if (!target) return;
                
                const rect = target.getBoundingClientRect();
                const next = (e.clientY - rect.top) / (rect.bottom - rect.top) > 0.5 || 
                             (e.clientX - rect.left) / (rect.right - rect.left) > 0.5;
                             
                previewContainer.insertBefore(draggingEl, next ? target.nextSibling : target);
            });

            const populateExistingImages = (images) => {
                previewContainer.innerHTML = '';
                activeFiles = [];
                syncFileInput();

                if (urlTextarea) urlTextarea.value = '';

                const urls = [];

                images.forEach(image => {
                    const src = resolveImageUrl(image);
                    const isUrl = image.startsWith('http://') || image.startsWith('https://');
                    const name = image.substring(image.lastIndexOf('/') + 1) || 'Room Image';

                    if (isUrl) {
                        urls.push(image);
                        const card = createPreviewCard('url', image, src, name);
                        previewContainer.appendChild(card);
                    } else {
                        const card = createPreviewCard('existing', image, src, name);
                        previewContainer.appendChild(card);
                    }
                });

                if (urlTextarea && urls.length > 0) {
                    urlTextarea.value = urls.join('\n');
                }

                updateOrder();
            };

            return {
                populate: populateExistingImages,
                clear: () => {
                    previewContainer.innerHTML = '';
                    activeFiles = [];
                    syncFileInput();
                    if (urlTextarea) urlTextarea.value = '';
                    updateOrder();
                }
            };
        };

        // Remove pre-rendered physical unit rows in add modal
        document.getElementById('add-physical-units-container')?.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-unit-row')) {
                e.preventDefault();
                e.target.closest('[data-physical-unit-row]')?.remove();
            }
        });

        // Initialize Image Preview Managers
        const addImageManager = setupRoomImagesPreviewManager(
            'add-room-form',
            'add_room_images',
            'textarea[name="image_links"]',
            'add-room-images-preview',
            'add_room_image_order'
        );

        const editImageManager = setupRoomImagesPreviewManager(
            'edit-room-form',
            'edit_room_images',
            'textarea[name="image_links"]',
            'edit-room-images-preview',
            'edit_room_image_order'
        );

        const addForm = document.getElementById('add-room-form');
        if (addForm) {
            addForm.addEventListener('submit', (e) => {
                const fileInput = document.getElementById('add_room_images');
                if (fileInput?.files?.length > 0 && !validateFiles(fileInput.files)) {
                    e.preventDefault();
                    return;
                }

                const orderInput = document.getElementById('add_room_image_order');
                let hasImages = false;
                if (orderInput && orderInput.value) {
                    try {
                        const items = JSON.parse(orderInput.value);
                        hasImages = items.length > 0;
                    } catch(err) {}
                }
                if (!hasImages) {
                    e.preventDefault();
                    alert('Add at least one room image by uploading a file or pasting a valid image URL.');
                }
            });
        }

        // --- Edit Room Form Setup ---
        const populateEditRoomModal = (button) => {
            const activeForm = document.getElementById('edit-room-form');
            if (!activeForm || !button) return;

            const activePhysicalTableBody = document.getElementById('edit-physical-rooms-table-body');
            const activePhysicalAddRowBtn = document.getElementById('edit-physical-rooms-add-row');
            const activePhysicalTextarea = document.getElementById('edit_physical_rooms');

            const activeFields = {
                name: document.getElementById('edit_room_name'),
                description: document.getElementById('edit_room_description'),
                capacity: document.getElementById('edit_room_capacity'),
                price: document.getElementById('edit_room_price'),
                status: document.getElementById('edit_room_status'),
                physicalRooms: activePhysicalTextarea,
                amenities: document.getElementById('edit_room_amenities'),
            };

            activeForm.action = button.dataset.roomUpdateUrl || '#';
            if (activeFields.name) activeFields.name.value = button.dataset.roomName || '';
            if (activeFields.description) activeFields.description.value = button.dataset.roomDescription || '';
            if (activeFields.capacity) activeFields.capacity.value = button.dataset.roomCapacity || '';
            if (activeFields.price) activeFields.price.value = button.dataset.roomPrice || '';
            if (activeFields.status) activeFields.status.value = button.dataset.roomStatus || 'available';

            try {
                const physicalRooms = JSON.parse(button.dataset.roomPhysicalRooms || '[]');
                setupPhysicalRoomsTable(activePhysicalTableBody, activePhysicalAddRowBtn, activePhysicalTextarea, physicalRooms);
            } catch {
                setupPhysicalRoomsTable(activePhysicalTableBody, activePhysicalAddRowBtn, activePhysicalTextarea, []);
            }

            try {
                const amenities = JSON.parse(button.dataset.roomAmenities || '[]');
                if (activeFields.amenities) {
                    activeFields.amenities.value = Array.isArray(amenities) ? amenities.join(', ') : '';
                }
            } catch {
                if (activeFields.amenities) activeFields.amenities.value = '';
            }

            try {
                const images = JSON.parse(button.dataset.roomImages || '[]');
                const imageArray = Array.isArray(images) ? images : [];
                if (editImageManager) {
                    editImageManager.populate(imageArray);
                }
            } catch {
                if (editImageManager) {
                    editImageManager.clear();
                }
            }

            const editModal = document.getElementById('edit-room-modal');
            if (typeof window.initImageInputToggles === 'function') {
                window.initImageInputToggles(editModal);
            }
        };

        if (!window.editRoomModalListenerBound) {
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-modal-open="edit-room-modal"]');
                if (!button) return;

                populateEditRoomModal(button);
            }, true);
            window.editRoomModalListenerBound = true;
        }

        const editForm = document.getElementById('edit-room-form');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                const fileInput = document.getElementById('edit_room_images');
                if (fileInput?.files?.length > 0 && !validateFiles(fileInput.files)) {
                    e.preventDefault();
                    return;
                }

                const orderInput = document.getElementById('edit_room_image_order');
                let hasImages = false;
                if (orderInput && orderInput.value) {
                    try {
                        const items = JSON.parse(orderInput.value);
                        hasImages = items.length > 0;
                    } catch(err) {}
                }
                if (!hasImages) {
                    e.preventDefault();
                    alert('Keep at least one existing image, upload a new file, or paste a valid image URL.');
                }
            });
        }
    })();
</script>
@include('partials.room-type-modal-script')
@endsection
