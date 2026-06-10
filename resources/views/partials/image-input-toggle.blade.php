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
    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
    <div class="inline-flex rounded-lg border border-gray-300 p-1 bg-gray-100 mb-3">
        <button type="button" class="image-mode-btn px-4 py-1.5 text-xs font-semibold rounded-md transition-all bg-white text-gray-900 shadow-sm" data-mode="upload">Upload file</button>
        <button type="button" class="image-mode-btn px-4 py-1.5 text-xs font-semibold rounded-md transition-all text-gray-500 hover:text-gray-900" data-mode="url">Paste URL</button>
    </div>
    <input type="hidden" name="{{ $modeName }}" value="{{ old($modeName, 'upload') }}" class="image-input-mode">

    <div class="image-upload-panel">
        <input type="file" id="{{ $fileId }}" name="{{ $fileName }}" class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2 @error($fileName) border-red-500 @enderror @error('images') border-red-500 @enderror @error('images.*') border-red-500 @enderror" accept="{{ $accept }}" @if($multiple) multiple @endif>
        @error('images') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('images.*') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('image') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="image-url-panel hidden">
        <input type="url" id="{{ $urlId }}" name="{{ $urlName }}" value="{{ old($urlName) }}" placeholder="https://example.com/image.jpg" class="block w-full text-sm border border-gray-300 rounded-lg px-3 py-2 @error($urlName) border-red-500 @enderror @error('image_url') border-red-500 @enderror @error('image_links') border-red-500 @enderror">
        @if ($multiple ?? false)
            <textarea name="image_links" rows="3" placeholder="One URL per line" class="mt-2 block w-full text-sm border border-gray-300 rounded-lg px-3 py-2 @error('image_links') border-red-500 @enderror">{{ old('image_links') }}</textarea>
        @endif
        @error($urlName) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('image_url') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        @error('image_links') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>
