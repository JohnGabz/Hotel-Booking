@props(['section'])

@php
    $imageUrl = fn (?string $value): string => \App\Support\ImageStorage::url($value, '');
    $fields = $section['modal_fields'] ?? $section['fields'];
@endphp

<form
    action="{{ $section['endpoint'] }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-5"
    data-landing-section-form
    data-section-id="{{ $section['id'] }}"
    data-section-title="{{ $section['title'] }}"
    data-no-loader
    novalidate
>
    @csrf

    <div class="rounded-lg border border-stone-200 bg-stone-50 p-4">
        <p class="text-sm font-semibold text-stone-900">{{ $section['title'] }}</p>
        <p class="mt-1 text-sm leading-7 text-stone-600">{{ $section['description'] }}</p>
    </div>
    <div class="hidden rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" data-form-summary></div>

    <div class="grid gap-4 xl:grid-cols-2">
        @foreach ($fields as $field)
            @php
                $type = $field['type'] ?? 'text';
                $key = $field['key'];
                $inputId = $section['id'] . '_' . $key;
                $value = $section['values'][$key] ?? '';
                $maxlength = $field['maxlength'] ?? null;
            @endphp

            <div class="form-group {{ $field['span'] ?? '' }}">
                <label class="form-label" for="{{ $inputId }}">{{ $field['label'] }}</label>

                @if ($type === 'textarea')
                    <textarea
                        id="{{ $inputId }}"
                        name="{{ $key }}"
                        rows="{{ $field['rows'] ?? 3 }}"
                        class="form-input"
                        data-field-label="{{ $field['label'] }}"
                        @if($maxlength) maxlength="{{ $maxlength }}" data-maxlength="{{ $maxlength }}" @endif
                    >{{ $value }}</textarea>
                    <p class="form-hint">Keep this concise and scannable on mobile screens.</p>
                @elseif ($type === 'image')
                    @php $preview = $imageUrl($value); @endphp
                    
                    <div class="mb-3 inline-flex rounded-lg p-1 bg-stone-100" role="group">
                        <button type="button" data-landing-toggle-mode="upload" data-target="{{ $inputId }}" class="landing-mode-btn-{{ $inputId }} px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-stone-900 shadow-sm">Upload files</button>
                        <button type="button" data-landing-toggle-mode="link" data-target="{{ $inputId }}" class="landing-mode-btn-{{ $inputId }} px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-stone-500 hover:text-stone-900">Paste URL</button>
                    </div>

                    <!-- Upload dropzone -->
                    <div id="{{ $inputId }}_upload_container" class="rounded-lg border border-dashed border-stone-300 bg-stone-50 p-4 transition hover:border-brand-primary/50" data-image-dropzone>
                        <input
                            id="{{ $inputId }}_upload"
                            name="{{ $key }}_upload"
                            type="file"
                            accept="image/*"
                            class="sr-only"
                            data-field-label="{{ $field['label'] }}"
                            data-image-input
                            data-preview-target="{{ $inputId }}_preview"
                            data-empty-target="{{ $inputId }}_empty"
                            data-remove-target="{{ $inputId }}_remove"
                        >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-stone-800">Drop an image here or choose a file</p>
                                <p class="mt-1 text-xs text-stone-500">PNG, JPG, or WebP up to 5 MB.</p>
                            </div>
                            <label for="{{ $inputId }}_upload" class="btn-secondary cursor-pointer px-4 py-2 text-sm">Choose image</label>
                        </div>
                    </div>

                    <!-- URL input -->
                    <div id="{{ $inputId }}_link_container" class="hidden rounded-lg border border-stone-200 bg-stone-50 p-4">
                        <label class="form-label" for="{{ $inputId }}_link">Image URL</label>
                        <input
                            id="{{ $inputId }}_link"
                            name="{{ $key }}_link"
                            type="url"
                            class="form-input mt-1"
                            placeholder="https://example.com/image.jpg"
                            data-field-label="{{ $field['label'] }} URL"
                        >
                    </div>

                    <input type="hidden" id="{{ $inputId }}_remove" name="{{ $key }}_remove" value="0" data-image-remove-input>
                    <div class="mt-3 space-y-3">
                        <img
                            id="{{ $inputId }}_preview"
                            src="{{ $preview }}"
                            alt="{{ $field['label'] }} preview"
                            class="{{ $preview === '' ? 'hidden' : '' }} h-40 w-full rounded-lg border border-stone-200 object-cover"
                            data-initial-src="{{ $preview }}"
                            loading="lazy"
                            decoding="async"
                        >
                        <div id="{{ $inputId }}_empty" class="{{ $preview === '' ? '' : 'hidden' }} rounded-lg border border-dashed border-stone-300 bg-stone-50 px-4 py-6 text-sm text-stone-500" data-initial-empty="{{ $preview === '' ? '1' : '0' }}">
                            No image uploaded yet.
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" class="btn-secondary px-4 py-2 text-sm" data-image-clear="{{ $inputId }}_upload">Remove image</button>
                        </div>
                    </div>
                @else
                    <input
                        id="{{ $inputId }}"
                        name="{{ $key }}"
                        type="{{ in_array($type, ['email', 'url', 'tel'], true) ? $type : 'text' }}"
                        class="form-input"
                        value="{{ $value }}"
                        data-field-label="{{ $field['label'] }}"
                        @if($maxlength) maxlength="{{ $maxlength }}" data-maxlength="{{ $maxlength }}" @endif
                    >
                    <p class="form-hint">{{ $type === 'url' ? 'Use a complete https:// URL.' : 'Shown directly on the public landing page.' }}</p>
                @endif

                <p class="form-error hidden" data-field-error="{{ $type === 'image' ? $key . '_upload' : $key }}"></p>
            </div>
        @endforeach
    </div>

    <div class="sticky bottom-0 -mx-4 -mb-4 flex flex-col-reverse gap-3 border-t border-stone-200 bg-white/95 px-4 py-4 shadow-[0_-14px_30px_rgba(28,25,23,0.08)] backdrop-blur sm:-mx-5 sm:-mb-5 sm:flex-row sm:justify-end sm:px-5">
        <a href="{{ route('admin.settings', ['tab' => 'landing']) }}" class="btn-secondary">Cancel</a>
        <button type="submit" class="btn-primary" data-landing-section-submit disabled>Save section</button>
    </div>
</form>

<script>
    (() => {
        const form = document.querySelector('[data-landing-section-form]');
        if (!form) return;

        // Mode toggling
        form.querySelectorAll('[data-landing-toggle-mode]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const mode = btn.dataset.landingToggleMode;
                const inputId = btn.dataset.target;
                const uploadContainer = document.getElementById(`${inputId}_upload_container`);
                const linkContainer = document.getElementById(`${inputId}_link_container`);
                
                // Toggle sections
                if (mode === 'upload') {
                    uploadContainer?.classList.remove('hidden');
                    linkContainer?.classList.add('hidden');
                } else {
                    uploadContainer?.classList.add('hidden');
                    linkContainer?.classList.remove('hidden');
                }

                // Update active state of buttons
                form.querySelectorAll(`.landing-mode-btn-${inputId}`).forEach(b => {
                    if (b.dataset.landingToggleMode === mode) {
                        b.className = `landing-mode-btn-${inputId} px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-stone-900 shadow-sm`;
                    } else {
                        b.className = `landing-mode-btn-${inputId} px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-stone-500 hover:text-stone-900`;
                    }
                });

                // Set mode tracking attribute
                btn.parentNode.setAttribute('data-active-mode', mode);
            });
        });

        // Watch url changes to update preview
        form.querySelectorAll('input[type="url"]').forEach(input => {
            input.addEventListener('input', () => {
                const url = input.value.trim();
                const previewId = input.id.replace('_link', '_preview');
                const emptyId = input.id.replace('_link', '_empty');
                const previewImg = document.getElementById(previewId);
                const emptyDiv = document.getElementById(emptyId);

                if (url && url.startsWith('http')) {
                    if (previewImg) {
                        previewImg.src = url;
                        previewImg.classList.remove('hidden');
                    }
                    if (emptyDiv) {
                        emptyDiv.classList.add('hidden');
                    }
                }
            });
        });

        // Sanitize on submission
        form.addEventListener('submit', () => {
            form.querySelectorAll('[data-landing-toggle-mode="upload"]').forEach(btn => {
                const mode = btn.parentNode.getAttribute('data-active-mode') || 'upload';
                const inputId = btn.dataset.target;
                if (mode === 'upload') {
                    // Clear link input
                    const linkInput = document.getElementById(`${inputId}_link`);
                    if (linkInput) linkInput.value = '';
                } else {
                    // Clear file input
                    const fileInput = document.getElementById(`${inputId}_upload`);
                    if (fileInput) fileInput.value = '';
                    const removeInput = document.getElementById(`${inputId}_remove`);
                    if (removeInput) removeInput.value = '0';
                }
            });
        });

        // Extend the generic image clear button to clear URL links as well
        form.querySelectorAll('[data-image-clear]').forEach(btn => {
            btn.addEventListener('click', () => {
                const inputId = btn.dataset.imageClear.replace('_upload', '');
                const linkInput = document.getElementById(`${inputId}_link`);
                if (linkInput) linkInput.value = '';
            });
        });
    })();
</script>
