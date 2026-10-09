<?php

namespace App\Exports;

use App\Models\ServiceRequest;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RequestsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected int $no = 0;
    protected int $totalRows = 0;

    protected array $statusColors = [
        'Open' => ['bg' => 'FCE4E4', 'text' => 'C1121F'],
        'In Progress' => ['bg' => 'FFF1DA', 'text' => 'B4690E'],
        'Done' => ['bg' => 'E2F5E9', 'text' => '1B8A4A'],
        'Cancelled' => ['bg' => 'ECECEC', 'text' => '6C6C6C'],
    ];

    public function __construct(protected array $filters = [])
    {
    }

    public function title(): string
    {
        return 'Log Permintaan';
    }

    public function collection()
    {
        $data = ServiceRequest::query()
            ->status($this->filters['status'] ?? null)
            ->bulan($this->filters['bulan'] ?? null)
            ->when($this->filters['cari'] ?? null, function ($query, $cari) {
                $query->where('title', 'like', "%{$cari}%");
            })
            ->orderByDesc('occurred_at')
            ->get();

        $this->totalRows = $data->count();

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Terjadi',
            'Bulan',
            'Kategori Permintaan',
            'Permintaan',
            'Pemohon',
            'Status Permintaan',
            'Tanggal Selesai',
            'Status',
        ];
    }

    public function map($request): array
    {
        $this->no++;

        return [
            $this->no,
            $request->occurred_at->format('d/m/Y'),
            $request->month_label,
            $request->category,
            $request->title,
            $request->requester,
            $request->follow_up,
            $request->completed_at?->format('d/m/Y'),
            $request->status->value,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 14,
            'C' => 16,
            'D' => 18,
            'E' => 30,
            'F' => 20,
            'G' => 42,
            'H' => 14,
            'I' => 14,
        ];
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $sheet->getRowDimension(1)->setRowHeight(24);

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '696CFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $this->totalRows + 1;
                $lastColumn = 'I';

                if ($this->totalRows === 0) {
                    return;
                }

                $fullRange = "A1:{$lastColumn}{$lastRow}";
                $dataRange = "A2:{$lastColumn}{$lastRow}";

                $sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9E3'],
                        ],
                    ],
                ]);

                $sheet->getStyle("E2:G{$lastRow}")->getAlignment()
                    ->setWrapText(true)
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle($dataRange)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("B2:D{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("H2:I{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:G{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F7F7FB'],
                            ],
                        ]);
                    }

                    $statusValue = $sheet->getCell("I{$row}")->getValue();
                    $colors = $this->statusColors[$statusValue] ?? null;

                    if ($colors) {
                        $sheet->getStyle("I{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => $colors['text']]],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => $colors['bg']],
                            ],
                        ]);
                    }
                }

                $sheet->freezePane('A2');
                $sheet->setAutoFilter($fullRange);
            },
        ];
    }
}
