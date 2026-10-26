<?php

namespace Tests\Feature\Persuratan;

use App\Models\Disposisi;
use App\Models\Laporan;
use App\Models\SuratMasuk;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\BuatDataPersuratan;
use Tests\TestCase;

class DisposisiDigitalPerUnitTest extends TestCase
{
    use RefreshDatabase, BuatDataPersuratan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->siapkanDataPersuratan();
    }

    /** Surat unit A yang sudah sampai ke es2A (status disposisi awal sesuai parameter). */
    private function suratUnitA(string $perihal = 'Surat unit A', string $status = 'baru'): SuratMasuk
    {
        return $this->buatSuratMasuk($this->tuA, $this->unitA, $this->es2A, $perihal, $status);
    }

    // ---------------------------------------------------------------- inbox

    public function test_inbox_hanya_berisi_disposisi_milik_penerima(): void
    {
        $this->suratUnitA('Inbox milik es2A');
        $this->buatSuratMasuk($this->tuB, $this->unitB, $this->es2B, 'Inbox milik es2B');

        $this->actingAs($this->es2A)->get(route('disposisi.index'))
            ->assertOk()
            ->assertSee('Inbox milik es2A')
            ->assertDontSee('Inbox milik es2B');
    }

    public function test_filter_status_selesai_di_inbox(): void
    {
        $this->suratUnitA('Surat masih baru');
        $this->suratUnitA('Surat sudah selesai', 'selesai');

        $this->actingAs($this->es2A)->get(route('disposisi.index', ['tab' => 'selesai']))
            ->assertOk()
            ->assertSee('Surat sudah selesai')
            ->assertDontSee('Surat masih baru');
    }

    public function test_membuka_disposisi_menandainya_dibaca(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);

        $this->actingAs($this->es2A)->get(route('disposisi.show', $akar))->assertOk();

        $akar->refresh();
        $this->assertSame('dibaca', $akar->status);
        $this->assertNotNull($akar->dibaca_at);
    }

    // ------------------------------------------------------------ hak akses

    public function test_disposisi_tidak_bisa_dibuka_orang_di_luar_rantai_atau_unit_lain(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);
        $anak = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);

        $this->actingAs($this->es2B)->get(route('disposisi.show', $akar))->assertForbidden();
        $this->actingAs($this->tuB)->get(route('disposisi.show', $akar))->assertForbidden();
        $this->actingAs($this->pegA2)->get(route('disposisi.show', $anak))->assertForbidden();
        $this->actingAs($this->pegA1)->get(route('disposisi.show', $anak))->assertOk();
    }

    public function test_tu_dan_eselon1_tidak_melihat_node_pegawai_di_pohon_disposisi(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);
        $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);

        // Eselon 2 unit terkait melihat pegawainya
        $this->actingAs($this->es2A)->get(route('disposisi.show', $akar))
            ->assertOk()
            ->assertSee($this->pegA1->name);

        // TU unit tersebut: hierarki berhenti di Eselon 2
        $this->actingAs($this->tuA)->get(route('disposisi.show', $akar))
            ->assertOk()
            ->assertDontSee($this->pegA1->name);

        $this->actingAs($this->es1)->get(route('disposisi.show', $akar))
            ->assertOk()
            ->assertDontSee($this->pegA1->name);
    }

    // ----------------------------------------------------------- meneruskan

    public function test_eselon2_meneruskan_ke_pegawai_di_unitnya_dengan_petunjuk(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);

        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegA1->id, $this->pegA2->id],
            'petunjuk' => ['Koordinasikan', 'Untuk diketahui'],
            'sifat' => 'segera',
            'tenggat_waktu' => now()->addDays(5)->toDateString(),
        ])->assertRedirect(route('disposisi.show', $akar))->assertSessionHas('success');

        $anak = Disposisi::where('parent_dispo_id', $akar->id)->get();

        $this->assertCount(2, $anak);
        $this->assertEqualsCanonicalizing(
            [$this->pegA1->id, $this->pegA2->id],
            $anak->pluck('penerima_id')->map(fn ($id) => (int) $id)->all()
        );

        foreach ($anak as $d) {
            $this->assertSame(['Koordinasikan', 'Untuk diketahui'], $d->petunjuk);
            $this->assertSame('Koordinasikan, Untuk diketahui', $d->instruksi);
            $this->assertSame('segera', $d->sifat);
            $this->assertSame('baru', $d->status);
            $this->assertSame($this->es2A->id, (int) $d->pengirim_id);
        }

        $this->assertSame('diproses', $akar->fresh()->status);
    }

    public function test_pengaturan_khusus_per_penerima_menimpa_pengaturan_umum(): void
    {
        $surat = $this->buatSuratMasuk($this->tuD, $this->unitD, $this->es1, 'Surat untuk Deputi');
        $akar = $this->akarDisposisi($surat);

        $this->actingAs($this->es1)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->es2A->id, $this->es2B->id],
            'petunjuk' => ['Edarkan'],
            'sifat' => 'biasa',
            'khusus' => [
                $this->es2A->id => [
                    'aktif' => 1,
                    'petunjuk' => ['Setuju'],
                    'instruksi' => 'Segera proses.',
                    'sifat' => 'segera',
                ],
            ],
        ])->assertSessionHas('success');

        $untukA = Disposisi::where('parent_dispo_id', $akar->id)->where('penerima_id', $this->es2A->id)->firstOrFail();
        $untukB = Disposisi::where('parent_dispo_id', $akar->id)->where('penerima_id', $this->es2B->id)->firstOrFail();

        $this->assertSame(['Setuju'], $untukA->petunjuk);
        $this->assertSame('Segera proses.', $untukA->instruksi);
        $this->assertSame('segera', $untukA->sifat);

        $this->assertSame(['Edarkan'], $untukB->petunjuk);
        $this->assertSame('biasa', $untukB->sifat);
    }

    public function test_eselon2_tidak_bisa_meneruskan_ke_pegawai_unit_lain(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);

        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegB->id],
            'petunjuk' => ['Edarkan'],
            'sifat' => 'biasa',
        ])->assertSessionHasErrors('penerima_ids');

        $this->assertSame(0, Disposisi::where('parent_dispo_id', $akar->id)->count());
        $this->assertSame('baru', $akar->fresh()->status);
    }

    public function test_eselon1_hanya_boleh_meneruskan_ke_eselon2(): void
    {
        $surat = $this->buatSuratMasuk($this->tuD, $this->unitD, $this->es1, 'Surat untuk Deputi');
        $akar = $this->akarDisposisi($surat);

        $this->actingAs($this->es1)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegA1->id],
            'petunjuk' => ['Edarkan'],
            'sifat' => 'biasa',
        ])->assertSessionHasErrors('penerima_ids');

        $this->actingAs($this->es1)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->es2A->id],
            'petunjuk' => ['Edarkan'],
            'sifat' => 'biasa',
        ])->assertSessionHas('success');
    }

    public function test_meneruskan_wajib_punya_petunjuk_atau_catatan(): void
    {
        $akar = $this->akarDisposisi($this->suratUnitA());

        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegA1->id],
            'sifat' => 'biasa',
        ])->assertSessionHasErrors('penerima_ids');

        // Catatan saja (tanpa petunjuk) sudah cukup
        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegA1->id],
            'instruksi' => 'Mohon dipelajari dan dibuat telaahan.',
            'sifat' => 'biasa',
        ])->assertSessionHas('success');
    }

    public function test_petunjuk_di_luar_daftar_standar_ditolak(): void
    {
        $akar = $this->akarDisposisi($this->suratUnitA());

        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), [
            'penerima_ids' => [$this->pegA1->id],
            'petunjuk' => ['Petunjuk karangan sendiri'],
            'sifat' => 'biasa',
        ])->assertSessionHasErrors('petunjuk.0');
    }

    public function test_disposisi_tidak_bisa_diteruskan_dua_kali(): void
    {
        $akar = $this->akarDisposisi($this->suratUnitA());
        $data = ['penerima_ids' => [$this->pegA1->id], 'petunjuk' => ['Edarkan'], 'sifat' => 'biasa'];

        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), $data)->assertSessionHas('success');
        $this->actingAs($this->es2A)->post(route('disposisi.store', $akar), $data)->assertSessionHas('error');

        $this->assertSame(1, Disposisi::where('parent_dispo_id', $akar->id)->count());
    }

    public function test_pegawai_dan_tu_tidak_bisa_meneruskan_disposisi(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);
        $anak = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);
        $data = ['penerima_ids' => [$this->pegA2->id], 'petunjuk' => ['Edarkan'], 'sifat' => 'biasa'];

        $this->actingAs($this->pegA1)->post(route('disposisi.store', $anak), $data)->assertForbidden();
        $this->actingAs($this->tuA)->post(route('disposisi.store', $akar), $data)->assertForbidden();
    }

    // -------------------------------------------------------------- laporan

    public function test_laporan_pegawai_menyelesaikan_disposisi_dan_rantai_atasnya(): void
    {
        $surat = $this->suratUnitA('Surat berlaporan', 'diproses');
        $akar = $this->akarDisposisi($surat);
        $anak = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);

        $this->actingAs($this->pegA1)->post(route('disposisi.laporan', $anak), [
            'isi' => 'Pekerjaan sudah selesai dikerjakan.',
            'drive_link' => 'https://drive.google.com/file/d/abc/view',
        ])->assertRedirect(route('disposisi.show', $anak))->assertSessionHas('success');

        $this->assertDatabaseHas('laporan', [
            'disposisi_id' => $anak->id,
            'pelapor_id' => $this->pegA1->id,
            'drive_link' => 'https://drive.google.com/file/d/abc/view',
        ]);
        $this->assertSame('selesai', $anak->fresh()->status);
        $this->assertSame('selesai', $akar->fresh()->status);
    }

    public function test_induk_baru_selesai_setelah_semua_bawahan_melapor(): void
    {
        $surat = $this->suratUnitA('Surat dua pelaksana', 'diproses');
        $akar = $this->akarDisposisi($surat);
        $untukA1 = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);
        $untukA2 = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA2);

        $this->actingAs($this->pegA1)->post(route('disposisi.laporan', $untukA1), ['isi' => 'Bagian pertama selesai.']);
        $this->assertSame('diproses', $akar->fresh()->status, 'Induk belum boleh selesai, pegawai kedua belum lapor.');

        $this->actingAs($this->pegA2)->post(route('disposisi.laporan', $untukA2), ['isi' => 'Bagian kedua selesai.']);
        $this->assertSame('selesai', $akar->fresh()->status);
    }

    public function test_laporan_wajib_diisi_dan_link_harus_valid(): void
    {
        $surat = $this->suratUnitA();
        $anak = $this->buatDisposisi($surat, $this->akarDisposisi($surat), $this->es2A, $this->pegA1);

        $this->actingAs($this->pegA1)->post(route('disposisi.laporan', $anak), ['isi' => ''])
            ->assertSessionHasErrors('isi', null, 'laporan');

        $this->actingAs($this->pegA1)->post(route('disposisi.laporan', $anak), ['isi' => 'Selesai', 'drive_link' => 'bukan-url'])
            ->assertSessionHasErrors('drive_link', null, 'laporan');

        $this->assertSame(0, Laporan::count());
    }

    public function test_hanya_penerima_yang_boleh_melapor(): void
    {
        $surat = $this->suratUnitA();
        $akar = $this->akarDisposisi($surat);
        $anak = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);

        $this->actingAs($this->pegA2)->post(route('disposisi.laporan', $anak), ['isi' => 'Bukan tugas saya'])->assertForbidden();
        $this->actingAs($this->tuA)->post(route('disposisi.laporan', $akar), ['isi' => 'TU tidak melapor'])->assertForbidden();
        $this->actingAs($this->es1)->post(route('disposisi.laporan', $akar), ['isi' => 'Bukan penerima'])->assertForbidden();

        $this->assertSame(0, Laporan::count());
    }

    public function test_eselon2_boleh_melapor_hanya_bila_belum_meneruskan(): void
    {
        $belum = $this->akarDisposisi($this->suratUnitA('Dikerjakan sendiri', 'dibaca'));
        $sudah = $this->akarDisposisi($this->suratUnitA('Sudah diteruskan', 'diproses'));

        $this->actingAs($this->es2A)->post(route('disposisi.laporan', $belum), ['isi' => 'Saya kerjakan sendiri.'])
            ->assertSessionHas('success');
        $this->assertSame('selesai', $belum->fresh()->status);

        $this->actingAs($this->es2A)->post(route('disposisi.laporan', $sudah), ['isi' => 'Terlambat.'])
            ->assertForbidden();
    }

    public function test_identitas_pelapor_pegawai_disamarkan_kecuali_untuk_unitnya(): void
    {
        $surat = $this->suratUnitA('Surat dengan laporan', 'diproses');
        $akar = $this->akarDisposisi($surat);
        $anak = $this->buatDisposisi($surat, $akar, $this->es2A, $this->pegA1);

        $this->actingAs($this->pegA1)->post(route('disposisi.laporan', $anak), ['isi' => 'Isi laporan rahasia unit.']);

        // Eselon 1 & TU: isi laporan terlihat, tetapi nama pegawai diganti nama Eselon 2 unitnya
        foreach ([$this->es1, $this->tuA] as $pengguna) {
            $this->actingAs($pengguna)->get(route('disposisi.show', $akar))
                ->assertOk()
                ->assertSee('Isi laporan rahasia unit.')
                ->assertDontSee($this->pegA1->name);
        }

        // Eselon 2 di unit yang sama dan admin melihat nama aslinya
        foreach ([$this->es2A, $this->admin] as $pengguna) {
            $this->actingAs($pengguna)->get(route('disposisi.show', $akar))
                ->assertOk()
                ->assertSee($this->pegA1->name);
        }
    }
}
