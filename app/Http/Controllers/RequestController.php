<?php

namespace App\Http\Controllers;

use App\Enums\PemohonEnum;
use App\Enums\StatusEnum;
use App\Exports\RequestsExport;
use App\Http\Requests\ServiceRequestRequest;
use App\Models\Category;
use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RequestController extends Controller
{
    public function index(Request $request)
    {
        $requests = ServiceRequest::query()
            ->status($request->query('status'))
            ->bulan($request->query('bulan'))
            ->when($request->query('cari'), function ($query, $cari) {
                $query->where('title', 'like', "%{$cari}%");
            })
            ->orderByDesc('occurred_at')
            ->paginate(10)
            ->withQueryString();

        return view('requests.index', [
            'requests' => $requests,
            'statusOptions' => StatusEnum::values(),
            'filters' => $request->only(['status', 'bulan', 'cari']),
        ]);
    }

    public function create()
    {
        return view('requests.create', [
            'kategoriOptions' => Category::names('permintaan'),
            'pemohonOptions' => PemohonEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function store(ServiceRequestRequest $request)
    {
        ServiceRequest::create($request->validated());

        return redirect()
            ->route('requests.index')
            ->with('success', 'Permintaan baru berhasil ditambahkan.');
    }

    public function show(ServiceRequest $log_permintaan)
    {
        $log_permintaan->load('activityLogs.user');

        return view('requests.show', ['request' => $log_permintaan]);
    }

    public function edit(ServiceRequest $log_permintaan)
    {
        $kategoriOptions = Category::names('permintaan');

        // Kalau kategori data ini sudah tidak ada di daftar, tetap tampilkan supaya tidak hilang dari form edit.
        if ($log_permintaan->category && ! $kategoriOptions->contains($log_permintaan->category)) {
            $kategoriOptions->push($log_permintaan->category);
        }

        return view('requests.edit', [
            'request' => $log_permintaan,
            'kategoriOptions' => $kategoriOptions,
            'pemohonOptions' => PemohonEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function update(ServiceRequestRequest $request, ServiceRequest $log_permintaan)
    {
        $log_permintaan->update($request->validated());

        return redirect()
            ->route('requests.index')
            ->with('success', 'Data permintaan berhasil diperbarui.');
    }

    public function destroy(ServiceRequest $log_permintaan)
    {
        $log_permintaan->delete();

        return redirect()
            ->route('requests.index')
            ->with('success', 'Data permintaan berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        return Excel::download(
            new RequestsExport($filters),
            'log-permintaan-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        $requests = ServiceRequest::query()
            ->status($filters['status'] ?? null)
            ->bulan($filters['bulan'] ?? null)
            ->when($filters['cari'] ?? null, function ($query, $cari) {
                $query->where('title', 'like', "%{$cari}%");
            })
            ->orderByDesc('occurred_at')
            ->get();

        $pdf = Pdf::loadView('requests.pdf', compact('requests', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->download('log-permintaan-' . now()->format('Ymd_His') . '.pdf');
    }
}
