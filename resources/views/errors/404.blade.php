<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Chapung Art</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-black text-white">
    <main class="flex min-h-screen items-center justify-center px-6">
        <section class="max-w-2xl text-center">
            <p class="text-xs font-black uppercase tracking-[0.36em] text-yellow-600">404</p>
            <h1 class="mt-5 text-5xl font-black uppercase md:text-7xl">Halaman Tidak Ditemukan</h1>
            <p class="mt-6 text-lg leading-8 text-zinc-400">Ruang yang Anda cari tidak tersedia di galeri Chapung Art.</p>
            <a href="{{ route('home') }}" class="mt-8 inline-flex rounded-xl bg-yellow-600 px-7 py-4 text-sm font-black uppercase tracking-[0.2em] text-black hover:bg-yellow-500">Kembali Beranda</a>
        </section>
    </main>
</body>
</html>
