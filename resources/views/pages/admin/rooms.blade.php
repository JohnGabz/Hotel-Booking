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
                            <a href="#" data-modal-open="generic-action-modal" data-modal-title="Delete room" data-action-url="{{ route('admin.rooms.destroy', $room) }}" data-action-method="DELETE" class="btn-secondary btn-delete">Delete</a>
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
                <div id="add-room-upload-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                <div id="add-room-link-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
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
                <label class="form-label">Current room images</label>
                <div id="edit-room-current-images" class="mt-2 grid gap-3 sm:grid-cols-3">
                    <!-- JS will populate current images -->
                </div>
            </div>
            <div class="form-group md:col-span-2">
                @include('partials.image-input-toggle', [
                    'prefix' => 'edit_room_image',
                    'fileId' => 'edit_room_images',
                    'urlId' => 'edit_room_image_url',
                    'fileName' => 'images[]',
                    'multiple' => true,
                    'label' => 'Add more room images',
                ])
                <div id="edit-room-upload-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                <div id="edit-room-link-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
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

            const normalized = path.replace(/^\\/+/, '').replace(/^storage\\//, '');
            const uploadBaseUrl = @json($uploadUrlBase);

            return `${uploadBaseUrl}/${normalized}`;
        };

        const createImageFigure = (src, alt = 'Room image') => {
            const figure = document.createElement('figure');
            figure.className = 'overflow-hidden rounded-2xl border border-stone-200 bg-stone-50';

            const img = document.createElement('img');
            img.src = src;
            img.alt = alt;
            img.className = 'h-28 w-full object-cover';

            figure.appendChild(img);

            return figure;
        };

        const renderFilePreviews = (files, container) => {
            if (!container) return;
            container.innerHTML = '';

            if (!files || !files.length) return;

            Array.from(files).forEach((file) => {
                const reader = new FileReader();
                reader.onload = (event) => {
                    container.appendChild(createImageFigure(event.target.result, 'Preview'));
                };
                reader.readAsDataURL(file);
            });
        };

        const renderStoredImages = (images, container) => {
            if (!container) return;
            container.innerHTML = '';

            if (!images || !images.length) {
                container.innerHTML = '<p class="text-sm text-stone-500">No images uploaded yet.</p>';
                return;
            }

            images.forEach((image) => {
                const figure = createImageFigure(resolveImageUrl(image));
                const label = document.createElement('label');
                label.className = 'flex items-center gap-2 px-3 py-2 text-xs text-stone-600';

                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'retained_images[]';
                checkbox.value = image;
                checkbox.checked = true;
                checkbox.className = 'rounded border-stone-300';

                const text = document.createElement('span');
                text.textContent = 'Keep this image';

                label.appendChild(checkbox);
                label.appendChild(text);
                figure.appendChild(label);
                container.appendChild(figure);
            });
        };

        const renderImageUrlPreviews = (urls, container) => {
            if (!container) return;
            container.innerHTML = '';

            if (!urls || !urls.length) return;

            urls.forEach((url) => {
                container.appendChild(createImageFigure(url, 'Preview'));
            });
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

        // Setup image input toggle for edit modal (legacy helper)
        const setupImageToggle = (toggleUploadBtn, toggleLinkBtn, uploadSection, linkSection, fileInput, textareaInput) => {
            let activeMode = 'upload';

            const setMode = (mode) => {
                activeMode = mode;
                if (mode === 'upload') {
                    toggleUploadBtn.className = 'px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-stone-900 shadow-sm';
                    toggleLinkBtn.className = 'px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-stone-500 hover:text-stone-900';
                    uploadSection.classList.remove('hidden');
                    linkSection.classList.add('hidden');
                } else {
                    toggleUploadBtn.className = 'px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-stone-500 hover:text-stone-900';
                    toggleLinkBtn.className = 'px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-stone-900 shadow-sm';
                    uploadSection.classList.add('hidden');
                    linkSection.classList.remove('hidden');
                }
            };

            toggleUploadBtn.addEventListener('click', (e) => { e.preventDefault(); setMode('upload'); });
            toggleLinkBtn.addEventListener('click', (e) => { e.preventDefault(); setMode('url'); });

            return {
                getMode: () => activeMode,
                sanitize: () => {
                    if (activeMode === 'upload') {
                        textareaInput.value = '';
                    } else {
                        fileInput.value = '';
                    }
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

        const addForm = document.getElementById('add-room-form');
        const addImagesInput = document.getElementById('add_room_images');
        const addPreview = document.getElementById('add-room-upload-preview');
        if (addImagesInput && addPreview) {
            addImagesInput.addEventListener('change', () => {
                if (validateFiles(addImagesInput.files)) {
                    renderFilePreviews(addImagesInput.files, addPreview);
                } else {
                    addImagesInput.value = '';
                    addPreview.innerHTML = '';
                }
            });
        }

        const addImageLinksInput = addForm?.querySelector('textarea[name="image_links"]');
        const addImageLinkPreview = document.getElementById('add-room-link-preview');
        if (addImageLinksInput && addImageLinkPreview) {
            addImageLinksInput.addEventListener('input', () => {
                const urls = addImageLinksInput.value
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line && line.startsWith('http'));
                renderImageUrlPreviews(urls, addImageLinkPreview);
            });
        }

        if (addForm) {
            addForm.addEventListener('submit', (e) => {
                const mode = addForm.querySelector('.image-input-mode')?.value || 'upload';
                const fileInput = addForm.querySelector('input[type="file"][name="images[]"]');
                const linksInput = addForm.querySelector('textarea[name="image_links"]');

                if (mode === 'upload' && linksInput) {
                    linksInput.value = '';
                } else if (mode === 'url' && fileInput) {
                    fileInput.value = '';
                }

                if (fileInput?.files?.length > 0 && !validateFiles(fileInput.files)) {
                    e.preventDefault();
                }
            });
        }

        // --- Edit Room Form Setup ---
        const editForm = document.getElementById('edit-room-form');
        const editPhysicalTableBody = document.getElementById('edit-physical-rooms-table-body');
        const editPhysicalAddRowBtn = document.getElementById('edit-physical-rooms-add-row');
        const editPhysicalTextarea = document.getElementById('edit_physical_rooms');
        const currentImagesContainer = document.getElementById('edit-room-current-images');
        const uploadPreviewContainer = document.getElementById('edit-room-upload-preview');
        const linkPreviewContainer = document.getElementById('edit-room-link-preview');
        const editImagesInput = document.getElementById('edit_room_images');
        const editImageLinksInput = editForm?.querySelector('textarea[name="image_links"]');

        const fields = {
            name: document.getElementById('edit_room_name'),
            description: document.getElementById('edit_room_description'),
            capacity: document.getElementById('edit_room_capacity'),
            price: document.getElementById('edit_room_price'),
            status: document.getElementById('edit_room_status'),
            physicalRooms: editPhysicalTextarea,
            amenities: document.getElementById('edit_room_amenities'),
        };

        const populateEditRoomModal = (button) => {
            if (!editForm || !button) return;

            editForm.action = button.dataset.roomUpdateUrl || '#';
            fields.name.value = button.dataset.roomName || '';
            fields.description.value = button.dataset.roomDescription || '';
            fields.capacity.value = button.dataset.roomCapacity || '';
            fields.price.value = button.dataset.roomPrice || '';
            fields.status.value = button.dataset.roomStatus || 'available';

            try {
                const physicalRooms = JSON.parse(button.dataset.roomPhysicalRooms || '[]');
                setupPhysicalRoomsTable(editPhysicalTableBody, editPhysicalAddRowBtn, editPhysicalTextarea, physicalRooms);
            } catch {
                setupPhysicalRoomsTable(editPhysicalTableBody, editPhysicalAddRowBtn, editPhysicalTextarea, []);
            }

            try {
                const amenities = JSON.parse(button.dataset.roomAmenities || '[]');
                fields.amenities.value = Array.isArray(amenities) ? amenities.join(', ') : '';
            } catch {
                fields.amenities.value = '';
            }

            if (editImagesInput) {
                editImagesInput.value = '';
            }

            if (editImageLinksInput) {
                editImageLinksInput.value = '';
            }

            try {
                const images = JSON.parse(button.dataset.roomImages || '[]');
                const imageArray = Array.isArray(images) ? images : [];
                renderStoredImages(imageArray, currentImagesContainer);
            } catch {
                renderStoredImages([], currentImagesContainer);
            }

            if (uploadPreviewContainer) {
                uploadPreviewContainer.innerHTML = '';
            }

            if (linkPreviewContainer) {
                linkPreviewContainer.innerHTML = '';
            }

            const editModal = document.getElementById('edit-room-modal');
            if (typeof window.initImageInputToggles === 'function') {
                window.initImageInputToggles(editModal);
            }
        };

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-modal-open="edit-room-modal"]');
            if (!button) return;

            populateEditRoomModal(button);
        }, true);

        if (editImagesInput && uploadPreviewContainer) {
            editImagesInput.addEventListener('change', () => {
                if (validateFiles(editImagesInput.files)) {
                    renderFilePreviews(editImagesInput.files, uploadPreviewContainer);
                } else {
                    editImagesInput.value = '';
                    uploadPreviewContainer.innerHTML = '';
                }
            });
        }

        if (editImageLinksInput && linkPreviewContainer) {
            editImageLinksInput.addEventListener('input', () => {
                const urls = editImageLinksInput.value
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line && line.startsWith('http'));
                renderImageUrlPreviews(urls, linkPreviewContainer);
            });
        }

        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                const mode = editForm.querySelector('.image-input-mode')?.value || 'upload';
                const fileInput = editForm.querySelector('input[type="file"][name="images[]"]');
                const linksInput = editForm.querySelector('textarea[name="image_links"]');

                if (mode === 'upload' && linksInput) {
                    linksInput.value = '';
                } else if (mode === 'url' && fileInput) {
                    fileInput.value = '';
                }

                if (fileInput?.files?.length > 0 && !validateFiles(fileInput.files)) {
                    e.preventDefault();
                }
            });
        }
    })();
</script>
@include('partials.room-type-modal-script')
@endsection
