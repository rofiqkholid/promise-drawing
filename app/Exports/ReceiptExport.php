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

class ReceiptExport implements FromArray, WithTitle, ShouldAutoSize, WithEvents
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
        $numberedRows = [];
        $no = 1;
        foreach ($this->rows as $row) {
            $numberedRows[] = array_merge([$no++], $row);
        }

        return [
            ['Receipt Repository Summary - ' . $this->downloadedAt],
            [
                'No',
                'Customer',
                'Model',
                'Part Number',
                'Doc Type',
                'Category',
                'ECN No',
                'Revision',
                'Received At',
                'Expires On'
            ],
            ...$numberedRows,
        ];
    }

    public function title(): string
    {
        return 'Receipt Export';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Merge title
                $event->sheet->mergeCells('A1:J1');
                $event->sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // Style Header
                $event->sheet->getStyle('A2:J2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['argb' => 'FFE2EFDA'],
                    ],
                ]);

                // Border all
                $lastRow = 2 + count($this->rows);
                $event->sheet->getStyle("A2:J{$lastRow}")->applyFromArray([
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
