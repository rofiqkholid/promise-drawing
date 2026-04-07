<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ActivityLogExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnWidths
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->leftJoin('doc_package_revisions as dpr', 'al.revision_id', '=', 'dpr.id')
            ->select(
                'al.created_at',
                'u.name as user_name',
                'al.activity_code',
                'dpr.ecn_no',
                'al.meta'
            );

        // --- FILTERING ---
        if ($this->request->filled('user_id') && $this->request->user_id !== 'All') {
            $query->where('al.user_id', $this->request->user_id);
        }
        if ($this->request->filled('activity_code') && $this->request->activity_code !== 'All') {
            $query->where('al.activity_code', $this->request->activity_code);
        }
        if ($this->request->filled('date_start')) {
            $query->whereDate('al.created_at', '>=', $this->request->date_start);
        }
        if ($this->request->filled('date_end')) {
            $query->whereDate('al.created_at', '<=', $this->request->date_end);
        }
        
        // Search Value
        $searchValue = $this->request->get('search_value');
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                $q->where('u.name', 'like', "%{$searchValue}%")
                  ->orWhere('al.activity_code', 'like', "%{$searchValue}%")
                  ->orWhere('al.meta', 'like', "%{$searchValue}%")
                  ->orWhere('dpr.ecn_no', 'like', "%{$searchValue}%");
            });
        }

        return $query->orderBy('al.created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Date Time',
            'User',
            'Activity',
            'Part No',
            'Rev',
            'Customer',
            'Model',
            'ECN No',
            'Description / Details'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Date
            'B' => 25, // User
            'C' => 20, // Activity
            'D' => 20, // Part No
            'E' => 8,  // Rev
            'F' => 15, // Customer
            'G' => 15, // Model
            'H' => 20, // ECN
            'I' => 60, // Description
        ];
    }

    public function map($row): array
    {
        $meta = json_decode($row->meta, true) ?? [];
        $code = $row->activity_code;
        $desc = '';

        // Extract Common Fields
        $partNo   = $meta['part_no'] ?? '-';
        $revNo    = $meta['revision_no'] ?? '-';
        $customer = $meta['customer_code'] ?? '-';
        $model    = $meta['model_name'] ?? '-';
        $ecnNo    = $meta['ecn_no'] ?? $row->ecn_no ?? '-';

        // --- DESCRIPTION LOGIC ---
        if ($code === 'UPLOAD') {
            $fileCount = $meta['file_count'] ?? '?';
            $types = $meta['file_types'] ?? '';
            $desc = "Uploaded {$fileCount} files ({$types}).";
            if (!empty($meta['doctype_group'])) $desc .= " DocType: {$meta['doctype_group']}.";
            if (!empty($meta['note'])) $desc .= " Note: {$meta['note']}";
        } 
        elseif ($code === 'SUBMIT_APPROVAL') {
            $desc = "Request Approval.";
            if (!empty($meta['note'])) $desc .= " Note: {$meta['note']}";
        } 
        elseif ($code === 'REVISE_CONFIRM') {
            $prev = $meta['previous_status'] ?? '?';
            $curr = $meta['current_status']  ?? 'Draft';
            $desc = "Revision Confirmed ({$prev} -> {$curr}).";
        } 
        elseif ($code === 'APPROVE' || $code === 'REJECT') {
            $status = ucfirst(strtolower($code));
            $desc = "Action: {$status}.";
            if (!empty($meta['note'])) $desc .= " Note: \"{$meta['note']}\"";
        } 
        elseif ($code === 'ROLLBACK') {
            $prev = $meta['previous_status'] ?? '?';
            $curr = $meta['current_status']  ?? '?';
            $desc = "Rollback ({$prev} -> {$curr}).";
            if (!empty($meta['reason'])) $desc .= " Reason: \"{$meta['reason']}\"";
            elseif (!empty($meta['note'])) $desc .= " Note: \"{$meta['note']}\"";
        } 
        elseif ($code === 'DOWNLOAD') {
            $file = $meta['downloaded_file'] ?? '-';
            $size = $meta['file_size'] ?? '';
            $desc = "Downloaded: {$file}";
            if ($size) $desc .= " ({$size})";
        } 
        elseif (str_contains($code, 'SHARE')) {
            $target = $meta['shared_to_dept'] ?? $meta['shared_with'] ?? $meta['shared_to'] ?? '-';
            // Bersihkan [EXP] jika ada
            $target = str_replace('[EXP] ', '', $target);
            
            $recipients = $meta['recipients'] ?? '';
            $desc = "Shared to: {$target}.";
            if ($recipients) $desc .= " Recipients: {$recipients}.";
            if (!empty($meta['expired_at'])) $desc .= " Exp: {$meta['expired_at']}.";
            if (!empty($meta['note'])) $desc .= " Note: \"{$meta['note']}\"";
        } 
        elseif ($code === 'DELETE_PACKAGE' || $code === 'DELETE_DRAFT') {
            $action = $code === 'DELETE_PACKAGE' ? 'Package Deleted' : 'Draft Deleted';
            $status = $meta['revision_status'] ?? 'Draft';
            $desc = "{$action}. Last Status: {$status}.";
            if (!empty($meta['deleted_at'])) $desc .= " Deleted At: {$meta['deleted_at']}.";
        } 
        else {
            // Fallback
            if (isset($meta['note'])) {
                $desc = $meta['note'];
            } else {
                $desc = is_string($row->meta) ? $row->meta : json_encode($meta);
            }
        }

        return [
            $row->created_at,
            $row->user_name ?? 'System',
            $row->activity_code,
            $partNo,
            $revNo,
            $customer,
            $model,
            $ecnNo,
            $desc
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            ],
            'A' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]], // Date
            'C' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]], // Activity
            'D' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],   // Part No
            'E' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]], // Rev
            'F' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]], // Customer
            'G' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]], // Model
            'H' => ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]],   // ECN
            'I' => ['alignment' => ['wrapText' => true]], // Description wrap text
        ];
    }
}