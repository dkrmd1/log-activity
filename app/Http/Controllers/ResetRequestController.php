<?php

namespace App\Http\Controllers;

use App\Enums\JenisPermohonanEnum;
use App\Enums\StatusEnum;
use App\Exports\ResetRequestsExport;
use App\Http\Requests\ResetRequestRequest;
use App\Models\ResetRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ResetRequestController extends Controller
{
    public function index(Request $request)
    {
        $resetRequests = ResetRequest::query()
            ->status($request->query('status'))
            ->bulan($request->query('bulan'))
            ->when($request->query('cari'), function ($query, $cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('name', 'like', "%{$cari}%")
                        ->orWhere('account_user_id', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%");
                });
            })
            ->orderByDesc('requested_at')
            ->paginate(10)
            ->withQueryString();

        return view('reset-requests.index', [
            'resetRequests' => $resetRequests,
            'statusOptions' => StatusEnum::values(),
            'filters' => $request->only(['status', 'bulan', 'cari']),
        ]);
    }

    public function create()
    {
        return view('reset-requests.create', [
            'typeOptions' => JenisPermohonanEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function store(ResetRequestRequest $request)
    {
        ResetRequest::create($request->validated());

        return redirect()
            ->route('reset-requests.index')
            ->with('success', 'Permohonan baru berhasil ditambahkan.');
    }

    public function show(ResetRequest $reset_request)
    {
        $reset_request->load('activityLogs.user');

        return view('reset-requests.show', ['resetRequest' => $reset_request]);
    }

    public function edit(ResetRequest $reset_request)
    {
        return view('reset-requests.edit', [
            'resetRequest' => $reset_request,
            'typeOptions' => JenisPermohonanEnum::values(),
            'statusOptions' => StatusEnum::values(),
        ]);
    }

    public function update(ResetRequestRequest $request, ResetRequest $reset_request)
    {
        $reset_request->update($request->validated());

        return redirect()
            ->route('reset-requests.index')
            ->with('success', 'Data permohonan berhasil diperbarui.');
    }

    public function destroy(ResetRequest $reset_request)
    {
        $reset_request->delete();

        return redirect()
            ->route('reset-requests.index')
            ->with('success', 'Data permohonan berhasil dihapus.');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        return Excel::download(
            new ResetRequestsExport($filters),
            'permohonan-reset-' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['status', 'bulan', 'cari']);

        $resetRequests = ResetRequest::query()
            ->status($filters['status'] ?? null)
            ->bulan($filters['bulan'] ?? null)
            ->when($filters['cari'] ?? null, function ($query, $cari) {
                $query->where(function ($q) use ($cari) {
                    $q->where('name', 'like', "%{$cari}%")
                        ->orWhere('account_user_id', 'like', "%{$cari}%")
                        ->orWhere('email', 'like', "%{$cari}%");
                });
            })
            ->orderByDesc('requested_at')
            ->get();

        $pdf = Pdf::loadView('reset-requests.pdf', compact('resetRequests', 'filters'))->setPaper('a4', 'landscape');

        return $pdf->download('permohonan-reset-' . now()->format('Ymd_His') . '.pdf');
    }
}
