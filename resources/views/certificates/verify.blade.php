@php
    $siteName = site_setting('site_name', 'Chapung Art');
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.seo-meta', ['seo' => seo_meta('certificates.verify', $certificate, [
        'title' => 'Certificate Verification - '.$siteName,
        'description' => 'Verifikasi Certificate of Authenticity Chapung Art.',
        'canonical_url' => route('certificates.verify', $certificateNumber),
        'robots' => $certificate ? 'index, follow' : 'noindex, follow',
    ])])
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <main class="flex min-h-screen items-center px-6 py-16">
        <section class="mx-auto w-full max-w-3xl rounded-3xl border border-zinc-800 bg-zinc-950 p-8 shadow-2xl shadow-black/30 md:p-12">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Certificate Verification</p>

            @if ($certificate)
                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <h1 class="text-3xl font-black uppercase tracking-tight md:text-5xl">{{ $certificate->certificate_number }}</h1>
                    <span class="rounded-full px-3 py-1 text-xs font-black uppercase tracking-[0.2em] {{ $certificate->is_verified ? 'bg-green-500 text-black' : 'bg-red-600 text-white' }}">
                        {{ $certificate->is_verified ? 'Verified' : 'Unverified' }}
                    </span>
                </div>

                <div class="mt-8 grid gap-4 text-sm text-zinc-300 md:grid-cols-2">
                    <div class="rounded-2xl border border-zinc-800 bg-black p-5">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Artwork</span>
                        <p class="mt-3 text-lg font-bold text-white">{{ $certificate->artwork?->title ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-800 bg-black p-5">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Artist</span>
                        <p class="mt-3 text-lg font-bold text-white">{{ $certificate->artist?->name ?: $certificate->artwork?->artist_display_name ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-800 bg-black p-5">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Owner</span>
                        <p class="mt-3 text-lg font-bold text-white">{{ $certificate->owner_name ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl border border-zinc-800 bg-black p-5">
                        <span class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Issued At</span>
                        <p class="mt-3 text-lg font-bold text-white">{{ optional($certificate->issued_at)->format('d M Y') ?: '-' }}</p>
                    </div>
                </div>
            @else
                <h1 class="mt-6 text-3xl font-black uppercase tracking-tight md:text-5xl">Certificate Not Found</h1>
                <p class="mt-5 text-zinc-300">Nomor sertifikat {{ $certificateNumber }} tidak ditemukan atau belum terdaftar.</p>
            @endif

            <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-xl border border-yellow-600/50 px-5 py-3 text-xs font-black uppercase tracking-[0.2em] text-yellow-600 hover:bg-yellow-600 hover:text-black">
                Back to {{ $siteName }}
            </a>
        </section>
    </main>
</body>
</html>
