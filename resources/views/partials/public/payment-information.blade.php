@php
    $bankAccounts = $bankAccounts ?? site_bank_accounts();
    $address = $address ?? site_setting('address', (string) config('chapung.address'));
    $variant = $variant ?? 'dark';
    $isLight = $variant === 'light';
@endphp

@if ($bankAccounts !== [] || filled($address))
    <section class="{{ $isLight ? 'border-t border-zinc-200 pt-5 text-zinc-700' : 'border-t border-zinc-800 pt-5 text-zinc-400' }}">
        <p class="text-xs font-black uppercase tracking-[0.22em] {{ $isLight ? 'text-yellow-700' : 'text-yellow-600' }}">{{ __('chapung.payment.instructions') }}</p>
        <p class="mt-2 text-sm leading-6 {{ $isLight ? 'text-zinc-500' : 'text-zinc-500' }}">{{ __('chapung.payment.description') }}</p>

        @if ($bankAccounts !== [])
            <div class="mt-4 space-y-3 text-sm">
                @foreach ($bankAccounts as $account)
                    <div class="grid gap-1">
                        <p class="font-black uppercase {{ $isLight ? 'text-zinc-950' : 'text-white' }}">{{ $account['bank'] }}</p>
                        <p><span class="font-bold {{ $isLight ? 'text-zinc-700' : 'text-zinc-300' }}">{{ __('chapung.payment.account_number') }}:</span> {{ $account['account_number'] }}</p>
                        @if (filled($account['account_name']))
                            <p><span class="font-bold {{ $isLight ? 'text-zinc-700' : 'text-zinc-300' }}">{{ __('chapung.payment.account_name') }}:</span> {{ $account['account_name'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        @if (filled($address))
            <div class="mt-4 text-sm leading-6">
                <p class="font-bold {{ $isLight ? 'text-zinc-950' : 'text-white' }}">{{ __('chapung.payment.domicile_address') }}</p>
                <p class="whitespace-pre-line">{{ $address }}</p>
            </div>
        @endif
    </section>
@endif
