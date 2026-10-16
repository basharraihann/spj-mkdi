<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuratTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // buang baris kosong pada daftar dasar/untuk/peserta
        $this->merge([
            'dasar' => array_values(array_filter((array) $this->input('dasar'), fn ($v) => filled($v))),
            'untuk' => array_values(array_filter((array) $this->input('untuk'), fn ($v) => filled($v))),
            'peserta' => array_values(array_filter((array) $this->input('peserta'), fn ($p) => filled($p['nama'] ?? null))),
        ]);
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'tempat' => ['required', 'string', 'max:100'],
            'kode_klasifikasi' => ['required', 'string', 'max:50'],
            'menimbang' => ['nullable', 'string'],
            'dasar' => ['nullable', 'array'],
            'dasar.*' => ['string'],
            'untuk' => ['required', 'array', 'min:1'],
            'untuk.*' => ['string'],
            'kepada' => ['nullable', 'string', 'max:255'],
            'jabatan_penandatangan' => ['required', 'string', 'max:255'],
            'nama_penandatangan' => ['required', 'string', 'max:255'],
            'nip_penandatangan' => ['nullable', 'string', 'max:30'],
            'peserta' => ['required', 'array', 'min:1'],
            'peserta.*.pegawai_id' => ['nullable', 'integer'],
            'peserta.*.nama' => ['required', 'string', 'max:255'],
            'peserta.*.nip' => ['nullable', 'string', 'max:30'],
            'peserta.*.jabatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'untuk' => 'poin "Untuk"',
            'peserta' => 'peserta',
            'peserta.*.nama' => 'nama peserta',
        ];
    }
}
