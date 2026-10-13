<?php

namespace App\Http\Controllers;

use App\Models\AnggaranItem;
use App\Models\AnggaranPemetaan;
use App\Models\AnggaranUpload;
use App\Services\AnggaranUnitResolver;
use Illuminate\Http\Request;

/**
 * Kelola pemetaan RO -> Unit. Khusus admin (middleware 'admin' di routes).
 */
class AnggaranPemetaanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $rules = AnggaranPemetaan::query()
            ->when($q !== '', fn($x) => $x->where(function ($w) use ($q) {
                $w->where('ro_kode', 'like', "%$q%")
                    ->orWhere('keg_kode', 'like', "%$q%")
                    ->orWhere('komp_kode', 'like', "%$q%")
                    ->orWhere('sub_kode', 'like', "%$q%")
                    ->orWhere('unit', 'like', "%$q%")
                    ->orWhere('induk', 'like', "%$q%");
            }))
            // Urut per induk: D1, D2, D3, D4 dulu, lalu biro/lainnya. Di dalam induk:
            // Asdep 1..5, Sesdep, lalu aturan yang langsung ke Deputi (unit = induk).
            ->orderByRaw("CASE WHEN COALESCE(NULLIF(induk, ''), unit) LIKE 'D_' THEN 0 ELSE 1 END")
            ->orderByRaw("COALESCE(NULLIF(induk, ''), unit)")
            ->orderByRaw("CASE WHEN unit = COALESCE(NULLIF(induk, ''), unit) THEN 1 ELSE 0 END")
            ->orderBy('unit')->orderBy('keg_kode')->orderBy('ro_kode')->orderBy('komp_kode')->orderBy('sub_kode')
            ->paginate(50)->withQueryString();

        $units = AnggaranPemetaan::query()->distinct()->orderBy('unit')->pluck('unit');
        $induks = AnggaranPemetaan::query()->whereNotNull('induk')->where('induk', '!=', '')->distinct()->orderBy('induk')->pluck('induk');

        // RO yang dipecah per subkomponen / komponen (dilihat dari aturan yang sudah ada)
        $all = AnggaranPemetaan::all(['ro_kode', 'komp_kode', 'sub_kode']);
        $norm = fn($v) => strtoupper(trim((string) $v));
        $subRos = $all->filter(fn($m) => $m->sub_kode !== '')->map(fn($m) => $norm($m->ro_kode))->unique()->all();
        $kompRos = $all->filter(fn($m) => $m->sub_kode === '' && $m->komp_kode !== '')->map(fn($m) => $norm($m->ro_kode))->unique()->all();
        $level = function ($r) use ($norm, $subRos, $kompRos) {
            $ro = $norm($r->ro_kode);
            if (in_array($ro, $subRos) && $norm($r->sub_kode) !== '')
                return 'sub';
            if (in_array($ro, $kompRos) && $norm($r->komp_kode) !== '')
                return 'komp';
            return null;
        };

        // Kode di upload aktif yang belum punya pemetaan
        $unmapped = collect();
        $upload = AnggaranUpload::where('is_active', true)->first();
        if ($upload) {
            $resolver = new AnggaranUnitResolver(); // baca pemetaan terbaru
            $unmapped = AnggaranItem::where('anggaran_upload_id', $upload->id)
                ->selectRaw('keg_kode, ro_kode, ro_uraian, komp_kode, sub_kode, SUM(pagu) as pagu')
                ->groupBy('keg_kode', 'ro_kode', 'ro_uraian', 'komp_kode', 'sub_kode')
                ->get()
                ->filter(fn($r) => $resolver->resolve($r)['unit'] === AnggaranUnitResolver::BELUM)
                ->groupBy(function ($r) use ($level) {
                    $k = $r->keg_kode . '|' . $r->ro_kode;
                    return match ($level($r)) {
                        'sub' => $k . '|' . $r->sub_kode,
                        'komp' => $k . '|' . $r->komp_kode,
                        default => $k,
                    };
                })
                ->map(function ($g) use ($level) {
                    $f = $g->first();
                    $lv = $level($f);
                    return (object) [
                        'keg_kode' => $f->keg_kode,
                        'ro_kode' => $f->ro_kode,
                        'ro_uraian' => $f->ro_uraian,
                        'level' => $lv,
                        'komp_kode' => $lv ? $f->komp_kode : null,
                        'sub_kode' => $lv === 'sub' ? $f->sub_kode : null,
                        'pagu' => $g->sum('pagu'),
                        'n' => $g->count(),
                    ];
                })
                ->values();
        }

        return view('anggaran.pemetaan', compact('rules', 'units', 'induks', 'unmapped', 'q', 'upload'));
    }

    public function store(Request $request)
    {
        $d = $this->clean($request);

        // Kunci sama = ubah unitnya (bukan membuat duplikat)
        AnggaranPemetaan::updateOrCreate(
            ['keg_kode' => $d['keg_kode'], 'ro_kode' => $d['ro_kode'], 'komp_kode' => $d['komp_kode'], 'sub_kode' => $d['sub_kode']],
            ['unit' => $d['unit'], 'induk' => $d['induk'], 'sub_unit' => null]
        );

        return back()->with('status', 'Pemetaan disimpan.');
    }

    public function update(Request $request, AnggaranPemetaan $pemetaan)
    {
        $data = $request->validate([
            'unit' => ['required', 'string', 'max:50'],
            'induk' => ['nullable', 'string', 'max:50'],
        ]);

        $unit = trim($data['unit']);
        $pemetaan->update([
            'unit' => $unit,
            'induk' => $this->induk($data['induk'] ?? null, $unit),
            'sub_unit' => null,
        ]);

        return back()->with('status', 'Unit untuk ' . $pemetaan->ro_kode . ' diperbarui.');
    }

    public function destroy(AnggaranPemetaan $pemetaan)
    {
        $pemetaan->delete();

        return back()->with('status', 'Aturan dihapus. Kode terkait akan tampil "Belum dipetakan" bila tidak ada aturan lain yang cocok.');
    }

    /** Rapikan input: trim + huruf besar untuk kode; kosong disimpan '' (bukan NULL). */
    private function clean(Request $request): array
    {
        $v = $request->validate([
            'keg_kode' => ['nullable', 'string', 'max:30'],
            'ro_kode' => ['required', 'string', 'max:30'],
            'komp_kode' => ['nullable', 'string', 'max:30'],
            'sub_kode' => ['nullable', 'string', 'max:30'],
            'unit' => ['required', 'string', 'max:50'],
            'induk' => ['nullable', 'string', 'max:50'],
        ]);

        $code = fn($k) => strtoupper(trim((string) ($v[$k] ?? '')));
        $keg = $code('keg_kode');
        $komp = $code('komp_kode');
        $subKode = $code('sub_kode');

        // Resolver tidak memakai kegiatan untuk aturan komponen/subkomponen,
        // jadi dikosongkan agar tidak muncul aturan dobel.
        if ($komp !== '' || $subKode !== '') {
            $keg = '';
        }
        // Aturan subkomponen tidak memakai komponen.
        if ($subKode !== '') {
            $komp = '';
        }

        return [
            'keg_kode' => $keg,
            'ro_kode' => $code('ro_kode'),
            'komp_kode' => $komp,
            'sub_kode' => $subKode,
            'unit' => trim($v['unit']),
            'induk' => $this->induk($v['induk'] ?? null, trim($v['unit'])),
        ];
    }

    /**
     * Induk: pakai isian bila ada; kalau kosong dan nama unit berakhiran D1..D4
     * (mis. "Asdep 1 D2") otomatis jadi "D2"; selain itu NULL (= unit itu sendiri).
     */
    private function induk(?string $induk, string $unit): ?string
    {
        $induk = trim((string) $induk);
        if ($induk !== '') {
            return $induk;
        }
        return preg_match('/\b(D[1-4])$/i', $unit, $m) ? strtoupper($m[1]) : null;
    }
}