<?php

namespace App\Http\Controllers;

use App\Enums\PemohonEnum;
use App\Enums\StatusEnum;
use App\Exports\IssuesExport;
use App\Http\Requests\IssueRequest;
use App\Models\Issue;
use App\Models\Category;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class IssueController extends Controller
{
    public function index(Request $request)
    {
        $issues = Issue::query()
            ->status($request->query('status'))
            ->bulan($request->query('bulan'))
            ->when($request->query('cari'), function ($query, $cari) {
                $query->where('title', 'like', "%{$cari}%");
            })
            ->orderByDesc('occurred_at')
            ->paginate(10)
            ->withQueryString();

        return view('issues.index', [
            'issues' => $issues,
            'statusOptions' => StatusEnum::values(),
            'filters' => $request->only(['status', 'bulan', 'cari']),
        ]);
    }

    public function create()
    {
        return view('issues.create', [
            'kategoriOptions' => Category::names('isu'),
            'pemohonOptions' => PemohonEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function store(IssueRequest $request)
    {
        Issue::create($request->validated());

        return redirect()
            ->route('issues.index')
            ->with('success', 'Isu baru berhasil ditambahkan.');
    }

    public function show(Issue $issue)
    {
        $issue->load('activityLogs.user');

        return view('issues.show', compact('issue'));
    }

    public function edit(Issue $issue)
    {
        $kategoriOptions = Category::names('isu');

        // Kalau kategori data ini sudah dihapus dari master data, tetap tampilkan
        // sebagai pilihan (biar tidak "hilang" tiba-tiba dari form edit).
        if ($issue->category && ! $kategoriOptions->contains($issue->category)) {
            $kategoriOptions->push($issue->category);
        }

        return view('issues.edit', [
            'issue' => $issue,
            'kategoriOptions' => $kategoriOptions,
            'pemohonOptions' => PemohonEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function update(IssueRequest $request, Issue $issue)
    {
        $issue->update($request->validated());

        return redirect()
            ->route('issues.index')
            ->with('success', 'Data isu berhasil diperbarui.');
    }

    public function destroy(Issue $issue)
    {
        $issue->delete();

        return redirect()
            ->route('issues.index')
            ->with('success', 'Data isu berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        return Excel::download(
            new IssuesExport($filters),
            'log-isu-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        $issues = Issue::query()
            ->status($filters['status'] ?? null)
            ->bulan($filters['bulan'] ?? null)
            ->when($filters['cari'] ?? null, function ($query, $cari) {
                $query->where('title', 'like', "%{$cari}%");
            })
            ->orderByDesc('occurred_at')
            ->get();

        $pdf = Pdf::loadView('issues.pdf', compact('issues', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->download('log-isu-' . now()->format('Ymd_His') . '.pdf');
    }
}
