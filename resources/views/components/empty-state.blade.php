{{-- Empty state block --}}
@props(['icon' => 'inbox', 'title' => 'Nothing here yet', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-14 text-center']) }}>
    <span class="mb-4 inline-flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 ring-1 ring-slate-200/70">
        <x-icon :name="$icon" class="size-6" />
    </span>
    <p class="text-sm font-semibold text-heading">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 max-w-sm text-[13px] text-muted">{{ $description }}</p>
    @endif
    @if (! empty(trim((string) $slot ?? '')))
        <div class="mt-4">{{ $slot }}</div>
    @endif
</div>
