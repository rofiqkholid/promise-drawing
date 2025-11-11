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
        return [
            // A1: judul + tanggal download (tanpa teks "Downloaded At")
            ['Summary Upload per Item ' . $this->downloadedAt],
            [],

            // A3:H3 header tabel (8 kolom)
            [
                'Customer',
                'Model',
                'Part Num',
                'Part Name',
                'Doc Type',
                'Category',
                'Part Group',
                'Upload Date',
            ],
            ...$this->rows,
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

                // ==== Judul di tengah (cover 8 kolom) ====
                $event->sheet->mergeCells('A1:H1');
                $event->sheet->getStyle('A1')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $event->sheet->getStyle('A1')->getFont()->setBold(true);

                // ==== HEADER (Customer .. Upload Date) ====
                $event->sheet->getStyle('A2:H2')->applyFromArray([
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
                $lastRow   = 2 + count($this->rows); // header di baris 3
                $cellRange = "A2:H{$lastRow}";

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
