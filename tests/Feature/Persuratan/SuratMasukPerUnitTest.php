<?php

namespace Tests\Feature\Persuratan;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\SuratMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuatDataPersuratan;
use Tests\TestCase;

class SuratMasukPerUnitTest extends TestCase
{
    use RefreshDatabase, BuatDataPersuratan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->siapkanDataPersuratan();
    }

    private function dataForm(array $timpa = []): array
    {
        return array_merge([
            'nomor_surat' => 'B-77/UJI/2026',
            'asal_surat' => 'Kementerian Uji',
            'perihal' => 'Perihal surat dari form uji',
            'tanggal_surat' => now()->toDateString(),
            'tanggal_diterima' => now()->toDateString(),
        ], $timpa);
    }

    public function test_tu_hanya_melihat_surat_milik_unitnya(): void
    {
        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat khusus unit A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Surat khusus unit B');

        $this->actingAs($this->tuA)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('Surat khusus unit A')
            ->assertDontSee('Surat khusus unit B');

        $this->actingAs($this->tuB)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('Surat khusus unit B')
            ->assertDontSee('Surat khusus unit A');
    }

    public function test_tu_unit_lain_tidak_bisa_membuka_detail_surat(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat unit A');

        $this->actingAs($this->tuB)->get(route('surat-masuk.show', $surat))->assertForbidden();
        $this->actingAs($this->tuA)->get(route('surat-masuk.show', $surat))->assertOk();
    }

    public function test_admin_dan_eselon1_melihat_semua_unit(): void
    {
        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat unit A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Surat unit B');

        foreach ([$this->admin, $this->es1] as $pengguna) {
            $this->actingAs($pengguna)->get(route('surat-masuk.index'))
                ->assertOk()
                ->assertSee('Surat unit A')
                ->assertSee('Surat unit B');
        }
    }

    public function test_eselon2_hanya_melihat_surat_yang_melibatkannya(): void
    {
        $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat unit A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Surat unit B');

        $this->actingAs($this->es2A)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('Surat unit A')
            ->assertDontSee('Surat unit B');
    }

    public function test_pegawai_hanya_melihat_surat_yang_didisposisikan_kepadanya(): void
    {
        $suratA = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat unit A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Surat unit B');

        $this->buatDisposisi($suratA, $this->akarDisposisi($suratA), $this->es2A, $this->pegA1);

        $this->actingAs($this->pegA1)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('Surat unit A')
            ->assertDontSee('Surat unit B');

        // Pegawai lain di unit yang sama, tidak menerima disposisi, tidak melihat apa pun
        $this->actingAs($this->pegA2)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertDontSee('Surat unit A');
    }

    public function test_tu_menginput_surat_otomatis_menuju_pejabat_di_unitnya(): void
    {
        $this->actingAs($this->tuA)
            ->post(route('surat-masuk.store'), $this->dataForm(['perihal' => 'Surat baru unit A']))
            ->assertRedirect(route('surat-masuk.index'));

        $surat = SuratMasuk::where('perihal', 'Surat baru unit A')->firstOrFail();

        $this->assertSame($this->unitA->id, (int) $surat->unit_id);
        $this->assertSame($this->tuA->id, (int) $surat->created_by_id);
        $this->assertSame('eselon2', $surat->jalur_tujuan);
        $this->assertMatchesRegularExpression('#^AG-\d{3}/D2/[IVX]+/\d{4}$#', $surat->nomor_agenda);

        $akar = $this->akarDisposisi($surat);
        $this->assertSame($this->es2A->id, (int) $akar->penerima_id);
        $this->assertSame($this->tuA->id, (int) $akar->pengirim_id);
        $this->assertSame('baru', $akar->status);
    }

    public function test_surat_dari_tu_deputi_menuju_eselon1_dengan_jalur_deputi(): void
    {
        $this->actingAs($this->tuD)
            ->post(route('surat-masuk.store'), $this->dataForm(['perihal' => 'Surat untuk Deputi']))
            ->assertRedirect(route('surat-masuk.index'));

        $surat = SuratMasuk::where('perihal', 'Surat untuk Deputi')->firstOrFail();

        $this->assertSame('deputi', $surat->jalur_tujuan);
        $this->assertSame($this->es1->id, (int) $this->akarDisposisi($surat)->penerima_id);
    }

    public function test_input_ditolak_bila_unit_tu_belum_punya_pejabat(): void
    {
        $this->actingAs($this->tuC)
            ->post(route('surat-masuk.store'), $this->dataForm(['perihal' => 'Surat unit tanpa pejabat']))
            ->assertSessionHasErrors('unit_id');

        $this->assertDatabaseMissing('surat_masuk', ['perihal' => 'Surat unit tanpa pejabat']);
    }

    public function test_admin_wajib_memilih_unit_tujuan(): void
    {
        $this->actingAs($this->admin)
            ->post(route('surat-masuk.store'), $this->dataForm())
            ->assertSessionHasErrors('unit_id');

        $this->actingAs($this->admin)
            ->post(route('surat-masuk.store'), $this->dataForm(['perihal' => 'Surat admin ke unit B', 'unit_id' => $this->unitB->id]))
            ->assertRedirect(route('surat-masuk.index'));

        $surat = SuratMasuk::where('perihal', 'Surat admin ke unit B')->firstOrFail();
        $this->assertSame($this->unitB->id, (int) $surat->unit_id);
        $this->assertSame($this->es2B->id, (int) $this->akarDisposisi($surat)->penerima_id);
    }

    public function test_pegawai_dan_pejabat_tidak_boleh_menginput_surat_masuk(): void
    {
        foreach ([$this->pegA1, $this->es2A, $this->es1] as $pengguna) {
            $this->actingAs($pengguna)
                ->post(route('surat-masuk.store'), $this->dataForm())
                ->assertForbidden();
        }
    }

    public function test_tanggal_diterima_tidak_boleh_sebelum_tanggal_surat(): void
    {
        $this->actingAs($this->tuA)
            ->post(route('surat-masuk.store'), $this->dataForm([
                'tanggal_surat' => now()->toDateString(),
                'tanggal_diterima' => now()->subDay()->toDateString(),
            ]))
            ->assertSessionHasErrors('tanggal_diterima');
    }

    public function test_tu_bisa_mengubah_surat_selama_belum_dibaca_penerima(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Perihal lama');
        $nomorAgenda = $surat->nomor_agenda;

        $this->actingAs($this->tuA)
            ->put(route('surat-masuk.update', $surat), $this->dataForm(['perihal' => 'Perihal baru']))
            ->assertRedirect(route('surat-masuk.index'));

        $surat->refresh();
        $this->assertSame('Perihal baru', $surat->perihal);
        $this->assertSame($nomorAgenda, $surat->nomor_agenda, 'Nomor agenda tidak boleh berubah saat edit.');
    }

    public function test_tu_tidak_bisa_mengubah_surat_yang_sudah_dibaca(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Perihal lama', 'dibaca');

        $this->actingAs($this->tuA)
            ->get(route('surat-masuk.edit', $surat))
            ->assertRedirect(route('surat-masuk.index'))
            ->assertSessionHas('error');

        $this->actingAs($this->tuA)
            ->put(route('surat-masuk.update', $surat), $this->dataForm(['perihal' => 'Dicoba diubah']))
            ->assertRedirect(route('surat-masuk.index'))
            ->assertSessionHas('error');

        $this->assertSame('Perihal lama', $surat->fresh()->perihal);
    }

    public function test_tu_unit_lain_tidak_bisa_mengubah_atau_menghapus_surat(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat unit A');

        $this->actingAs($this->tuB)->get(route('surat-masuk.edit', $surat))->assertForbidden();
        $this->actingAs($this->tuB)->delete(route('surat-masuk.destroy', $surat))->assertForbidden();

        $this->assertDatabaseHas('surat_masuk', ['id' => $surat->id]);
    }

    public function test_tu_bisa_menghapus_surat_yang_masih_baru_beserta_disposisinya(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat akan dihapus');

        $this->actingAs($this->tuA)
            ->delete(route('surat-masuk.destroy', $surat))
            ->assertRedirect(route('surat-masuk.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('surat_masuk', ['id' => $surat->id]);
        $this->assertSame(0, Disposisi::where('surat_masuk_id', $surat->id)->count());
    }

    public function test_surat_yang_sudah_punya_laporan_tidak_bisa_dihapus_siapa_pun(): void
    {
        $surat = $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, 'Surat berlaporan');
        $anak = $this->buatDisposisi($surat, $this->akarDisposisi($surat), $this->es2A, $this->pegA1, 'selesai');

        Laporan::create([
            'disposisi_id' => $anak->id,
            'pelapor_id' => $this->pegA1->id,
            'isi' => 'Sudah dikerjakan.',
        ]);

        foreach ([$this->admin, $this->tuA] as $pengguna) {
            $this->actingAs($pengguna)
                ->delete(route('surat-masuk.destroy', $surat))
                ->assertRedirect(route('surat-masuk.index'))
                ->assertSessionHas('error');
        }

        $this->assertDatabaseHas('surat_masuk', ['id' => $surat->id]);
    }
}
