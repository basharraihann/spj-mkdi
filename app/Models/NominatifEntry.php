<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUnit;
use Illuminate\Database\Eloquent\Model;

class NominatifEntry extends Model
{
    use BelongsToUnit;

    protected $table = 'nominatif_entries';

    protected $fillable = [
        'unit_id',
        'uraian_kegiatan',
        'tanggal',
        'provinsi',
        'kota',
        'ppk_id',
        'bendahara_id',
        'jenis_detail',          // baru
        'penanggung_jawab_id',   // baru
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function peserta()
    {
        return $this->hasMany(NominatifPeserta::class, 'nominatif_entry_id')->orderBy('urutan');
    }

    public function ppk()
    {
        return $this->belongsTo(Pegawai::class, 'ppk_id');
    }

    public function bendahara()
    {
        return $this->belongsTo(Pegawai::class, 'bendahara_id');
    }

    // ===== BARU: mulai dari sini =====

    public function items()
    {
        return $this->hasMany(NominatifItem::class, 'nominatif_entry_id')->orderBy('urutan');
    }

    public function penanggungJawab()
    {
        return $this->belongsTo(Pegawai::class, 'penanggung_jawab_id');
    }

    public function totalSubtotal(): int
    {
        return (int) $this->items->sum(fn($i) => $i->subtotal);
    }
    public function totalPpn(): int
    {
        return (int) $this->items->sum(fn($i) => $i->ppn);
    }
    public function totalPph22(): int
    {
        return (int) $this->items->sum(fn($i) => $i->pph22);
    }
    public function totalPph23(): int
    {
        return (int) $this->items->sum(fn($i) => $i->pph23);
    }
    public function totalAkhir(): int
    {
        return (int) $this->items->sum(fn($i) => $i->total);
    }

    // ===== BARU: sampai sini =====

    public function totalBruto(): int
    {
        return (int) $this->peserta->sum(fn($p) => $p->bruto);
    }

    public function totalPajak(): int
    {
        return (int) $this->peserta->sum(fn($p) => $p->pajak);
    }

    public function totalNetto(): int
    {
        return $this->totalBruto() - $this->totalPajak();
    }
}
