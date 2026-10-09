<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use App\Support\Signature;
use Illuminate\Http\Request;

/**
 * Alur persetujuan Laporan Bulanan:
 *   Staf  -> "Ajukan"           -> status submitted (tanda tangan "Dibuat oleh" tersimpan)
 *   Group Head -> "Setujui"     -> status approved  (tanda tangan "Diketahui oleh" tersimpan)
 *   Group Head -> "Tolak"       -> status rejected  (wajib isi catatan, Staf perbaiki lalu ajukan ulang)
 */
class ReportApprovalController extends Controller
{
    public function submit(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isStaff(), 403, 'Hanya Staf yang bisa mengajukan laporan.');

        [$bulan, $tahun] = $this->period($request);
        $snapshot = MonthlyReport::snapshotFor($bulan, $tahun);

        if (! Signature::exists($user->signature)) {
            return back()->with('error', 'Tanda tangan Anda belum ada. Isi dulu di menu Profil Saya, lalu ajukan lagi.');
        }

        if ($snapshot['count'] === 0) {
            return back()->with('error', 'Tidak ada data di periode ini, tidak ada yang perlu diajukan.');
        }

        $report = MonthlyReport::forPeriod($bulan, $tahun)->first();

        if ($report
            && in_array($report->status, [MonthlyReport::SUBMITTED, MonthlyReport::APPROVED], true)
            && $report->isCurrent($snapshot['fingerprint'])) {
            return back()->with('error', 'Laporan periode ini sudah diajukan/disetujui dan datanya belum berubah.');
        }

        // Pengajuan ulang: buang tanda tangan lama (milik pengajuan sebelumnya).
        if ($report) {
            Signature::delete($report->prepared_signature);
            Signature::delete($report->reviewed_signature);
        }

        $report ??= new MonthlyReport(['year' => $tahun, 'month' => $bulan]);

        $report->fill([
            'status' => MonthlyReport::SUBMITTED,
            'data_fingerprint' => $snapshot['fingerprint'],
            'prepared_by' => $user->id,
            'prepared_name' => $user->name,
            'prepared_position' => $user->position_label,
            'prepared_signature' => Signature::snapshot($user->signature),
            'prepared_at' => now(),
            'reviewed_by' => null,
            'reviewed_name' => null,
            'reviewed_position' => null,
            'reviewed_signature' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ])->save();

        return redirect()
            ->route('reports.index', ['bulan' => $bulan, 'tahun' => $tahun])
            ->with('success', 'Laporan berhasil diajukan ke Group Head.');
    }

    public function approve(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isGroupHead(), 403, 'Hanya Group Head yang bisa menyetujui laporan.');

        [$bulan, $tahun] = $this->period($request);
        $report = MonthlyReport::forPeriod($bulan, $tahun)->first();
        $back = redirect()->route('reports.index', ['bulan' => $bulan, 'tahun' => $tahun]);

        if (! $report || $report->status !== MonthlyReport::SUBMITTED) {
            return $back->with('error', 'Tidak ada laporan yang menunggu persetujuan di periode ini.');
        }

        if ($report->prepared_by === $user->id) {
            return $back->with('error', 'Anda tidak bisa menyetujui laporan yang Anda ajukan sendiri.');
        }

        if (! $report->isCurrent(MonthlyReport::snapshotFor($bulan, $tahun)['fingerprint'])) {
            return $back->with('error', 'Data bulan ini berubah sejak laporan diajukan. Minta Staf mengajukan ulang supaya yang Anda setujui sesuai data terbaru.');
        }

        if (! Signature::exists($user->signature)) {
            return $back->with('error', 'Tanda tangan Anda belum ada. Isi dulu di menu Profil Saya, lalu setujui lagi.');
        }

        $report->update([
            'status' => MonthlyReport::APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_name' => $user->name,
            'reviewed_position' => $user->position_label,
            'reviewed_signature' => Signature::snapshot($user->signature),
            'reviewed_at' => now(),
            'review_note' => null,
        ]);

        return $back->with('success', 'Laporan disetujui dan ditandatangani.');
    }

    public function reject(Request $request)
    {
        $user = $request->user();
        abort_unless($user->isGroupHead(), 403, 'Hanya Group Head yang bisa menolak laporan.');

        $request->validate(
            ['review_note' => ['required', 'string', 'max:1000']],
            ['review_note.required' => 'Isi catatan alasan penolakan supaya Staf tahu apa yang perlu diperbaiki.'],
            ['review_note' => 'Catatan']
        );

        [$bulan, $tahun] = $this->period($request);
        $report = MonthlyReport::forPeriod($bulan, $tahun)->first();
        $back = redirect()->route('reports.index', ['bulan' => $bulan, 'tahun' => $tahun]);

        if (! $report || $report->status !== MonthlyReport::SUBMITTED) {
            return $back->with('error', 'Tidak ada laporan yang menunggu persetujuan di periode ini.');
        }

        Signature::delete($report->reviewed_signature);

        $report->update([
            'status' => MonthlyReport::REJECTED,
            'reviewed_by' => $user->id,
            'reviewed_name' => $user->name,
            'reviewed_position' => $user->position_label,
            'reviewed_signature' => null,
            'reviewed_at' => now(),
            'review_note' => $request->input('review_note'),
        ]);

        return $back->with('success', 'Laporan dikembalikan ke Staf untuk direvisi.');
    }

    /**
     * @return array{0:int,1:int} [bulan, tahun]
     */
    protected function period(Request $request): array
    {
        $data = $request->validate([
            'bulan' => ['required', 'integer', 'between:1,12'],
            'tahun' => ['required', 'integer', 'between:2000,2100'],
        ]);

        return [(int) $data['bulan'], (int) $data['tahun']];
    }
}
