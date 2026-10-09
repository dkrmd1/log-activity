<?php

namespace App\Http\Controllers;

use App\Models\ServerCheck;
use App\Models\ServerCheckEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $rules = ['entries' => ['nullable', 'array']];

        foreach (array_keys(ServerCheckEntry::SLOTS) as $n) {
            $rules["entries.*.c{$n}"] = ['nullable', Rule::in(ServerCheckEntry::CONDITIONS)];
            $rules["entries.*.n{$n}"] = ['nullable', 'string', 'max:2000'];
        }

        $request->validate($rules);

        $input = $request->input('entries', []);
        $userId = auth()->id();
        $changed = 0;

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

                // Paraf otomatis = user yang terakhir mengubah baris ini.
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

        return redirect()
            ->route('server-checks.edit', $server_check)
            ->with('success', $changed > 0 ? 'Form berhasil disimpan.' : 'Tidak ada perubahan.');
    }

    public function destroy(ServerCheck $server_check)
    {
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

    protected function respond(ServerCheck $check, bool $inline)
    {
        @set_time_limit(120);

        $check->load('entries.parafUser');
        $checks = collect([$check]);

        $pdf = Pdf::loadView('server-checks.pdf', [
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
