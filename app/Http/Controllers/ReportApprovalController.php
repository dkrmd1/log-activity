<?php

namespace App\Http\Controllers;

use App\Models\MonthlyReport;
use App\Models\User;
use App\Notifications\SignatureActivity;
use App\Support\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/**
 * Alur persetujuan Laporan Bulanan:
 *   Staf  -> "Ajukan"           -> status submitted (tanda tangan "Dibuat oleh" tersimpan)
 *   Group Head -> "Setujui"     -> status approved  (tanda tangan "Diketahui oleh" tersimpan)
 *   Group Head -> "Tolak"       -> status rejected  (wajib isi catatan, Staf perbaiki lalu ajukan ulang)
 *
 * Setiap langkah mengirim notifikasi ke pihak terkait.
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

        // Notifikasi ke semua Group Head: ada laporan menunggu persetujuan.
        $this->notify(
            User::where('role', 'group_head')->get(),
            $user,
            'Laporan Bulanan menunggu persetujuan',
            "{$user->name} mengajukan Laporan Bulanan {$this->label($bulan, $tahun)}.",
            $bulan,
            $tahun,
            'tabler-file-check',
            'warning'
        );

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

        $this->approveAs($report, $user, $bulan, $tahun);

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

        if (! $report || ! in_array($report->status, [MonthlyReport::SUBMITTED, MonthlyReport::APPROVED], true)) {
            return $back->with('error', 'Tidak ada laporan yang bisa di-reject di periode ini.');
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

        // Notifikasi ke Staf yang mengajukan, lengkap dengan catatan penolakan.
        $this->notify(
            User::whereKey($report->prepared_by)->get(),
            $user,
            'Laporan Bulanan ditolak',
            "{$user->name} mengembalikan Laporan Bulanan {$this->label($bulan, $tahun)}. Catatan: "
                . Str::limit($request->input('review_note'), 120),
            $bulan,
            $tahun,
            'tabler-circle-x',
            'danger'
        );

        return $back->with('success', 'Laporan dikembalikan ke Staf untuk direvisi.');
    }

    /**
     * Persetujuan OTOMATIS: dipanggil saat Group Head membuka halaman Laporan Bulanan.
     * Hanya berlaku kalau laporan berstatus "diajukan", datanya belum berubah sejak diajukan,
     * dan Group Head sudah punya tanda tangan di Profil.
     *
     * @return string|null 'approved' | 'no_signature' | null (tidak ada yang perlu dilakukan)
     */
    public function autoApprove(User $user, int $bulan, int $tahun): ?string
    {
        if (! $user->isGroupHead()) {
            return null;
        }

        $report = MonthlyReport::forPeriod($bulan, $tahun)->first();

        if (! $report || $report->status !== MonthlyReport::SUBMITTED || $report->prepared_by === $user->id) {
            return null;
        }

        if (! $report->isCurrent(MonthlyReport::snapshotFor($bulan, $tahun)['fingerprint'])) {
            return null;
        }

        if (! Signature::exists($user->signature)) {
            return 'no_signature';
        }

        $this->approveAs($report, $user, $bulan, $tahun);

        return 'approved';
    }

    /**
     * Setujui + tanda tangani laporan atas nama $user, lalu beri tahu Staf pengaju.
     */
    protected function approveAs(MonthlyReport $report, User $user, int $bulan, int $tahun): void
    {
        $report->update([
            'status' => MonthlyReport::APPROVED,
            'reviewed_by' => $user->id,
            'reviewed_name' => $user->name,
            'reviewed_position' => $user->position_label,
            'reviewed_signature' => Signature::snapshot($user->signature),
            'reviewed_at' => now(),
            'review_note' => null,
        ]);

        $this->notify(
            User::whereKey($report->prepared_by)->get(),
            $user,
            'Laporan Bulanan disetujui',
            "{$user->name} menyetujui dan menandatangani Laporan Bulanan {$this->label($bulan, $tahun)}.",
            $bulan,
            $tahun,
            'tabler-circle-check',
            'success'
        );
    }

    /**
     * Kirim notifikasi ke $recipients, tanpa pelaku itu sendiri.
     */
    protected function notify($recipients, User $actor, string $title, string $message, int $bulan, int $tahun, string $icon, string $color): void
    {
        $recipients = $recipients->reject(fn (User $u) => $u->id === $actor->id);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new SignatureActivity(
            $title,
            $message,
            route('reports.index', ['bulan' => $bulan, 'tahun' => $tahun], false), // path relatif
            $icon,
            $color
        ));
    }

    protected function label(int $bulan, int $tahun): string
    {
        return Carbon::create($tahun, $bulan, 1)->locale('id')->translatedFormat('F Y');
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
