@php
    $prefix = $prefix ?? 'image';
    $fileId = $fileId ?? $prefix;
    $urlId = $urlId ?? $prefix . '_url';
    $modeName = $modeName ?? 'image_input_mode';
    $fileName = $fileName ?? ($multiple ?? false ? 'images[]' : 'image');
    $urlName = $urlName ?? 'image_url';
    $multiple = $multiple ?? false;
    $accept = $accept ?? 'image/*';
    $label = $label ?? 'Room image';
@endphp

<div class="image-input-toggle" data-image-input>
    <label class="form-label">{{ $label }}</label>
    <div class="inline-flex rounded-lg border border-gray-300 p-1 bg-stone-100 mb-3">
        <button type="button" class="image-mode-btn px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-stone-900 shadow-sm" data-mode="upload">Upload file</button>
        <button type="button" class="image-mode-btn px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-stone-500 hover:text-stone-900" data-mode="url">Paste URL</button>
    </div>
    <input type="hidden" name="{{ $modeName }}" value="{{ old($modeName, 'upload') }}" class="image-input-mode">

    <div class="image-upload-panel">
        <input type="file" id="{{ $fileId }}" name="{{ $fileName }}" class="form-input border border-gray-300 @error('images') error @enderror @error('images.*') error @enderror @error('image') error @enderror" accept="{{ $accept }}" @if($multiple) multiple @endif>
        @error('images') <p class="form-error">{{ $message }}</p> @enderror
        @error('images.*') <p class="form-error">{{ $message }}</p> @enderror
        @error('image') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="image-url-panel hidden">
        <input type="url" id="{{ $urlId }}" name="{{ $urlName }}" value="{{ old($urlName) }}" placeholder="https://example.com/image.jpg" class="form-input border border-gray-300 @error($urlName) error @enderror @error('image_url') error @enderror @error('image_links') error @enderror">
        @if ($multiple ?? false)
            <textarea name="image_links" rows="3" placeholder="One URL per line" class="form-input border border-gray-300 mt-2 @error('image_links') error @enderror">{{ old('image_links') }}</textarea>
        @endif
        @error($urlName) <p class="form-error">{{ $message }}</p> @enderror
        @error('image_url') <p class="form-error">{{ $message }}</p> @enderror
        @error('image_links') <p class="form-error">{{ $message }}</p> @enderror
    </div>
</div>
