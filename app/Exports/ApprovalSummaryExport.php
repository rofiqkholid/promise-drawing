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

    public function __construct(array $rows)
    {
        $this->rows = $rows;
    }

    public function array(): array
    {
        return [
            ['Summary Upload per Item'], // A1
            [],                          // A2
            ['Customer', 'Model', 'Part Num', 'Part Name',
             'File name', 'Doc Type', 'Category', 'Part Group'], // A3:H3
            ...$this->rows,              // data mulai baris 4
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

                // ==== Judul di tengah ====
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                // ==== Border all untuk tabel (header + data) ====
                $lastRow   = 3 + count($this->rows); // header di baris 3
                $cellRange = "A3:H{$lastRow}";

                $event->sheet->getStyle($cellRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['argb' => 'FF000000'],
                        ],
                    ],
                ]);

                // ==== Warna untuk baris pemisah (baris kosong antar Customer/Model) ====
                // data pertama di Excel = baris 4, index 0 di $this->rows
                foreach ($this->rows as $i => $row) {
                    // kalau semua kolom kosong -> ini separator
                    if (trim(implode('', array_map('strval', $row))) === '') {
                        $excelRow = 4 + $i; // baris di sheet
                        $event->sheet->getStyle("A{$excelRow}:H{$excelRow}")
                            ->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'color'    => ['argb' => 'FFEFEFEF'], // abu-abu muda
                                ],
                            ]);
                    }
                }
            },
        ];
    }
}
