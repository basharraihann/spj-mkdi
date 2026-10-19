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
            'dasar' => array_values(array_filter((array) $this->input('dasar'), fn($v) => filled($v))),
            'untuk' => array_values(array_filter((array) $this->input('untuk'), fn($v) => filled($v))),
            'peserta' => array_values(array_filter((array) $this->input('peserta'), fn($p) => filled($p['nama'] ?? null))),
        ]);
    }

    public function rules(): array
    {
        return [
            'tanggal' => ['required', 'date'],
            'tempat' => ['required', 'string', 'max:100'],
            'kode_klasifikasi' => ['required', 'string', 'max:50'],

            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'provinsi' => ['required', 'string', 'max:100'],
            'kota' => ['required', 'string', 'max:100'],
            'tujuan' => ['nullable', 'string', 'max:255'],

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
            'peserta.*.jenis' => ['required', 'in:pns,non_pns'],
            'peserta.*.pegawai_id' => ['nullable', 'integer'],
            'peserta.*.nama' => ['required', 'string', 'max:255'],
            'peserta.*.nip' => ['nullable', 'string', 'max:30'],
            'peserta.*.jabatan' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_klasifikasi' => 'nomor ST',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'provinsi' => 'provinsi',
            'kota' => 'kota/kabupaten',
            'untuk' => 'poin "Untuk"',
            'peserta' => 'peserta',
            'peserta.*.jenis' => 'jenis peserta',
            'peserta.*.nama' => 'nama peserta',
            'jabatan_penandatangan' => 'jabatan penandatangan',
            'nama_penandatangan' => 'penandatangan',
        ];
    }
}