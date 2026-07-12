@php
    $code = $code ?? 500;
    $title = $title ?? __('chapung.errors.500_title');
    $message = $message ?? __('chapung.errors.500_message');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $code }} - Chapung Art</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white antialiased">
    <main class="flex min-h-screen items-center justify-center px-4 py-12 sm:px-6 lg:px-8" aria-labelledby="error-title">
        <section class="w-full max-w-3xl rounded-lg border border-zinc-800 bg-zinc-950/80 px-6 py-10 text-center shadow-2xl shadow-black/40 sm:px-10 sm:py-14">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Chapung Art</p>
            <p class="mt-6 text-sm font-black uppercase tracking-[0.28em] text-red-500">{{ __('chapung.errors.label', ['code' => $code]) }}</p>
            <h1 id="error-title" class="mx-auto mt-4 max-w-2xl text-4xl font-black uppercase leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                {{ $title }}
            </h1>
            <p class="mx-auto mt-6 max-w-xl text-base leading-8 text-zinc-300 sm:text-lg">
                {{ $message }}
            </p>
            <div class="mt-9 flex justify-center">
                <a href="{{ url('/') }}" class="inline-flex min-h-12 items-center justify-center rounded-md bg-yellow-600 px-6 py-3 text-center text-xs font-black uppercase tracking-[0.18em] text-black transition hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:ring-offset-2 focus:ring-offset-black">
                    {{ __('chapung.errors.back_home') }}
                </a>
            </div>
        </section>
    </main>
</body>
</html>
