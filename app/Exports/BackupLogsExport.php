<?php

namespace App\Exports;

use App\Models\BackupLog;
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

class BackupLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents, WithTitle
{
    protected int $no = 0;
    protected int $totalRows = 0;

    public function __construct(protected array $filters = [])
    {
    }

    public function title(): string
    {
        return 'Log Backup';
    }

    public function collection()
    {
        $data = BackupLog::query()
            ->with('user')
            ->jenis($this->filters['jenis'] ?? null)
            ->bulan($this->filters['bulan'] ?? null)
            ->cari($this->filters['cari'] ?? null)
            ->orderByDesc('backup_date')
            ->orderByDesc('backup_time')
            ->get();

        $this->totalRows = $data->count();

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Jenis',
            'Tanggal',
            'Jam',
            'Keterangan',
            'Dicatat Oleh',
        ];
    }

    public function map($backupLog): array
    {
        $this->no++;

        return [
            $this->no,
            $backupLog->jenis,
            $backupLog->date_label,
            $backupLog->time_label,
            $backupLog->notes,
            $backupLog->user?->name ?? '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 30,
            'C' => 20,
            'D' => 10,
            'E' => 36,
            'F' => 22,
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
                $lastColumn = 'F';

                if ($this->totalRows === 0) {
                    return;
                }

                $fullRange = "A1:{$lastColumn}{$lastRow}";

                $sheet->getStyle($fullRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D9D9E3'],
                        ],
                    ],
                ]);

                $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP);

                $sheet->getStyle("E2:E{$lastRow}")->getAlignment()->setWrapText(true);

                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("C2:D{$lastRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F7F7FB'],
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
