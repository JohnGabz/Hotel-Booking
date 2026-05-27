@props(['section'])

@php
    $imageUrl = fn (?string $value): string => \App\Support\ImageStorage::url($value, '');
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
        @foreach ($section['fields'] as $field)
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
                    <div class="rounded-lg border border-dashed border-stone-300 bg-stone-50 p-4 transition hover:border-brand-primary/50" data-image-dropzone>
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
