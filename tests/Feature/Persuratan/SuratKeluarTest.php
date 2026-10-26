<?php

namespace Tests\Feature\Persuratan;

use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuatDataPersuratan;
use Tests\TestCase;

class SuratKeluarTest extends TestCase
{
    use RefreshDatabase, BuatDataPersuratan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->siapkanDataPersuratan();
    }

    private function buatSuratKeluar(User $pembuat, string $hal, string $status = 'draft', array $payload = []): SuratKeluar
    {
        return SuratKeluar::create([
            'jenis_dokumen' => 'nota_dinas',
            'nomor_surat' => null,
            'sifat' => 'biasa',
            'hal' => $hal,
            'tanggal_surat' => now()->toDateString(),
            'tujuan' => 'Pihak terkait',
            'dari' => $pembuat->unit?->nama,
            'isi_payload' => array_merge(['isi_ringkasan' => 'Isi uji.', 'perlu_es1' => 0], $payload),
            'tembusan' => [],
            'created_by' => $pembuat->id,
            'status_persetujuan' => $status,
        ]);
    }

    private function aksi(User $pengguna, SuratKeluar $surat, string $aksi, array $tambahan = [])
    {
        return $this->actingAs($pengguna)
            ->from(route('surat-keluar.show', $surat))
            ->patch(route('surat-keluar.status', $surat), array_merge(['aksi' => $aksi], $tambahan));
    }

    // ---------------------------------------------------------------- draf

    public function test_tu_membuat_draf_surat_keluar(): void
    {
        $this->actingAs($this->tuA)->post(route('surat-keluar.store'), [
            'jenis_dokumen' => 'nota_dinas',
            'sifat' => 'biasa',
            'hal' => 'Nota dinas dari unit A',
            'tanggal_surat' => now()->toDateString(),
            'isi_payload' => ['isi_ringkasan' => 'Ringkasan nota dinas unit A.'],
        ])->assertRedirect();

        $this->assertDatabaseHas('surat_keluars', [
            'hal' => 'Nota dinas dari unit A',
            'created_by' => $this->tuA->id,
            'status_persetujuan' => 'draft',
        ]);
    }

    public function test_pegawai_hanya_melihat_dokumennya_sendiri_sedangkan_admin_melihat_semua(): void
    {
        $this->buatSuratKeluar($this->tuA, 'Dokumen milik TU A');
        $this->buatSuratKeluar($this->pegA1, 'Dokumen milik pegawai A1');

        $this->actingAs($this->pegA1)->get(route('surat-keluar.index'))
            ->assertOk()
            ->assertSee('Dokumen milik pegawai A1')
            ->assertDontSee('Dokumen milik TU A');

        $this->actingAs($this->admin)->get(route('surat-keluar.index'))
            ->assertOk()
            ->assertSee('Dokumen milik pegawai A1')
            ->assertSee('Dokumen milik TU A');
    }

    public function test_filter_status_di_daftar_surat_keluar(): void
    {
        $this->buatSuratKeluar($this->tuA, 'Dokumen berstatus draf', 'draft');
        $this->buatSuratKeluar($this->tuA, 'Dokumen berstatus terbit', 'terbit');

        $this->actingAs($this->admin)->get(route('surat-keluar.index', ['status' => 'terbit']))
            ->assertOk()
            ->assertSee('Dokumen berstatus terbit')
            ->assertDontSee('Dokumen berstatus draf');
    }

    public function test_draf_boleh_diubah_pembuatnya_tetapi_tidak_setelah_diajukan(): void
    {
        $draf = $this->buatSuratKeluar($this->tuA, 'Draf boleh diedit');
        $this->actingAs($this->tuA)->get(route('surat-keluar.edit', $draf))->assertOk();

        $diajukan = $this->buatSuratKeluar($this->tuA, 'Sudah diajukan', 'diajukan');
        $this->actingAs($this->tuA)->get(route('surat-keluar.edit', $diajukan))
            ->assertRedirect(route('surat-keluar.show', $diajukan))
            ->assertSessionHasErrors('aksi');

        $this->actingAs($this->tuA)->delete(route('surat-keluar.destroy', $diajukan))
            ->assertSessionHasErrors('aksi');
        $this->assertDatabaseHas('surat_keluars', ['id' => $diajukan->id]);
    }

    // ----------------------------------------------------- alur persetujuan

    public function test_alur_lengkap_draf_diajukan_disetujui_eselon2_lalu_terbit(): void
    {
        $surat = $this->buatSuratKeluar($this->tuA, 'Nota dinas alur lengkap');

        $this->aksi($this->tuA, $surat, 'ajukan')->assertSessionHas('success');
        $this->assertSame('diajukan', $surat->fresh()->status_persetujuan);

        $this->aksi($this->es2A, $surat, 'setujui_es2')->assertSessionHas('success');
        $this->assertSame('disetujui_es2', $surat->fresh()->status_persetujuan);

        $this->aksi($this->tuA, $surat, 'terbit')->assertSessionHas('success');
        $this->assertSame('terbit', $surat->fresh()->status_persetujuan);
    }

    public function test_dokumen_yang_butuh_eselon1_harus_disetujui_eselon1_sebelum_terbit(): void
    {
        $surat = $this->buatSuratKeluar($this->tuD, 'Undangan butuh Eselon 1', 'diajukan', ['perlu_es1' => 1]);

        $this->aksi($this->es2A, $surat, 'setujui_es2')->assertSessionHas('success');

        // TU belum bisa menerbitkan sebelum Eselon 1 menyetujui
        $this->aksi($this->tuD, $surat, 'terbit')->assertSessionHasErrors('aksi');
        $this->assertSame('disetujui_es2', $surat->fresh()->status_persetujuan);

        $this->aksi($this->es1, $surat, 'setujui_es1')->assertSessionHas('success');
        $this->aksi($this->tuD, $surat, 'terbit')->assertSessionHas('success');
        $this->assertSame('terbit', $surat->fresh()->status_persetujuan);
    }

    public function test_pegawai_dan_tu_tidak_bisa_menyetujui(): void
    {
        $surat = $this->buatSuratKeluar($this->tuA, 'Menunggu persetujuan', 'diajukan');

        $this->aksi($this->pegA1, $surat, 'setujui_es2')->assertSessionHasErrors('aksi');
        $this->aksi($this->tuA, $surat, 'setujui_es2')->assertSessionHasErrors('aksi');

        $this->assertSame('diajukan', $surat->fresh()->status_persetujuan);
    }

    public function test_tidak_bisa_melompati_tahap_persetujuan(): void
    {
        $surat = $this->buatSuratKeluar($this->tuA, 'Masih draf');

        $this->aksi($this->es2A, $surat, 'setujui_es2')->assertSessionHasErrors('aksi');
        $this->aksi($this->tuA, $surat, 'terbit')->assertSessionHasErrors('aksi');

        $this->assertSame('draft', $surat->fresh()->status_persetujuan);
    }

    public function test_dokumen_dikembalikan_ke_draf_dengan_catatan_revisi(): void
    {
        $surat = $this->buatSuratKeluar($this->tuA, 'Perlu revisi', 'diajukan');

        $this->aksi($this->es2A, $surat, 'kembalikan', ['catatan' => 'Perbaiki bagian dasar hukum.'])
            ->assertSessionHas('success');

        $surat->refresh();
        $this->assertSame('draft', $surat->status_persetujuan);
        $this->assertSame('Perbaiki bagian dasar hukum.', $surat->payload('catatan_revisi'));
    }

    public function test_rekap_csv_hanya_memuat_dokumen_sesuai_filter(): void
    {
        $this->buatSuratKeluar($this->tuA, 'Dokumen unit A');
        $this->buatSuratKeluar($this->tuB, 'Dokumen unit B');

        $respons = $this->actingAs($this->admin)->get(route('surat-keluar.index', ['export' => 'csv']));

        $respons->assertOk();
        $this->assertStringContainsString('text/csv', (string) $respons->headers->get('Content-Type'));

        $isi = $respons->streamedContent();
        $this->assertStringContainsString('Dokumen unit A', $isi);
        $this->assertStringContainsString('Dokumen unit B', $isi);
    }
}
