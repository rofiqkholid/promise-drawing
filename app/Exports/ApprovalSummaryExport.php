<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ApprovalSummaryExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
{
    protected array $rows;
    protected string $downloadedAt;

    public function __construct(array $rows, string $downloadedAt)
    {
        $this->rows         = $rows;
        $this->downloadedAt = $downloadedAt;
    }

    public function array(): array
    {
        // tambahkan nomor di kiri
        $numberedRows = [];
        $no = 1;
        foreach ($this->rows as $row) {
            $numberedRows[] = array_merge([$no++], $row);
        }

        return [
            // Row 1: judul
            ['Summary Upload per Item ' . $this->downloadedAt],

            // Row 2: header tabel (13 kolom: A - M)
            [
                'No',
                'Customer',
                'Model',
                'Part Num',
                'Part Name',
                'Doc Type',
                'Category',
                'ECN No',
                'Revision No',   // <-- NEW
                'Part Group',
                'Receive Date',
                'Upload Date',
                'F/Good',
            ],

            // Row 3+: data dengan nomor
            ...$numberedRows,
        ];
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                // ==== Judul di tengah (cover 13 kolom: A1:M1) ====
                $event->sheet->mergeCells('A1:M1');
                $event->sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                // ==== HEADER (row 2) berwarna, A2:M2 ====
                $event->sheet->getStyle('A2:M2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['argb' => 'FFD9EAD3'],
                    ],
                ]);

                // ==== Border all untuk header + data ====
                $lastRow   = 2 + count($this->rows); // header row 2, data mulai row 3
                $cellRange = "A2:M{$lastRow}";

                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);
            },
        ];
    }
}
