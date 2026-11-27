{{--
    Input berlabel untuk filter disposisi.
    <x-disposisi.kolom name="dari" label="Dari tanggal" type="date" :value="$dari" />

    type         -> default "text"
    auto = true  -> form langsung dikirim saat nilai diganti (default false)
    id           -> opsional, default "f-{name}"
    atribut lain (value, min, max, maxlength, placeholder) diteruskan ke <input>
--}}
@props(['name', 'label', 'type' => 'text', 'auto' => false])

@php $id = $attributes->get('id', 'f-' . $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-1 block text-xs font-medium text-slate-500">{{ $label }}</label>
    <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
        {{ $attributes->except('id')->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-none outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100']) }}
        @if ($auto) onchange="this.form.submit()" @endif>
</div>
