{{--
    A section header used on every module page.
    Right side accepts a $actions slot (buttons).
--}}
@props(['title', 'subtitle' => null, 'eyebrow' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div>
        @if ($eyebrow)
            <p class="eyebrow mb-1">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-[22px] font-bold tracking-tight text-heading">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 max-w-2xl text-[13.5px] leading-relaxed text-muted">{{ $subtitle }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
