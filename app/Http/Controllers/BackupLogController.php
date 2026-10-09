<?php

namespace App\Http\Controllers;

use App\Exports\BackupLogsExport;
use App\Http\Requests\BackupLogRequest;
use App\Models\BackupLog;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BackupLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['jenis', 'bulan', 'cari']);

        $backupLogs = BackupLog::query()
            ->with('user')
            ->jenis($filters['jenis'] ?? null)
            ->bulan($filters['bulan'] ?? null)
            ->cari($filters['cari'] ?? null)
            ->orderByDesc('backup_date')
            ->orderByDesc('backup_time')
            ->paginate(10)
            ->withQueryString();

        // Ringkasan: backup terakhir tiap jenis pada bulan yang dipilih (default: bulan ini).
        // Format('F Y') sengaja tidak diterjemahkan karena dibaca oleh strtotime() di scopeBulan.
        $summaryMonth = $filters['bulan'] ?? null ?: now()->format('F Y');

        $latestPerJenis = BackupLog::query()
            ->bulan($summaryMonth)
            ->orderByDesc('backup_date')
            ->orderByDesc('backup_time')
            ->get()
            ->groupBy('jenis')
            ->map->first();

        // Label bulan (Bahasa Indonesia) untuk judul kartu ringkasan; kalau input bulan tidak valid, tampilkan apa adanya.
        $timestamp = strtotime('1 ' . $summaryMonth);
        $summaryLabel = $timestamp !== false
            ? Carbon::createFromTimestamp($timestamp)->locale('id')->translatedFormat('F Y')
            : $summaryMonth;

        return view('backup-logs.index', [
            'backupLogs' => $backupLogs,
            'jenisOptions' => Category::names('backup'),
            'filters' => $filters,
            'summaryLabel' => $summaryLabel,
            'latestPerJenis' => $latestPerJenis,
        ]);
    }

    public function create()
    {
        return view('backup-logs.create', [
            'jenisOptions' => Category::names('backup'),
        ]);
    }

    public function store(BackupLogRequest $request)
    {
        BackupLog::create($request->validated() + ['user_id' => auth()->id()]);

        return redirect()
            ->route('backup-logs.index')
            ->with('success', 'Catatan backup berhasil ditambahkan.');
    }

    public function edit(BackupLog $backup_log)
    {
        $jenisOptions = Category::names('backup');

        // Kalau jenis data ini sudah tidak ada di daftar, tetap tampilkan supaya tidak hilang dari form edit.
        if ($backup_log->jenis && ! $jenisOptions->contains($backup_log->jenis)) {
            $jenisOptions->push($backup_log->jenis);
        }

        return view('backup-logs.edit', [
            'backupLog' => $backup_log,
            'jenisOptions' => $jenisOptions,
        ]);
    }

    public function update(BackupLogRequest $request, BackupLog $backup_log)
    {
        $backup_log->update($request->validated());

        return redirect()
            ->route('backup-logs.index')
            ->with('success', 'Catatan backup berhasil diperbarui.');
    }

    public function destroy(BackupLog $backup_log)
    {
        $backup_log->delete();

        return redirect()
            ->route('backup-logs.index')
            ->with('success', 'Catatan backup berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['jenis', 'bulan', 'cari']);

        return Excel::download(
            new BackupLogsExport($filters),
            'log-backup-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['jenis', 'bulan', 'cari']);

        $backupLogs = BackupLog::query()
            ->with('user')
            ->jenis($filters['jenis'] ?? null)
            ->bulan($filters['bulan'] ?? null)
            ->cari($filters['cari'] ?? null)
            ->orderByDesc('backup_date')
            ->orderByDesc('backup_time')
            ->get();

        $pdf = Pdf::loadView('backup-logs.pdf', compact('backupLogs', 'filters'))->setPaper('a4', 'portrait');

        return $pdf->download('log-backup-' . now()->format('Ymd_His') . '.pdf');
    }
}
