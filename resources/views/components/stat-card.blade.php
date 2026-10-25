@props([
    'label',
    'value',
    'icon' => 'activity',
    'delta' => null,
    'trend' => 'up', // up|down|flat
    'hint' => null,
    'tone' => 'brand', // brand|dark|amber|rose
])

@php
    $iconWrap = match ($tone) {
        'dark' => 'bg-ink-900 text-brand-400',
        'amber' => 'bg-amber-50 text-amber-600 ring-amber-100',
        'rose' => 'bg-rose-50 text-rose-600 ring-rose-100',
        default => 'bg-brand-50 text-brand-600 ring-brand-100',
    };

    $trendColor = match ($trend) {
        'down' => 'text-rose-600 bg-rose-50',
        'flat' => 'text-slate-500 bg-slate-100',
        default => 'text-emerald-600 bg-emerald-50',
    };

    $trendIcon = match ($trend) {
        'down' => 'trending-down',
        'flat' => 'minus',
        default => 'trending-up',
    };
@endphp

<div {{ $attributes->merge(['class' => 'card card-hover group relative overflow-hidden p-5']) }}>
    <div class="pointer-events-none absolute -right-6 -top-8 size-24 rounded-full bg-brand-400/10 blur-2xl transition group-hover:bg-brand-400/20"></div>

    <div class="flex items-start justify-between gap-3">
        <p class="text-[12.5px] font-semibold uppercase tracking-wide text-muted">{{ $label }}</p>
        <span class="inline-flex size-9 items-center justify-center rounded-xl ring-1 {{ $iconWrap }}">
            <x-icon :name="$icon" class="size-[18px]" />
        </span>
    </div>

    <p class="mt-3 text-[27px] font-extrabold leading-none tracking-tight text-heading">{{ $value }}</p>

    <div class="mt-3 flex items-center gap-2">
        @if ($delta)
            <span class="inline-flex items-center gap-1 rounded-md px-1.5 py-0.5 text-[11px] font-bold {{ $trendColor }}">
                <x-icon :name="$trendIcon" class="size-3" />
                {{ $delta }}
            </span>
        @endif
        @if ($hint)
            <span class="text-[11.5px] text-muted">{{ $hint }}</span>
        @endif
    </div>
</div>
