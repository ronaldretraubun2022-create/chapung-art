@props([
    'as' => 'input',
])

@if ($as === 'textarea')
    <textarea {{ $attributes->merge(['class' => 'ca-field w-full']) }}>{{ $slot }}</textarea>
@elseif ($as === 'select')
    <select {{ $attributes->merge(['class' => 'ca-field w-full']) }}>{{ $slot }}</select>
@else
    <input {{ $attributes->merge(['class' => 'ca-field w-full']) }}>
@endif
