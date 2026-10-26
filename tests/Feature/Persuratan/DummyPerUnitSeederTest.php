<?php

namespace Tests\Feature\Persuratan;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Database\Seeders\DummyPerUnitSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuatDataPersuratan;
use Tests\TestCase;

class DummyPerUnitSeederTest extends TestCase
{
    use RefreshDatabase, BuatDataPersuratan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->siapkanDataPersuratan();
        $this->seed(DummyPerUnitSeeder::class);
    }

    public function test_setiap_unit_ber_tu_dan_berpejabat_mendapat_tiga_surat_masuk_miliknya(): void
    {
        $peta = [
            [$this->unitA, $this->tuA, $this->es2A],
            [$this->unitB, $this->tuB, $this->es2B],
            [$this->unitD, $this->tuD, $this->es1],
        ];

        foreach ($peta as [$unit, $tu, $pejabat]) {
            $surat = SuratMasuk::where('unit_id', $unit->id)->get();

            $this->assertCount(3, $surat, "Unit {$unit->kode} harus punya 3 surat masuk dummy.");
            $this->assertEqualsCanonicalizing(['baru', 'didisposisi', 'selesai'], $surat->pluck('status')->all());
            $this->assertTrue($surat->every(fn ($s) => (int) $s->created_by_id === $tu->id));

            foreach ($surat as $s) {
                $akar = $this->akarDisposisi($s);
                $this->assertSame($tu->id, (int) $akar->pengirim_id);
                $this->assertSame($pejabat->id, (int) $akar->penerima_id, "Disposisi awal unit {$unit->kode} harus ke pejabat unitnya.");
            }
        }
    }

    public function test_unit_tanpa_pejabat_dilewati(): void
    {
        $this->assertSame(0, SuratMasuk::where('unit_id', $this->unitC->id)->count());
        $this->assertSame(0, SuratKeluar::where('created_by', $this->tuC->id)->count());
    }

    public function test_rantai_disposisi_sesuai_aturan_hierarki(): void
    {
        // Unit A: TU -> Eselon 2 -> pegawai unit A
        $selesaiA = SuratMasuk::where('unit_id', $this->unitA->id)->where('status', 'selesai')->firstOrFail();
        $this->assertSame(
            [$this->es2A->id, $this->pegA1->id],
            Disposisi::where('surat_masuk_id', $selesaiA->id)->orderBy('id')->pluck('penerima_id')->map(fn ($i) => (int) $i)->all()
        );

        // Unit Deputi: TU -> Eselon 1 -> Eselon 2 -> pegawai
        $selesaiD = SuratMasuk::where('unit_id', $this->unitD->id)->where('status', 'selesai')->firstOrFail();
        $this->assertSame(
            [$this->es1->id, $this->es2A->id, $this->pegA1->id],
            Disposisi::where('surat_masuk_id', $selesaiD->id)->orderBy('id')->pluck('penerima_id')->map(fn ($i) => (int) $i)->all()
        );

        // Surat yang masih baru berhenti di pejabat penerima awal
        $baruA = SuratMasuk::where('unit_id', $this->unitA->id)->where('status', 'baru')->firstOrFail();
        $this->assertSame(1, Disposisi::where('surat_masuk_id', $baruA->id)->count());
    }

    public function test_surat_selesai_punya_laporan_dan_semua_disposisinya_selesai(): void
    {
        $this->assertSame(3, Laporan::count(), 'Satu laporan per unit untuk surat yang selesai.');

        SuratMasuk::where('status', 'selesai')->get()->each(function ($surat) {
            $this->assertSame(
                0,
                Disposisi::where('surat_masuk_id', $surat->id)->where('status', '!=', 'selesai')->count()
            );
        });
    }

    public function test_setiap_tu_punya_tiga_surat_keluar_dengan_status_berbeda(): void
    {
        foreach ([$this->tuA, $this->tuB, $this->tuD] as $tu) {
            $dokumen = SuratKeluar::where('created_by', $tu->id)->get();

            $this->assertCount(3, $dokumen);
            $this->assertEqualsCanonicalizing(['draft', 'diajukan', 'terbit'], $dokumen->pluck('status_persetujuan')->all());
        }
    }

    public function test_menjalankan_seeder_dua_kali_tidak_menggandakan_data(): void
    {
        $sebelum = [SuratMasuk::count(), Disposisi::count(), Laporan::count(), SuratKeluar::count()];

        $this->seed(DummyPerUnitSeeder::class);

        $this->assertSame($sebelum, [SuratMasuk::count(), Disposisi::count(), Laporan::count(), SuratKeluar::count()]);
    }

    public function test_data_dummy_tampil_sesuai_unit_masing_masing(): void
    {
        $this->actingAs($this->tuA)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('(' . $this->unitA->kode . ')')
            ->assertDontSee('(' . $this->unitB->kode . ')')
            ->assertDontSee('(' . $this->unitD->kode . ')');

        $this->actingAs($this->tuB)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('(' . $this->unitB->kode . ')')
            ->assertDontSee('(' . $this->unitA->kode . ')');

        // Eselon 2 hanya melihat surat yang melintasi dirinya (es2B hanya terlibat di surat unit B)
        $this->actingAs($this->es2B)->get(route('surat-masuk.index'))
            ->assertOk()
            ->assertSee('(' . $this->unitB->kode . ')')
            ->assertDontSee('(' . $this->unitA->kode . ')');

        // Admin dan Eselon 1 melihat semuanya
        foreach ([$this->admin, $this->es1] as $pengguna) {
            $this->actingAs($pengguna)->get(route('surat-masuk.index'))
                ->assertOk()
                ->assertSee('(' . $this->unitA->kode . ')')
                ->assertSee('(' . $this->unitB->kode . ')');
        }
    }
}
