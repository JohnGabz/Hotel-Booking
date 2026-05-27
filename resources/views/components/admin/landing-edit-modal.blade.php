@props(['section'])

@php
    $imageUrl = function (?string $value): string {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        if (filter_var($value, FILTER_VALIDATE_URL)) {
            return $value;
        }

        $path = ltrim($value, '/');

        return asset(str_starts_with($path, 'storage/') ? $path : 'storage/' . $path);
    };
@endphp

<x-modal id="landing-section-modal-{{ $section['id'] }}" :title="'Edit ' . $section['title']" size="max-w-4xl">
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

        <p class="text-sm leading-7 text-stone-600">{{ $section['description'] }}</p>
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
                    @elseif ($type === 'image')
                        @php $preview = $imageUrl($value); @endphp
                        <input
                            id="{{ $inputId }}_upload"
                            name="{{ $key }}_upload"
                            type="file"
                            accept="image/*"
                            class="form-input pt-2"
                            data-field-label="{{ $field['label'] }}"
                            data-image-input
                            data-preview-target="{{ $inputId }}_preview"
                            data-empty-target="{{ $inputId }}_empty"
                        >
                        <div class="mt-3">
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
                    @endif

                    <p class="form-error hidden" data-field-error="{{ $type === 'image' ? $key . '_upload' : $key }}"></p>
                </div>
            @endforeach
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-stone-200 pt-5 sm:flex-row sm:justify-end">
            <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
            <button type="submit" class="btn-primary" data-landing-section-submit>Save section</button>
        </div>
    </form>
</x-modal>
