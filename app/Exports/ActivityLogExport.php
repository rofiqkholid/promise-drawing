<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths; // Tambahan agar kolom Description lebar
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
        // Query persis sama dengan Controller
        $query = DB::table('activity_logs as al')
            ->leftJoin('users as u', 'al.user_id', '=', 'u.id')
            ->leftJoin('doc_package_revisions as dpr', 'al.revision_id', '=', 'dpr.id')
            ->select(
                'al.created_at',
                'u.name as user_name',
                'al.activity_code',
                'dpr.ecn_no', // ECN dari tabel revisi (backup)
                'al.meta'     // Data Snapshot lengkap
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
            'ECN No (Ref)',
            'Description / Details (Snapshot)' // Kolom detail lengkap
        ];
    }

    // Mengatur lebar kolom agar Description lega
    public function columnWidths(): array
    {
        return [
            'A' => 20, // Date
            'B' => 20, // User
            'C' => 20, // Activity
            'D' => 20, // ECN
            'E' => 80, // Description (Lebar)
        ];
    }

    public function map($row): array
    {
        // Decode JSON Meta
        $meta = json_decode($row->meta, true) ?? [];
        $desc = '';

        // Helper untuk Part Info
        $partInfo = '';
        if (isset($meta['part_no'])) {
            $rev = isset($meta['revision_no']) ? "Rev-{$meta['revision_no']}" : '';
            $partInfo = "[{$meta['part_no']} {$rev}]";
        }

        // --- LOGIKA FORMATTING TEXT UNTUK EXCEL ---
        
        $code = $row->activity_code;

        if ($code === 'UPLOAD') {
            // Format: "Uploaded 3 Files. [PartNo Rev] Customer - Model. Note: ..."
            $fileCount = $meta['file_count'] ?? '?';
            $types = $meta['file_types'] ?? '';
            $desc = "Uploaded {$fileCount} files ({$types}). {$partInfo} ";
            $desc .= ($meta['customer_code'] ?? '') . " - " . ($meta['model_name'] ?? '');
            if (!empty($meta['note'])) $desc .= " | Note: {$meta['note']}";
        } 
        
        elseif ($code === 'SUBMIT_APPROVAL') {
            // Format: "Request Approval for [PartNo Rev]. Customer..."
            $desc = "Request Approval for {$partInfo}. ";
            $desc .= ($meta['customer_code'] ?? '') . " - " . ($meta['model_name'] ?? '');
        } 
        
        elseif ($code === 'REVISE_CONFIRM') {
            // Format: "Revision Confirmed. Approved -> Draft. [PartNo Rev]..."
            $prev = $meta['previous_status'] ?? '?';
            $curr = $meta['current_status']  ?? 'Draft';
            $desc = "Revision Confirmed ({$prev} -> {$curr}). {$partInfo}";
        } 
        
        elseif ($code === 'APPROVE' || $code === 'REJECT') {
            // Format: "Approved/Rejected. [PartNo Rev]. Note: ..."
            $status = ucfirst(strtolower($code));
            $desc = "{$status}. {$partInfo}";
            if (!empty($meta['note'])) $desc .= " | Note: \"{$meta['note']}\"";
        } 
        
        elseif ($code === 'ROLLBACK') {
            // Format: "Rollback (Approved -> Waiting). [PartNo Rev]. Reason: ..."
            $prev = $meta['previous_status'] ?? '?';
            $curr = $meta['current_status']  ?? '?';
            $desc = "Rollback ({$prev} -> {$curr}). {$partInfo}";
            if (!empty($meta['reason'])) $desc .= " | Reason: \"{$meta['reason']}\"";
        } 
        
        elseif ($code === 'DOWNLOAD') {
            // Format: "Downloaded: Filename.zip (5.2 MB). [PartNo Rev]"
            $file = $meta['downloaded_file'] ?? '-';
            $size = $meta['file_size'] ?? '';
            $desc = "Downloaded: {$file}";
            if ($size) $desc .= " ({$size})";
            $desc .= ". {$partInfo}";
        } 
        
        elseif (str_contains($code, 'SHARE')) {
            // Format: "Shared to: Purchasing, PUD. Recipients: Budi, Andi. [PartNo Rev]"
            $target = $meta['shared_to_dept'] ?? $meta['shared_with'] ?? '-';
            $recipients = $meta['recipients'] ?? ''; // Daftar nama orang
            
            $desc = "Shared to: {$target}.";
            if ($recipients) $desc .= " Recipients: {$recipients}.";
            $desc .= " {$partInfo}";
            if (!empty($meta['note'])) $desc .= " | Msg: \"{$meta['note']}\"";
        } 
        
        else {
            // Fallback: Jika logic belum ada atau format lama
            if (is_string($row->meta)) {
                $desc = $row->meta;
            } elseif (isset($meta['note'])) {
                $desc = $meta['note'];
            } else {
                $desc = json_encode($meta);
            }
        }

        // ECN Priority: Ambil dari Snapshot Meta dulu (paling akurat saat kejadian), fallback ke tabel revisi
        $ecnDisplay = $meta['ecn_no'] ?? $row->ecn_no ?? '-';

        return [
            $row->created_at,
            $row->user_name ?? 'System',
            $row->activity_code,
            $ecnDisplay,
            $desc // <-- Hasil formatting yang lengkap
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]], // Header Bold
        ];
    }
}