<?php

namespace Tests\Feature\Persuratan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuatDataPersuratan;
use Tests\TestCase;

class MonitoringTest extends TestCase
{
    use RefreshDatabase, BuatDataPersuratan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->siapkanDataPersuratan();
    }

    public function test_semua_role_bisa_membuka_halaman_monitoring(): void
    {
        foreach ([$this->admin, $this->tuA, $this->es1, $this->es2A, $this->pegA1] as $pengguna) {
            $this->actingAs($pengguna)->get(route('monitoring.index'))->assertOk();
        }
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get(route('monitoring.index'))->assertRedirect(route('login'));
    }

    public function test_rekap_status_hanya_untuk_admin_tu_dan_pejabat(): void
    {
        foreach ([$this->admin, $this->tuA, $this->es1, $this->es2A] as $pengguna) {
            $this->actingAs($pengguna)->get(route('monitoring.index'))->assertViewHas('lihatSemua', true);
        }

        $this->actingAs($this->pegA1)->get(route('monitoring.index'))->assertViewHas('lihatSemua', false);
    }

    public function test_perlu_tindakan_saya_hanya_berisi_disposisi_milik_sendiri(): void
    {
        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Tugas es2A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Tugas es2B');
        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Sudah selesai', 'selesai');

        $this->actingAs($this->es2A)->get(route('monitoring.index'))
            ->assertOk()
            ->assertViewHas('disposisiSaya', function ($daftar) {
                return $daftar->count() === 1
                    && (int) $daftar->first()->penerima_id === $this->es2A->id;
            });

        $this->actingAs($this->pegA1)->get(route('monitoring.index'))
            ->assertViewHas('disposisiSaya', fn ($daftar) => $daftar->isEmpty());
    }

    public function test_rekap_excel_ditolak_untuk_pegawai(): void
    {
        $this->actingAs($this->pegA1)->get(route('monitoring.export'))->assertForbidden();
    }

    public function test_rekap_excel_bisa_diunduh_admin(): void
    {
        if (!class_exists(\ZipArchive::class)) {
            $this->markTestSkipped('Ekstensi PHP zip tidak aktif.');
        }

        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat untuk rekap');

        $this->actingAs($this->admin)->get(route('monitoring.export'))->assertOk();
    }
}
