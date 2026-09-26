@php
    $item = $item ?? null;
    $formKey = $item ? 'edit-' . $item->id : 'create';
    $useOld = old('_form') === $formKey;
    // token acak tiap kali form di-render, biar Chrome gak bisa nge-grup field antar modal
    $nonce = \Illuminate\Support\Str::random(8);
@endphp

<div>
    <label class="text-xs text-gray-500">Nomor MAK</label>
    <input type="text" name="mak" value="{{ $useOld ? old('mak') : $item?->mak }}" required
        autocomplete="{{ $nonce }}-mak"
        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
</div>
<div>
    <label class="text-xs text-gray-500">Uraian Kegiatan</label>
    <input type="text" name="uraian_giat" value="{{ $useOld ? old('uraian_giat') : $item?->uraian_giat }}"
        autocomplete="{{ $nonce }}-giat"
        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
</div>
<div>
    <label class="text-xs text-gray-500">Uraian Komponen</label>
    <input type="text" name="uraian_komponen" value="{{ $useOld ? old('uraian_komponen') : $item?->uraian_komponen }}"
        autocomplete="{{ $nonce }}-komponen"
        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
</div>
<div>
    <label class="text-xs text-gray-500">Uraian Akun AP</label>
    <input type="text" name="uraian_akun_ap" value="{{ $useOld ? old('uraian_akun_ap') : $item?->uraian_akun_ap }}"
        autocomplete="{{ $nonce }}-akun"
        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
</div>
<div>
    <label class="text-xs text-gray-500">Uraian Belanja</label>
    <input type="text" name="uraian_belanja" value="{{ $useOld ? old('uraian_belanja') : $item?->uraian_belanja }}"
        autocomplete="{{ $nonce }}-belanja"
        class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none">
</div>