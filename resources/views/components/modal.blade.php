{{--
    Centered modal dialog driven by a shared Alpine store so any button can open it:

        <x-modal name="create-project" title="New project" max-width="2xl">…body…</x-modal>

        <button @click="$store.modals.open('create-project')">New project</button>

    The modal auto-opens when its bound server errors exist (validation feedback),
    via the data-open-on-error attribute set by the including view.
--}}
@props(['name', 'title' => null, 'description' => null, 'maxWidth' => 'lg', 'closeable' => true])

@php
    $widths = [
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
    ];
@endphp

<div x-data
     x-show="$store.modals.isOpen('{{ $name }}')"
     x-cloak
     x-transition.opacity.duration.200ms
     class="fixed inset-0 z-50 overflow-y-auto"
     role="dialog" aria-modal="true">

    <div class="flex min-h-full items-start justify-center p-4 pt-6 sm:p-6 sm:pt-10">
        <div class="fixed inset-0 bg-ink-950/60 backdrop-blur-md" @if ($closeable) @click="$store.modals.close('{{ $name }}')" @endif></div>

        <div x-show="$store.modals.isOpen('{{ $name }}')"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-3 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
             @keydown.escape.window="$store.modals.close('{{ $name }}')"
             class="relative w-full {{ $widths[$maxWidth] ?? 'max-w-lg' }} rounded-2xl border border-slate-200 bg-white shadow-2xl">

            @if ($title)
                <div class="flex items-start gap-3 border-b border-slate-100 px-6 py-4">
                    <div class="min-w-0 flex-1">
                        <h3 class="text-[16px] font-bold tracking-tight text-heading">{{ $title }}</h3>
                        @if ($description)
                            <p class="mt-0.5 text-[12.5px] text-muted">{{ $description }}</p>
                        @endif
                    </div>
                    @if ($closeable)
                        <button type="button" @click="$store.modals.close('{{ $name }}')"
                                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-heading">
                            <x-icon name="x" class="size-4" />
                        </button>
                    @endif
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>
</div>
