{{-- Status pill. Usage: <x-status-badge :label="$p->status_label" :classes="$p->status_classes" :dot="true" /> --}}
@props(['label', 'classes' => 'bg-slate-100 text-slate-700 ring-slate-200', 'dot' => true])

<span {{ $attributes->merge(['class' => 'badge '.$classes]) }}>
    @if ($dot)
        <span class="size-1.5 rounded-full bg-current opacity-70"></span>
    @endif
    {{ $label }}
</span>
