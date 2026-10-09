<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\BackupLog;
use App\Models\Category;
use App\Models\Issue;
use App\Models\MonthlyReport;
use App\Models\ResetRequest;
use App\Models\ServerCheck;
use App\Models\ServiceRequest;
use App\Support\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class MonthlyReportController extends Controller
{
    public function index(Request $request)
    {
        Carbon::setLocale('id');

        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $preview = $this->buildReportData($bulan, $tahun);

        // Status persetujuan periode ini + apakah data berubah sejak diajukan/disetujui.
        $approval = MonthlyReport::forPeriod($bulan, $tahun)->first();
        $snapshot = MonthlyReport::snapshotFor($bulan, $tahun);
        $dataChanged = $approval && ! $approval->isCurrent($snapshot['fingerprint']);

        return view('reports.index', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'summary' => $preview['summary'],
            'periodLabel' => $preview['periodLabel'],
            'approval' => $approval,
            'dataChanged' => $dataChanged,
            'hasSignature' => Signature::exists(auth()->user()->signature),
        ]);
    }

    /**
     * Pratinjau laporan: PDF yang sama dengan download, tapi dibuka langsung di browser (inline).
     */
    public function preview(Request $request)
    {
        return $this->pdf($request, true);
    }

    public function pdf(Request $request, bool $inline = false)
    {
        // Render PDF bisa agak lama di server lokal (XAMPP), jadi batas waktunya dilonggarkan.
        @set_time_limit(180);

        // Paksa locale Indonesia untuk format tanggal/bulan, supaya tidak bergantung
        // ke pengaturan APP_LOCALE di .env (jadi tetap "Oktober", bukan "October").
        Carbon::setLocale('id');

        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);

        $data = $this->buildReportData($bulan, $tahun);

        $appSetting = AppSetting::current();
        $logoBase64 = null;

        // Logo khusus laporan PDF (terpisah dari logo website di sidebar/login).
        // Diperkecil dulu supaya proses render PDF tidak lambat.
        if ($appSetting->report_logo && Storage::disk('public')->exists($appSetting->report_logo)) {
            $logoBase64 = $this->logoDataUri(Storage::disk('public')->path($appSetting->report_logo));
        }

        // Persetujuan: tanda tangan hanya ditampilkan kalau masih berlaku untuk data terbaru.
        // Selain itu laporan diberi watermark DRAFT; "CONFIDENTIAL" hanya untuk laporan yang sudah di-ACC.
        $approval = MonthlyReport::forPeriod($bulan, $tahun)->first();
        $valid = $approval && $approval->isCurrent(MonthlyReport::snapshotFor($bulan, $tahun)['fingerprint']);
        // Laporan yang ditolak belum punya pengesahan, jadi diperlakukan seperti belum ada tanda tangan.
        $approval = ($valid && $approval->status !== MonthlyReport::REJECTED) ? $approval : null;
        $isFinal = $approval && $approval->status === MonthlyReport::APPROVED;

        $viewData = [
            'summary' => $data['summary'],
            'periodLabel' => $data['periodLabel'],
            'appSetting' => $appSetting,
            'logoBase64' => $logoBase64,
            'approval' => $approval,
            'isFinal' => $isFinal,
            'preparedSig' => $approval && in_array($approval->status, [MonthlyReport::SUBMITTED, MonthlyReport::APPROVED], true)
                ? Signature::dataUri($approval->prepared_signature) : null,
            'reviewedSig' => $isFinal ? Signature::dataUri($approval->reviewed_signature) : null,
            'preparedBy' => $approval?->prepared_name ?? auth()->user()->name ?? '-',
            'docNumber' => 'FORM-LOGACT-001',
            'docVersion' => '1.0',
        ];

        // 1. Render cover (polos, tanpa kop dokumen/watermark) sebagai PDF terpisah.
        $coverPdf = Pdf::loadView('reports.pdf_cover', $viewData)->setPaper('a4', 'portrait');

        // 2. Render isi laporan (Kontrol Dokumen s/d section Incident/Request/Reset, dengan kop & watermark).
        //    Section list dipisah per jenis (Incident, Request, Reset, Backup) dan diberi nomor berurutan mulai 3.
        //    Jenis yang tidak ada datanya dilewati, jadi nomor berikutnya otomatis maju.
        $sections = $this->buildSections($data['items'], $data['periodLabel'], $data['backupLogs'], $data['serverChecks']);

        $render = fn (array $secs, array $extra = []) => Pdf::loadView(
            'reports.pdf_content',
            array_merge($viewData, ['sections' => $secs, 'tocPages' => []], $extra)
        )->setPaper('a4', 'portrait');

        // Pengukuran jumlah halaman dilakukan tanpa logo (logo tidak mempengaruhi jumlah halaman),
        // jadi jauh lebih cepat.
        $countPages = function (array $secs) use ($render): int {
            $pdf = $render($secs, ['logoBase64' => null, 'preparedSig' => null, 'reviewedSig' => null]);
            $pdf->output();

            return $pdf->getDomPDF()->getCanvas()->get_page_count();
        };

        // Halaman awal tiap section = 1 (cover) + jumlah halaman isi sebelum section itu + 1.
        // Total halaman = 1 (cover) + jumlah halaman isi.
        $tocPages = [];
        foreach (array_keys($sections) as $i) {
            $tocPages[$i] = 1 + $countPages(array_slice($sections, 0, $i)) + 1;
        }
        $totalPages = 1 + $countPages($sections);

        $contentPdf = $render($sections, ['tocPages' => $tocPages, 'totalPages' => $totalPages]);

        $coverPath = storage_path('app/tmp_cover_' . uniqid() . '.pdf');
        $contentPath = storage_path('app/tmp_content_' . uniqid() . '.pdf');

        $filename = 'laporan-bulanan-' . Carbon::create($tahun, $bulan, 1)->format('Ym') . '.pdf';

        try {
            file_put_contents($coverPath, $coverPdf->output());
            file_put_contents($contentPath, $contentPdf->output());

            // 3. Gabungkan keduanya jadi 1 PDF, cover di depan lalu isi laporan.
            $merged = new Fpdi();

            foreach ([$coverPath, $contentPath] as $file) {
                $pageCount = $merged->setSourceFile($file);

                for ($i = 1; $i <= $pageCount; $i++) {
                    $templateId = $merged->importPage($i);
                    $size = $merged->getTemplateSize($templateId);
                    $merged->AddPage($size['orientation'], [$size['width'], $size['height']]);
                    $merged->useTemplate($templateId);
                }
            }

            $output = $merged->Output('S', $filename);
        } finally {
            // Selalu bersihkan file sementara, walaupun terjadi error.
            @unlink($coverPath);
            @unlink($contentPath);
        }

        return response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
        ]);
    }

    /**
     * Baca file logo lalu kembalikan sebagai data URI PNG yang sudah diperkecil
     * (maks. 500px lebar). Transparansi PNG tetap dipertahankan.
     */
    protected function logoDataUri(string $path, int $maxWidth = 500): string
    {
        $raw = file_get_contents($path);
        $info = @getimagesizefromstring($raw);

        if (! $info || ! function_exists('imagecreatefromstring')) {
            return 'data:' . ($info['mime'] ?? 'image/png') . ';base64,' . base64_encode($raw);
        }

        [$w, $h] = $info;
        $src = @imagecreatefromstring($raw);

        if (! $src) {
            return 'data:' . $info['mime'] . ';base64,' . base64_encode($raw);
        }

        $nw = min($w, $maxWidth);
        $nh = (int) round($h * $nw / $w);

        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagefill($dst, 0, 0, imagecolorallocatealpha($dst, 0, 0, 0, 127));
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        ob_start();
        imagepng($dst, null, 6);
        $png = ob_get_clean();

        return 'data:image/png;base64,' . base64_encode($png);
    }

    /**
     * Pecah daftar gabungan menjadi section Incident / Request / Reset / Backup dengan nomor
     * berurutan (mulai 3). Section tanpa data dilewati. Kalau semuanya kosong, tetap tampil 1 section
     * dengan pesan "Tidak ada data".
     */
    protected function buildSections($items, string $periodLabel, $backupLogs = null, $serverChecks = null): array
    {
        $defs = [
            ['sumber' => 'Log Isu', 'title' => 'Incident List Summary'],
            ['sumber' => 'Log Permintaan', 'title' => 'Request List Summary'],
            ['sumber' => 'Permohonan Reset', 'title' => 'Reset List Summary'],
        ];

        $sections = [];
        $no = 3;

        foreach ($defs as $def) {
            $subset = $items->where('sumber', $def['sumber'])->values();

            if ($subset->isEmpty()) {
                continue;
            }

            $sections[] = [
                'no' => $no++,
                'title' => $def['title'] . ' ' . $periodLabel,
                'items' => $subset,
            ];
        }

        // Section Backup Data: tabel ringkas per tanggal (kolom = jenis backup, isi = jam backup), supaya tidak panjang.
        if ($backupLogs && $backupLogs->isNotEmpty()) {
            $sections[] = [
                'no' => $no++,
                'type' => 'backup',
                'title' => 'Backup Data Summary ' . $periodLabel,
                'items' => $this->buildBackupMatrix($backupLogs),
            ];
        }

        // Section Monitoring Ruang Server: tabel ringkas per tanggal (status tiap jam pemeriksaan), portrait seperti section lain.
        if ($serverChecks && $serverChecks->isNotEmpty()) {
            $sections[] = [
                'no' => $no++,
                'type' => 'kontrol',
                'title' => 'Monitoring Ruang Server Summary ' . $periodLabel,
                'items' => $serverChecks,
            ];
        }

        if (empty($sections)) {
            $sections[] = [
                'no' => 3,
                'title' => 'Incident, Request & Reset List Summary ' . $periodLabel,
                'items' => collect(),
            ];
        }

        return $sections;
    }

    /**
     * Gabungkan data Log Isu + Log Permintaan + Permohonan Reset untuk 1 bulan (Backup dikembalikan terpisah)
     * jadi satu daftar yang seragam kolomnya, siap ditampilkan/di-export.
     */
    /**
     * Ubah daftar backup 1 bulan jadi tabel ringkas (pivot):
     *   baris  = tanggal yang ada backup-nya (maks. sebanyak hari dalam sebulan)
     *   kolom  = jenis backup, isi sel = jam backup ("17.00"; kalau lebih dari 1 di hari yang sama: "08.00, 17.00")
     *   footer = jumlah backup per jenis
     *
     * @return array{jenis: array<int,string>, rows: array<int,array>, totals: array<string,int>}
     */
    protected function buildBackupMatrix($backupLogs): array
    {
        // Kolom: jenis di master (urut sesuai Kelola Kategori) + jenis lama yang masih ada di data tapi sudah dihapus dari master.
        $jenis = Category::names('backup')->merge($backupLogs->pluck('jenis'))->unique()->values();

        $rows = $backupLogs
            ->groupBy(fn (BackupLog $b) => $b->backup_date->format('Y-m-d'))
            ->sortKeys()
            ->map(function ($group, $date) use ($jenis) {
                return [
                    'date_label' => Carbon::parse($date)->locale('id')->translatedFormat('d F Y'),
                    'cells' => $jenis->mapWithKeys(fn ($j) => [
                        $j => $group->where('jenis', $j)->sortBy('backup_time')->map->time_label->implode(', '),
                    ])->all(),
                ];
            })
            ->values()
            ->all();

        $totals = $jenis->mapWithKeys(fn ($j) => [$j => $backupLogs->where('jenis', $j)->count()])->all();

        return ['jenis' => $jenis->all(), 'rows' => $rows, 'totals' => $totals];
    }

    protected function buildReportData(int $bulan, int $tahun): array
    {
        $periodStart = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $periodLabel = $periodStart->locale('id')->translatedFormat('F Y');

        $issues = Issue::whereYear('occurred_at', $tahun)->whereMonth('occurred_at', $bulan)->get();
        $requests = ServiceRequest::whereYear('occurred_at', $tahun)->whereMonth('occurred_at', $bulan)->get();
        $resetRequests = ResetRequest::whereYear('requested_at', $tahun)->whereMonth('requested_at', $bulan)->get();
        $backupLogs = BackupLog::whereYear('backup_date', $tahun)->whereMonth('backup_date', $bulan)
            ->orderBy('backup_date')
            ->orderBy('backup_time')
            ->get();

        // Form Kontrol Server & Jaringan (jadi tabel ringkas "Monitoring Ruang Server" di laporan).
        $serverChecks = ServerCheck::with('entries')
            ->whereYear('check_date', $tahun)->whereMonth('check_date', $bulan)
            ->orderBy('check_date')
            ->get();

        $items = collect();

        foreach ($issues as $issue) {
            $items->push([
                'tanggal' => $issue->occurred_at,
                'sumber' => 'Log Isu',
                'kategori' => $issue->category,
                'topik' => $issue->title,
                'penjelasan' => trim(($issue->description ?: '') . ($issue->follow_up ? "\nTindak lanjut: {$issue->follow_up}" : '')),
                'status' => $issue->status->value,
            ]);
        }

        foreach ($requests as $req) {
            $items->push([
                'tanggal' => $req->occurred_at,
                'sumber' => 'Log Permintaan',
                'kategori' => $req->category,
                'topik' => $req->title,
                'penjelasan' => $req->follow_up,
                'status' => $req->status->value,
            ]);
        }

        foreach ($resetRequests as $rr) {
            $items->push([
                'tanggal' => $rr->requested_at,
                'sumber' => 'Permohonan Reset',
                'kategori' => $rr->types_label,
                'topik' => "{$rr->name} ({$rr->account_user_id})",
                'penjelasan' => $rr->notes,
                'status' => $rr->status->value,
            ]);
        }

        $items = $items->sortBy('tanggal')->values();

        $summary = [
            'issues' => $issues->count(),
            'requests' => $requests->count(),
            'reset_requests' => $resetRequests->count(),
            // Backup tidak punya status, jadi sengaja TIDAK dihitung ke 'total' maupun rincian status.
            'backups' => $backupLogs->count(),
            'controls' => $serverChecks->count(),
            'total' => $items->count(),
            'open' => $items->where('status', 'Open')->count(),
            'in_progress' => $items->where('status', 'In Progress')->count(),
            'done' => $items->where('status', 'Done')->count(),
            'cancelled' => $items->where('status', 'Cancelled')->count(),
        ];

        return compact('items', 'summary', 'periodLabel', 'backupLogs', 'serverChecks');
    }
}