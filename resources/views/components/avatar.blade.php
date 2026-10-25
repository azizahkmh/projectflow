@props(['name' => 'User', 'color' => '#84CC16', 'size' => 'md', 'subtitle' => null, 'ring' => false])

@php
    $dims = match ($size) {
        'sm' => 'h-7 w-7 text-[10px]',
        'lg' => 'h-12 w-12 text-sm',
        default => 'h-9 w-9 text-xs',
    };

    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2.5']) }}>
    <span class="inline-flex {{ $dims }} shrink-0 items-center justify-center rounded-full font-bold text-white ring-2 {{ $ring ? 'ring-white' : 'ring-transparent' }} shadow-sm"
          style="background: linear-gradient(135deg, {{ $color }}, {{ $color }}CC);"
          title="{{ $name }}">
        {{ $initials }}
    </span>

    @if ($subtitle !== null || $slot->isNotEmpty())
        <span class="min-w-0">
            <span class="block truncate text-[13px] font-semibold text-heading">{{ $name }}</span>
            @if ($subtitle)
                <span class="block truncate text-[11px] text-muted">{{ $subtitle }}</span>
            @else
                {{ $slot }}
            @endif
        </span>
    @endif
</div>
