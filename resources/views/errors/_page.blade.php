@php
    $status = $status ?? 500;
    $title = $title ?? 'Something went wrong';
    $message = $message ?? 'Something unexpected happened. Please try again in a moment.';
    $action = $action ?? 'Go home';
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} - {{ $title }} | {{ config('app.name') }}</title>
    <meta name="robots" content="noindex">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Satoshi:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-stone-50 text-stone-900">
    <main class="flex min-h-screen items-center justify-center px-4 py-10">
        <section class="w-full max-w-2xl rounded-2xl border border-stone-200 bg-white p-6 shadow-xl sm:p-10">
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-brand-primary">{{ $status }}</p>
            <h1 class="mt-4 text-3xl font-bold text-stone-950 sm:text-5xl">{{ $title }}</h1>
            <p class="mt-4 text-base leading-7 text-stone-600">{{ $message }}</p>

            @if (! empty($errors) && $errors->any())
                <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-4">
                    <p class="text-sm font-semibold text-red-800">Please check these fields:</p>
                    <ul class="mt-3 list-disc space-y-1 pl-5 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <button type="button" onclick="history.length > 1 ? history.back() : window.location.assign('{{ route('home') }}')" class="btn-secondary">Go back</button>
                <a href="{{ route('home') }}" class="btn-primary">{{ $action }}</a>
            </div>
        </section>
    </main>
</body>
</html>
