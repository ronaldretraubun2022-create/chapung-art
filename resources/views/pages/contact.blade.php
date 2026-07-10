@extends('layouts.public')

@php
    $email = site_setting('email', 'info@chapungart.com');
    $whatsapp = preg_replace('/\D+/', '', site_setting('whatsapp', '6281234567890')) ?: '6281234567890';
    $address = site_setting('address', 'Merauke, Papua Selatan');
    $maps = site_setting('google_maps_url');
    $mailboxes = $mailboxes ?? [];
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('contact', fallback: [
        'title' => 'Contact | '.site_setting('site_name', 'Chapung Art'),
        'description' => 'Hubungi Chapung Art untuk karya seni, fotografi budaya, kolaborasi, dan marketplace seni Papua Selatan.',
        'canonical_url' => route('contact'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(127,29,29,.2),transparent_30rem),#050505] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">Contact</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">Terhubung dengan Chapung Art</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-300">Untuk pembelian karya, arsip fotografi, publikasi budaya, dan kolaborasi kreatif.</p>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.85fr_1.15fr]">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">WhatsApp</p>
                    <h2 class="mt-3 text-2xl font-black text-white">+{{ $whatsapp }}</h2>
                </a>
                <a href="mailto:{{ $email }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Email</p>
                    <h2 class="mt-3 text-2xl font-black text-white">{{ $email }}</h2>
                </a>
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Address</p>
                    <h2 class="mt-3 text-2xl font-black text-white">{{ $address }}</h2>
                </div>
                @if ($maps)
                    <a href="{{ $maps }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Maps</p>
                        <h2 class="mt-3 text-2xl font-black text-white">Open location</h2>
                    </a>
                @else
                    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Maps</p>
                        <h2 class="mt-3 text-2xl font-black text-white">Location URL belum diatur</h2>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('contact.send') }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                @csrf
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">Contact Form</p>
                <h2 class="mt-3 text-3xl font-black uppercase text-white">Kirim pesan</h2>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('name') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('email') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="department" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Mailbox</label>
                        <select id="department" name="department" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                            @foreach ($mailboxes as $key => $mailbox)
                                <option value="{{ $key }}" @selected(old('department', 'contact') === $key)>{{ $mailbox['label'] }}</option>
                            @endforeach
                        </select>
                        @error('department') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="subject" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('subject') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="message" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Message</label>
                    <textarea id="message" name="message" required rows="6" maxlength="3000" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                </div>

                <button class="mt-5 rounded-md bg-yellow-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">Send Message</button>
            </form>
        </div>
    </section>
@endsection
