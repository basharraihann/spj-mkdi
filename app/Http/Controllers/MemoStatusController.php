<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\MemoEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Ubah status anggaran satu memo.
 * {memo} memakai id yang sama dengan di MemoController@index:
 *   entry-12            -> memo mandiri (memo_entries)
 *   agenda-3-pns        -> memo PNS milik agenda 3
 *   agenda-3-non_pns    -> memo Non PNS milik agenda 3
 */
class MemoStatusController extends Controller
{
    private const STATUS = ['outstanding', 'sakti', 'sp2d', 'batal'];

    /** Perpindahan yang diizinkan dari status saat ini. */
    private const BOLEH = [
        'outstanding' => ['sakti', 'batal'],
        'sakti'       => ['sp2d', 'outstanding', 'batal'],
        'sp2d'        => ['sakti', 'batal'],
        'batal'       => ['outstanding'],
    ];

    /** Perpindahan "maju" yang normal. Selain ini (mundur / batal) wajib isi alasan. */
    private const MAJU = ['outstanding' => 'sakti', 'sakti' => 'sp2d'];

    public function update(Request $request, string $memo): RedirectResponse
    {
        $data = $request->validate([
            'status'  => ['required', Rule::in(self::STATUS)],
            'tanggal' => ['nullable', 'date'],
            'alasan'  => ['nullable', 'string', 'max:255'],
        ]);

        [$tipe, $id, $jenis] = $this->parse($memo);
        $ke = $data['status'];
        $tanggal = $data['tanggal'] ?? now()->toDateString();

        $kolom = $tipe === 'entry'
            ? ['status' => 'status_anggaran', 'sakti' => 'tgl_sakti', 'sp2d' => 'tgl_sp2d']
            : ['status' => "status_{$jenis}", 'sakti' => "tgl_sakti_{$jenis}", 'sp2d' => "tgl_sp2d_{$jenis}"];

        try {
            DB::transaction(function () use ($tipe, $id, $jenis, $ke, $tanggal, $data, $kolom) {
                $class = $tipe === 'entry' ? MemoEntry::class : Agenda::class;
                $row = $class::query()->lockForUpdate()->findOrFail($id);

                $dari = $row->{$kolom['status']} ?: 'outstanding';

                if ($dari === $ke) {
                    return;
                }

                if (!in_array($ke, self::BOLEH[$dari] ?? [], true)) {
                    throw new \DomainException("Status tidak bisa langsung diubah dari {$dari} ke {$ke}.");
                }

                $maju = (self::MAJU[$dari] ?? null) === $ke;
                if (!$maju && blank($data['alasan'] ?? null)) {
                    throw new \DomainException('Alasan wajib diisi untuk membatalkan atau mundur status.');
                }

                $row->{$kolom['status']} = $ke;

                if ($ke === 'sakti') {
                    $row->{$kolom['sakti']} = $row->{$kolom['sakti']} ?? $tanggal;
                    $row->{$kolom['sp2d']} = null;
                } elseif ($ke === 'sp2d') {
                    $row->{$kolom['sakti']} = $row->{$kolom['sakti']} ?? $tanggal;
                    $row->{$kolom['sp2d']} = $tanggal;
                } elseif ($ke === 'outstanding') {
                    $row->{$kolom['sakti']} = null;
                    $row->{$kolom['sp2d']} = null;
                }

                $row->save();

                DB::table('memo_status_logs')->insert([
                    'memo_type'  => $tipe,
                    'memo_id'    => $id,
                    'jenis'      => $jenis,
                    'dari'       => $dari,
                    'ke'         => $ke,
                    'alasan'     => $data['alasan'] ?? null,
                    'user_id'    => $request->user()?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
        } catch (\DomainException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('success', 'Status memo diperbarui.');
    }

    /** @return array{0:string,1:int,2:?string} [tipe, id, jenis] */
    private function parse(string $memo): array
    {
        if (preg_match('/^entry-(\d+)$/', $memo, $m)) {
            return ['entry', (int) $m[1], null];
        }

        if (preg_match('/^agenda-(\d+)-(pns|non_pns)$/', $memo, $m)) {
            return ['agenda', (int) $m[1], $m[2]];
        }

        abort(404);
    }
}
