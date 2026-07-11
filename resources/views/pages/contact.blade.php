@extends('layouts.public')

@php
    $email = site_setting('email', (string) config('chapung.emails.info'));
    $contactNumbers = site_contact_numbers();
    $address = site_setting('address', (string) config('chapung.address'));
    $maps = trim((string) site_setting('google_maps_url', (string) config('chapung.google_maps_url')));
    $maps = filter_var($maps, FILTER_VALIDATE_URL) ? $maps : null;
    $mapQuery = rawurlencode(preg_replace('/\s+/', ' ', trim((string) $address)) ?: 'Chapung Art Merauke');
    $mapEmbedUrl = 'https://www.google.com/maps?q='.$mapQuery.'&output=embed';
    $mailboxes = $mailboxes ?? [];
@endphp

@section('seo')
    @include('partials.seo-meta', ['seo' => seo_meta('contact', fallback: [
        'title' => __('chapung.pages.contact.title').' | '.site_setting('site_name', 'Chapung Art'),
        'description' => __('chapung.pages.contact.description'),
        'canonical_url' => route('contact'),
    ])])
@endsection

@section('content')
    <section class="border-b border-zinc-800 bg-[radial-gradient(circle_at_top_right,rgba(127,29,29,.2),transparent_30rem),#050505] px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl">
            <p class="text-xs font-black uppercase tracking-[0.32em] text-yellow-600">{{ __('chapung.pages.contact.title') }}</p>
            <h1 class="mt-4 max-w-4xl text-4xl font-black uppercase leading-none tracking-tight text-white sm:text-6xl">{{ __('chapung.pages.contact.heading') }}</h1>
            <p class="mt-6 max-w-3xl text-lg leading-8 text-zinc-300">{{ __('chapung.pages.contact.intro') }}</p>
        </div>
    </section>

    <section class="px-4 py-16 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[.85fr_1.15fr]">
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-1">
                @foreach ($contactNumbers as $contact)
                    <a href="https://wa.me/{{ $contact['whatsapp'] }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.common.whatsapp') }} {{ $contact['label'] }}</p>
                        <h2 class="mt-3 text-2xl font-black text-white">{{ $contact['phone'] }}</h2>
                    </a>
                @endforeach
                <a href="mailto:{{ $email }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.common.email') }}</p>
                    <h2 class="mt-3 text-2xl font-black text-white">{{ $email }}</h2>
                </a>
                <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.pages.contact.address') }}</p>
                    <h2 class="mt-3 text-2xl font-black text-white">{{ $address }}</h2>
                </div>
                @if ($maps)
                    <a href="{{ $maps }}" target="_blank" rel="noopener" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6 hover:border-yellow-600/70">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.pages.contact.maps') }}</p>
                        <h2 class="mt-3 text-2xl font-black text-white">{{ __('chapung.common.open_location') }}</h2>
                    </a>
                @else
                    <div class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                        <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.pages.contact.maps') }}</p>
                        <h2 class="mt-3 text-2xl font-black text-white">{{ __('chapung.common.location_not_set') }}</h2>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('contact.send') }}" class="rounded-lg border border-zinc-800 bg-zinc-950 p-6">
                @csrf
                <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.pages.contact.form') }}</p>
                <h2 class="mt-3 text-3xl font-black uppercase text-white">{{ __('chapung.pages.contact.send') }}</h2>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="name" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.contact.name') }}</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('name') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('email') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="department" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.contact.mailbox') }}</label>
                        <select id="department" name="department" required class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white focus:border-yellow-600 focus:ring-yellow-600">
                            @foreach ($mailboxes as $key => $mailbox)
                                <option value="{{ $key }}" @selected(old('department', 'contact') === $key)>{{ $mailbox['label'] }}</option>
                            @endforeach
                        </select>
                        @error('department') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="subject" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.contact.subject') }}</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="160" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">
                        @error('subject') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="message" class="text-xs font-black uppercase tracking-[0.16em] text-zinc-400">{{ __('chapung.pages.contact.message') }}</label>
                    <textarea id="message" name="message" required rows="6" maxlength="3000" class="mt-2 w-full rounded-md border border-zinc-800 bg-black px-4 py-3 text-sm text-white placeholder:text-zinc-600 focus:border-yellow-600 focus:ring-yellow-600">{{ old('message') }}</textarea>
                    @error('message') <p class="mt-2 text-xs font-bold text-red-400">{{ $message }}</p> @enderror
                </div>

                <button class="mt-5 rounded-md bg-yellow-600 px-6 py-4 text-xs font-black uppercase tracking-[0.18em] text-black hover:bg-yellow-500">{{ __('chapung.common.send_message') }}</button>
            </form>
        </div>

        <div class="mx-auto mt-8 max-w-7xl overflow-hidden rounded-lg border border-zinc-800 bg-zinc-950">
            <div class="flex flex-col gap-4 border-b border-zinc-800 p-6 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.24em] text-yellow-600">{{ __('chapung.pages.contact.maps') }}</p>
                    <h2 class="mt-3 max-w-3xl whitespace-pre-line text-2xl font-black text-white">{{ $address }}</h2>
                </div>
                @if ($maps)
                    <a href="{{ $maps }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md border border-yellow-600 px-5 py-3 text-xs font-black uppercase tracking-[0.16em] text-yellow-500 transition hover:bg-yellow-600 hover:text-black">
                        {{ __('chapung.common.open_location') }}
                    </a>
                @endif
            </div>
            <div class="aspect-[4/3] w-full bg-black sm:aspect-[16/7]">
                <iframe
                    src="{{ $mapEmbedUrl }}"
                    title="{{ __('chapung.pages.contact.maps') }} {{ $address }}"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    class="h-full w-full border-0"
                    allowfullscreen
                ></iframe>
            </div>
        </div>
    </section>
@endsection
