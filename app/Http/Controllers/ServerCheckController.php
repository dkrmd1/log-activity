<?php

namespace App\Http\Controllers;

use App\Models\ServerCheck;
use App\Models\ServerCheckEntry;
use App\Models\User;
use App\Notifications\SignatureActivity;
use App\Support\Signature;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServerCheckController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan');

        $checks = ServerCheck::query()
            ->with('entries.parafUser')
            ->bulan($bulan)
            ->orderByDesc('check_date')
            ->paginate(15)
            ->withQueryString();

        return view('server-checks.index', [
            'checks' => $checks,
            'bulan' => $bulan,
            'slots' => ServerCheckEntry::SLOTS,
        ]);
    }

    /**
     * Buat form untuk 1 tanggal (item diambil dari master item aktif), lalu langsung ke halaman isi.
     */
    public function store(Request $request)
    {
        abort_unless($request->user()->isStaff(), 403, 'Hanya Staf yang bisa membuat form kontrol server.');

        $data = $request->validate(
            ['check_date' => ['required', 'date']],
            [],
            ['check_date' => 'Tanggal']
        );

        $date = Carbon::parse($data['check_date'])->toDateString();

        if ($existing = ServerCheck::whereDate('check_date', $date)->first()) {
            return redirect()
                ->route('server-checks.edit', $existing)
                ->with('success', 'Form tanggal ini sudah ada, silakan lanjut mengisi.');
        }

        $check = ServerCheck::createForDate($date, auth()->id());

        if ($check->entries()->count() === 0) {
            $check->delete();

            return back()->with('error', 'Belum ada item pemeriksaan aktif. Minta Admin menambahkan di menu "Item Kontrol Server".');
        }

        return redirect()
            ->route('server-checks.edit', $check)
            ->with('success', 'Form berhasil dibuat. Silakan isi pemeriksaan.');
    }

    public function edit(ServerCheck $server_check)
    {
        $server_check->load('entries.parafUser');

        return view('server-checks.edit', [
            'check' => $server_check,
            'groups' => $server_check->entries->groupBy('section_code'),
            'slots' => ServerCheckEntry::SLOTS,
        ]);
    }

    public function update(Request $request, ServerCheck $server_check)
    {
        // Paraf = tanda tangan staf yang mengisi, jadi hanya Staf yang boleh mengisi form.
        abort_unless($request->user()->isStaff(), 403, 'Hanya Staf yang bisa mengisi form kontrol server.');

        $rules = ['entries' => ['nullable', 'array']];

        foreach (array_keys(ServerCheckEntry::SLOTS) as $n) {
            $rules["entries.*.c{$n}"] = ['nullable', Rule::in(ServerCheckEntry::CONDITIONS)];
            $rules["entries.*.n{$n}"] = ['nullable', 'string', 'max:2000'];
        }

        $request->validate($rules);

        $input = $request->input('entries', []);
        $userId = auth()->id();
        $changed = 0;

        // Status "lengkap" SEBELUM perubahan, untuk mendeteksi form yang baru saja menjadi lengkap.
        $server_check->loadMissing('entries');
        $wasComplete = $this->isComplete($server_check);
        $wasRejected = $server_check->isRejected();

        DB::transaction(function () use ($server_check, $input, $userId, &$changed) {
            foreach ($server_check->entries as $entry) {
                $row = $input[$entry->id] ?? null;

                if (! is_array($row)) {
                    continue;
                }

                $dirty = false;

                // Hanya kolom yang dikirim form yang diproses (keterangan gabungan hanya dikirim di baris pertama bagian).
                foreach (array_keys($row) as $key) {
                    if (! preg_match('/^[cn][1-3]$/', $key)) {
                        continue;
                    }

                    $value = trim((string) ($row[$key] ?? ''));
                    $value = $value === '' ? null : $value;

                    if ((string) $entry->{$key} !== (string) $value) {
                        $entry->{$key} = $value;
                        $dirty = true;
                    }
                }

                // Paraf otomatis = staf yang terakhir mengubah baris ini.
                if ($dirty) {
                    $entry->paraf_by = $userId;
                    $entry->save();
                    $changed++;
                }
            }

            // Supaya laporan bulanan tahu data bulan ini berubah (lihat MonthlyReport::snapshotFor).
            if ($changed > 0) {
                $server_check->touch();
            }
        });

        // Isi form berubah -> tanda tangan lama tidak berlaku lagi (harus ditandatangani ulang).
        $signatureReset = $changed > 0 && $server_check->isSigned();

        if ($signatureReset) {
            $server_check->clearSignature();
        }

        // Staf sudah merevisi form yang ditolak -> penolakan dicabut, Group Head diberi tahu.
        $revised = $changed > 0 && $wasRejected;

        if ($revised) {
            $server_check->clearRejection();
        }

        // Notifikasi ke Group Head (hanya pada momen penting, bukan setiap simpan).
        if ($changed > 0) {
            $actor = $request->user();

            if ($revised) {
                $this->notifyGroupHeads(
                    $server_check,
                    $actor,
                    'Form Kontrol Server sudah direvisi',
                    "{$actor->name} merevisi Form Kontrol Server {$server_check->date_label} yang Anda tolak. Mohon diperiksa ulang.",
                    'tabler-clipboard-check',
                    'warning'
                );
            } elseif ($signatureReset) {
                $this->notifyGroupHeads(
                    $server_check,
                    $actor,
                    'Form perlu ditandatangani ulang',
                    "{$actor->name} mengubah Form Kontrol Server {$server_check->date_label} yang sudah ditandatangani. Tanda tangan dihapus, mohon tanda tangani ulang.",
                    'tabler-alert-triangle',
                    'warning'
                );
            } elseif (! $wasComplete && $this->isComplete($server_check)) {
                $this->notifyGroupHeads(
                    $server_check,
                    $actor,
                    'Form Kontrol Server siap diperiksa',
                    "{$actor->name} melengkapi Form Kontrol Server {$server_check->date_label}. Menunggu pemeriksaan dan tanda tangan Anda.",
                    'tabler-clipboard-check',
                    'warning'
                );
            }
        }

        return redirect()
            ->route('server-checks.edit', $server_check)
            ->with('success', $changed > 0
                ? 'Form berhasil disimpan.'
                    . ($signatureReset ? ' Tanda tangan Group Head dihapus karena isi berubah, Group Head perlu menandatangani ulang.' : '')
                    . ($revised ? ' Penolakan dicabut dan Group Head sudah diberi tahu untuk memeriksa ulang.' : '')
                : 'Tidak ada perubahan.');
    }

    /**
     * Tanda tangani form. HANYA Group Head. Tanda tangan diambil dari Profil dan disalin sebagai snapshot.
     */
    public function sign(Request $request, ServerCheck $server_check)
    {
        $user = $request->user();

        abort_unless($user->isGroupHead(), 403, 'Hanya Group Head yang bisa menandatangani form kontrol server.');

        $server_check->load('entries');

        if (array_sum($server_check->filledCounts()) === 0) {
            return back()->with('error', 'Form masih kosong, isi pemeriksaan dulu sebelum ditandatangani.');
        }

        if (! Signature::exists($user->signature)) {
            return back()->with('error', 'Tanda tangan Anda belum ada. Isi dulu di menu Profil Saya, lalu tanda tangani lagi.');
        }

        // Ganti tanda tangan sebelumnya (kalau ada) dengan yang baru.
        Signature::delete($server_check->signed_signature);

        $server_check->timestamps = false; // tanda tangan bukan perubahan data
        $server_check->update([
            'signed_by' => $user->id,
            'signed_name' => $user->name,
            'signed_signature' => Signature::snapshot($user->signature),
            'signed_at' => now(),
            // Ditandatangani = disetujui, jadi penolakan sebelumnya (kalau ada) tidak berlaku lagi.
            'rejected_by' => null,
            'rejected_name' => null,
            'reject_note' => null,
            'rejected_at' => null,
        ]);
        $server_check->timestamps = true;

        // Notifikasi ke pembuat form & petugas yang memberi paraf.
        $this->notifyStaff(
            $server_check,
            $user,
            'Form Kontrol Server ditandatangani',
            "{$user->name} menandatangani Form Kontrol Server {$server_check->date_label}.",
            'tabler-signature',
            'success'
        );

        return back()->with('success', 'Form berhasil ditandatangani.');
    }

    /**
     * Batalkan tanda tangan: hanya Group Head.
     */
    public function unsign(Request $request, ServerCheck $server_check)
    {
        $user = $request->user();

        abort_unless(
            $user->isGroupHead(),
            403,
            'Hanya Group Head yang bisa membatalkan tanda tangan form kontrol server.'
        );

        $server_check->clearSignature();

        $this->notifyStaff(
            $server_check,
            $user,
            'Tanda tangan form dibatalkan',
            "{$user->name} membatalkan tanda tangan Form Kontrol Server {$server_check->date_label}.",
            'tabler-writing-sign',
            'danger'
        );

        return back()->with('success', 'Tanda tangan form dibatalkan.');
    }

    /**
     * Tolak form: hanya Group Head, WAJIB disertai keterangan. Form dikembalikan ke Staf untuk direvisi.
     */
    public function reject(Request $request, ServerCheck $server_check)
    {
        $user = $request->user();

        abort_unless($user->isGroupHead(), 403, 'Hanya Group Head yang bisa menolak form kontrol server.');

        $request->validate(
            ['reject_note' => ['required', 'string', 'max:1000']],
            ['reject_note.required' => 'Isi keterangan penolakan supaya Staf tahu apa yang perlu diperbaiki.'],
            ['reject_note' => 'Keterangan']
        );

        if ($server_check->isSigned()) {
            return back()->with('error', 'Form ini sudah ditandatangani. Batalkan tanda tangan dulu kalau ingin menolaknya.');
        }

        $note = trim((string) $request->input('reject_note'));

        $server_check->timestamps = false; // penolakan bukan perubahan data
        $server_check->update([
            'rejected_by' => $user->id,
            'rejected_name' => $user->name,
            'reject_note' => $note,
            'rejected_at' => now(),
        ]);
        $server_check->timestamps = true;

        $this->notifyStaff(
            $server_check,
            $user,
            'Form Kontrol Server ditolak',
            "{$user->name} menolak Form Kontrol Server {$server_check->date_label}. Keterangan: " . Str::limit($note, 120),
            'tabler-circle-x',
            'danger'
        );

        return back()->with('success', 'Form dikembalikan ke Staf untuk direvisi.');
    }

    public function destroy(ServerCheck $server_check)
    {
        Signature::delete($server_check->signed_signature);
        $server_check->delete();

        return redirect()
            ->route('server-checks.index')
            ->with('success', 'Form kontrol berhasil dihapus.');
    }

    /**
     * Download PDF 1 hari (landscape, identik dengan lampiran di laporan bulanan).
     */
    public function pdf(ServerCheck $server_check)
    {
        return $this->respond($server_check, false);
    }

    /**
     * Pratinjau PDF langsung di browser (tanpa download).
     */
    public function preview(ServerCheck $server_check)
    {
        return $this->respond($server_check, true);
    }

    /**
     * Form dianggap LENGKAP kalau semua item sudah terisi di ketiga waktu pemeriksaan.
     */
    protected function isComplete(ServerCheck $check): bool
    {
        $total = $check->entries->count();

        if ($total === 0) {
            return false;
        }

        foreach ($check->filledCounts() as $count) {
            if ($count < $total) {
                return false;
            }
        }

        return true;
    }

    /**
     * Beri tahu semua Group Head (kecuali pelaku sendiri).
     */
    protected function notifyGroupHeads(ServerCheck $check, User $actor, string $title, string $message, string $icon, string $color): void
    {
        $recipients = User::where('role', 'group_head')
            ->where('id', '!=', $actor->id)
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send($recipients, new SignatureActivity(
            $title,
            $message,
            route('server-checks.edit', $check, false), // path relatif
            $icon,
            $color
        ));
    }

    /**
     * Beri tahu Staf: pembuat form + petugas yang memberi paraf (kecuali pelaku sendiri).
     */
    protected function notifyStaff(ServerCheck $check, User $actor, string $title, string $message, string $icon, string $color): void
    {
        $ids = $check->entries()->pluck('paraf_by')
            ->push($check->created_by)
            ->filter()
            ->unique()
            ->reject(fn ($id) => (int) $id === (int) $actor->id)
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        Notification::send(
            User::whereIn('id', $ids)->where('role', 'staff')->get(),
            new SignatureActivity(
                $title,
                $message,
                route('server-checks.edit', $check, false), // path relatif
                $icon,
                $color
            )
        );
    }

    protected function respond(ServerCheck $check, bool $inline)
    {
        @set_time_limit(120);

        $check->load('entries.parafUser');
        $checks = collect([$check]);

        $pdf = Pdf::loadView('server-checks.sheet', [
            'checks' => $checks,
            'sigs' => ServerCheck::signatureMap($checks),
        ])->setPaper('a4', 'landscape');

        $filename = 'kontrol-server-jaringan-' . $check->check_date->format('Ymd') . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => ($inline ? 'inline' : 'attachment') . '; filename="' . $filename . '"',
        ]);
    }
}
