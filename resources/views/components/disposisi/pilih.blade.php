{{--
    Dropdown berlabel untuk filter disposisi.
    <x-disposisi.pilih name="status" label="Status"> <option>…</option> </x-disposisi.pilih>

    auto = true  -> form langsung dikirim saat pilihan diganti (default)
    id           -> opsional, default "f-{name}"
--}}
@props(['name', 'label', 'auto' => true])

@php $id = $attributes->get('id', 'f-' . $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-1 block text-xs font-medium text-slate-500">{{ $label }}</label>
    <select id="{{ $id }}" name="{{ $name }}"
        {{ $attributes->except('id')->merge(['class' => 'block w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-none outline-none transition hover:border-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100']) }}
        @if ($auto) onchange="this.form.submit()" @endif>
        {{ $slot }}
    </select>
</div>
