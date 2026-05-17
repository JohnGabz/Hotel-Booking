@extends('layouts.admin')

@section('content')
@php
    $roomImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1200&q=80',
        'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80',
    ];
    $resolveRoomImage = fn (?string $image) => $image ? (str_starts_with($image, 'http') ? $image : asset('storage/' . $image)) : null;
    $viewMode = $viewMode ?? 'grid';
@endphp

<div class="space-y-8">
    <section class="surface p-6 sm:p-8 lg:p-10">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <span class="eyebrow">Rooms</span>
                <h1 class="mt-4 responsive-title lg:text-5xl">Room inventory designed as a visual catalog.</h1>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-stone-600">Switch between grid and table modes, inspect rates, and update availability from one consistent screen.</p>
            </div>
            <a href="#" data-modal-open="add-room-modal" data-modal-title="Add room" class="btn-primary">Add room</a>
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
                            <th class="px-5 py-4">Room</th>
                            <th class="px-5 py-4">Status</th>
                            <th class="px-5 py-4">Capacity</th>
                            <th class="px-5 py-4">Rate</th>
                            <th class="px-5 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($rooms as $room)
                            <tr class="transition hover:bg-stone-50/80">
                                <td class="px-5 py-4 font-semibold text-stone-950" data-label="Room">{{ $room->name }}</td>
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
                <label class="form-label" for="add_room_name">Room name</label>
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
                <select id="add_room_status" name="status" class="form-input @error('status') error @enderror" required>
                    <option value="available" @selected(old('status') === 'available')>Available</option>
                    <option value="occupied" @selected(old('status') === 'occupied')>Occupied</option>
                    <option value="maintenance" @selected(old('status') === 'maintenance')>Maintenance</option>
                </select>
                @error('status') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label" for="add_room_amenities">Amenities</label>
                <textarea id="add_room_amenities" name="amenities" rows="4" class="form-input @error('amenities') error @enderror" placeholder="Wi-Fi, Air conditioning, Pool, Breakfast">{{ old('amenities') }}</textarea>
                @error('amenities') <p class="form-error">{{ $message }}</p> @enderror
                <p class="mt-2 text-xs text-stone-500">Separate amenities with commas.</p>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">Room images source</label>
                <div class="mt-2 flex gap-3">
                    <span class="btn-secondary px-4 py-2 text-sm">Upload files</span>
                    <span class="btn-secondary px-4 py-2 text-sm">Paste URLs</span>
                </div>
            </div>

            <div id="add-room-upload-section" class="form-group md:col-span-2">
                <label class="form-label" for="add_room_images">Upload images</label>
                <input type="file" id="add_room_images" name="images[]" class="form-input @error('images') error @enderror @error('images.*') error @enderror" accept="image/*" multiple>
                <div id="add-room-upload-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                @error('images') <p class="form-error">{{ $message }}</p> @enderror
                @error('images.*') <p class="form-error">{{ $message }}</p> @enderror
                <p class="text-xs text-stone-500">Select one or more images, paste image URLs below, or use both.</p>
            </div>

            <div id="add-room-link-section" class="form-group md:col-span-2">
                <label class="form-label" for="add_room_image_links">Image URLs</label>
                <textarea id="add_room_image_links" name="image_links" rows="4" class="form-input @error('image_links') error @enderror" placeholder="Enter image URLs, one per line&#10;https://example.com/image1.jpg&#10;https://example.com/image2.jpg">{{ old('image_links') }}</textarea>
                <div id="add-room-link-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                @error('image_links') <p class="form-error">{{ $message }}</p> @enderror
                <p class="text-xs text-stone-500">Paste image URLs (one per line). Images must be publicly accessible.</p>
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
                <label class="form-label" for="edit_room_name">Room name</label>
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
                <label class="form-label" for="edit_room_amenities">Amenities</label>
                <textarea id="edit_room_amenities" name="amenities" rows="4" class="form-input" placeholder="Wi-Fi, Air conditioning, Pool, Breakfast"></textarea>
                <p class="mt-2 text-xs text-stone-500">Separate amenities with commas.</p>
            </div>
            <div class="form-group md:col-span-2">
                <label class="form-label">Room images source</label>
                <div class="mt-2 flex gap-3">
                    <span class="btn-secondary px-4 py-2 text-sm">Keep existing</span>
                    <span class="btn-secondary px-4 py-2 text-sm">Add files</span>
                    <span class="btn-secondary px-4 py-2 text-sm">Add URLs</span>
                </div>
            </div>

            <div id="edit-room-upload-section" class="form-group md:col-span-2">
                <label class="form-label" for="edit_room_images">Upload images</label>
                <input type="file" id="edit_room_images" name="images[]" class="form-input" accept="image/*" multiple>
                <p class="mt-2 text-xs text-stone-500">New uploads are added to the gallery. Uncheck existing images below to remove them.</p>
                <div id="edit-room-current-images" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                <div id="edit-room-upload-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
            </div>

            <div id="edit-room-link-section" class="form-group md:col-span-2">
                <label class="form-label" for="edit_room_image_links">Image URLs</label>
                <textarea id="edit_room_image_links" name="image_links" rows="4" class="form-input" placeholder="Enter image URLs, one per line&#10;https://example.com/image1.jpg&#10;https://example.com/image2.jpg"></textarea>
                <div id="edit-room-link-preview" class="mt-3 grid gap-3 sm:grid-cols-3"></div>
                <p class="text-xs text-stone-500">Paste image URLs (one per line). Images must be publicly accessible.</p>
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
            return path.startsWith('http') ? path : `${window.location.origin}/storage/${path}`;
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

        const addImagesInput = document.getElementById('add_room_images');
        const addPreview = document.getElementById('add-room-upload-preview');
        if (addImagesInput && addPreview) {
            addImagesInput.addEventListener('change', () => renderFilePreviews(addImagesInput.files, addPreview));
        }

        // Handle image link textarea input for add room
        const addImageLinksInput = document.getElementById('add_room_image_links');
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

        const modal = document.getElementById('edit-room-modal');
        const form = document.getElementById('edit-room-form');
        const currentImagesContainer = document.getElementById('edit-room-current-images');
        const uploadPreviewContainer = document.getElementById('edit-room-upload-preview');
        const linkPreviewContainer = document.getElementById('edit-room-link-preview');
        const editImagesInput = document.getElementById('edit_room_images');
        const editImageLinksInput = document.getElementById('edit_room_image_links');
        if (!modal || !form) return;

        const fields = {
            name: document.getElementById('edit_room_name'),
            description: document.getElementById('edit_room_description'),
            capacity: document.getElementById('edit_room_capacity'),
            price: document.getElementById('edit_room_price'),
            status: document.getElementById('edit_room_status'),
            amenities: document.getElementById('edit_room_amenities'),
        };

        document.querySelectorAll('[data-modal-open="edit-room-modal"]').forEach((button) => {
            button.addEventListener('click', () => {
                form.action = button.dataset.roomUpdateUrl || '#';
                fields.name.value = button.dataset.roomName || '';
                fields.description.value = button.dataset.roomDescription || '';
                fields.capacity.value = button.dataset.roomCapacity || '';
                fields.price.value = button.dataset.roomPrice || '';
                fields.status.value = button.dataset.roomStatus || 'available';

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
                    
                    // Display current images
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
            });
        });

        if (editImagesInput && uploadPreviewContainer) {
            editImagesInput.addEventListener('change', () => renderFilePreviews(editImagesInput.files, uploadPreviewContainer));
        }

        // Handle image link textarea input for edit room
        if (editImageLinksInput && linkPreviewContainer) {
            editImageLinksInput.addEventListener('input', () => {
                const urls = editImageLinksInput.value
                    .split('\n')
                    .map(line => line.trim())
                    .filter(line => line && line.startsWith('http'));
                renderImageUrlPreviews(urls, linkPreviewContainer);
            });
        }
    })();
</script>
@endsection
