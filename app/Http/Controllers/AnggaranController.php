<?php

namespace App\Http\Controllers;

use App\Models\AnggaranItem;
use App\Models\AnggaranUpload;
use App\Services\AnggaranImportService;
use App\Support\AnggaranExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class AnggaranController extends Controller
{
    public function index()
    {

        $canUpload = (bool) auth()->user()?->isAdmin();

        return view('anggaran.index', compact('canUpload'));
    }

    public function data()
    {
        $active = AnggaranUpload::where('is_active', true)->latest('id')->first();

        if (!$active) {
            return response()->json(['upload' => null, 'items' => []]);
        }

        $items = AnggaranItem::where('anggaran_upload_id', $active->id)
            ->orderBy('id')
            ->get([
                'keg_kode',
                'keg_uraian',
                'ro_kode',
                'ro_uraian',
                'komp_kode',
                'komp_uraian',
                'sub_kode',
                'sub_uraian',
                'akun_kode',
                'akun_uraian',
                'item_kode',
                'item_uraian',
                'pagu',
                'realisasi',
            ]);

        return response()->json([
            'upload' => [
                'periode' => $active->periode,
                'satker' => $active->satker_nama,
                'file' => $active->nama_file,
                'at' => $active->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            ],
            'items' => $items,
        ]);
    }

    public function export(Request $request)
    {
        $activeUpload = AnggaranUpload::where('is_active', true)->latest('id')->first();

        $items = [];
        if ($activeUpload) {
            $items = AnggaranItem::where('anggaran_upload_id', $activeUpload->id)
                ->orderBy('id')
                ->get();
        }

        return AnggaranExcel::download(
            $items,
            $activeUpload,
            $request->only(['keg', 'ro', 'komp', 'sub', 'akun', 'q', 'level'])
        );
    }

    public function uploadForm()
    {
        $uploads = AnggaranUpload::with('uploader:id,name')->latest('id')->take(10)->get();

        return view('anggaran.upload', compact('uploads'));
    }

    public function uploadStore(Request $request, AnggaranImportService $importer)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:20480'],
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes' => 'File harus berformat .xlsx atau .xls.',
            'file.max' => 'Ukuran file maksimal 20 MB.',
        ]);

        $file = $request->file('file');

        try {
            $upload = $importer->import($file->getRealPath(), $file->getClientOriginalName(), auth()->id());
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Import anggaran gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal memproses file. Pastikan file asli dari SAKTI dan belum diubah.');
        }

        return redirect()->route('anggaran.index')
            ->with('success', "Data anggaran diperbarui: {$upload->jumlah_item} item ({$upload->periode}).");
    }
}