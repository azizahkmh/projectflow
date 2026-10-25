{{--
    Lucide icon wrapper:  <x-icon name="folder" class="size-4" />
--}}
@props(['name' => 'circle', 'size' => null])

<i data-lucide="{{ $name }}"
   @if ($size) style="width:{{ $size }};height:{{ $size }}" @endif
   {{ $attributes->merge(['class' => 'inline-block align-middle']) }}></i>
