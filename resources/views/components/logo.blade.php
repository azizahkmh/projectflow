{{--
    ProjectFlow brand mark — uses the uploaded logo image.
    Props: $size (sm|md|lg), $tone (light|dark), $withWordmark (bool)
--}}
@props(['size' => 'md', 'withWordmark' => false, 'tone' => 'light'])

@php
    $dims = match ($size) {
        'sm' => ['tile' => 'h-8 w-8 rounded-[9px]', 'img' => 'h-[18px] w-[18px]', 'text' => 'text-[15px]'],
        'lg' => ['tile' => 'h-12 w-12 rounded-2xl', 'img' => 'h-7 w-7', 'text' => 'text-xl'],
        default => ['tile' => 'h-9 w-9 rounded-xl', 'img' => 'h-5 w-5', 'text' => 'text-[17px]'],
    };

    $tile = $tone === 'dark'
        ? 'bg-brand-400'
        : 'bg-ink-900';
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="relative inline-flex {{ $dims['tile'] }} {{ $tile }} items-center justify-center shadow-[0_6px_18px_-8px_rgba(15,23,42,0.7)] ring-1 ring-white/10">
        <img src="{{ asset('images/logo.png') }}"
             alt="ProjectFlow"
             class="{{ $dims['img'] }} object-contain"
             width="24" height="24">
    </span>

    @if ($withWordmark)
        <span class="{{ $dims['text'] }} font-extrabold tracking-[-0.02em]">
            Project<span class="text-brand-500">Flow</span>
        </span>
    @endif
</div>
