{{--
    Right-hand slide-over panel, same store API as <x-modal>:

        <x-slide-over name="edit-project" :title="…">
            <x-slot:footer>…</x-slot:footer>
            …form body…
        </x-slide-over>
--}}
@props(['name', 'title' => 'Panel', 'description' => null])

<div x-data
     x-show="$store.modals.isOpen('{{ $name }}')"
     x-cloak
     class="fixed inset-0 z-50 overflow-hidden"
     role="dialog" aria-modal="true">

    <div class="absolute inset-0 bg-ink-950/50 backdrop-blur-[3px]"
         x-show="$store.modals.isOpen('{{ $name }}')"
         x-transition.opacity.duration.200ms
         @click="$store.modals.close('{{ $name }}')"></div>

    <div class="pointer-events-none absolute inset-y-0 right-0 flex w-full max-w-[520px]">
        <div x-show="$store.modals.isOpen('{{ $name }}')"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-250"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="translate-x-full"
             @keydown.escape.window="$store.modals.close('{{ $name }}')"
             class="pointer-events-auto flex h-full w-full flex-col border-l border-slate-200 bg-white shadow-2xl">

            <div class="flex shrink-0 items-start gap-3 border-b border-slate-100 px-6 py-4">
                <div class="min-w-0 flex-1">
                    <h3 class="text-[16px] font-bold tracking-tight text-heading">{{ $title }}</h3>
                    @if ($description)
                        <p class="mt-0.5 text-[12.5px] text-muted">{{ $description }}</p>
                    @endif
                </div>
                <button type="button" @click="$store.modals.close('{{ $name }}')"
                        class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-heading">
                    <x-icon name="x" class="size-4" />
                </button>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-6 py-5">
                {{ $slot }}
            </div>

            @isset($footer)
                <div class="shrink-0 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    {{ $footer }}
                </div>
            @endisset
        </div>
    </div>
</div>
