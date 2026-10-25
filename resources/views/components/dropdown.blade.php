{{--
    Alpine dropdown.
    <x-dropdown align="right" width="56">
        <x-slot:trigger><button>…</button></x-slot:trigger>
        …menu items…
    </x-dropdown>
--}}
@props(['align' => 'right', 'width' => '56'])

@php
    $alignment = $align === 'left' ? 'left-0 origin-top-left' : 'right-0 origin-top-right';
@endphp

<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <div @click="open = ! open">
        {{ $trigger }}
    </div>

    <div x-show="open"
         x-cloak
         @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
         class="absolute z-50 mt-2 w-{{ $width }} origin-top overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lift {{ $alignment }}">
        {{ $slot }}
    </div>
</div>
