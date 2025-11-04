<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class ApprovalSummaryExport implements FromArray, WithTitle
{
    protected string $customer;
    protected string $model;
    protected array $rows;

    public function __construct(string $customer, string $model, array $rows)
    {
        $this->customer = $customer;
        $this->model    = $model;
        $this->rows     = $rows;
    }

    public function array(): array
    {
        $headerRows = [
            // baris atas sesuai template
            ['Customer', $this->customer],
            ['Model',    $this->model],
            [], // baris kosong
            ['Part No', 'Part Name', 'File Name', 'Doctype', 'Category', 'Part Group'],
        ];

        return array_merge($headerRows, $this->rows);
    }

    public function title(): string
    {
        return 'Summary';
    }
}
