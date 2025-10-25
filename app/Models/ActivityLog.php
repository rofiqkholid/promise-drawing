<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    // pastikan nama tabel sesuai
    protected $table = 'activity_logs';

    // kalau semua kolom boleh mass-assign
    protected $guarded = [];

    // cast kolom JSON & tanggal
    protected $casts = [
        'meta'       => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /* ===== Kode aksi (boleh pakai PHP enum kalau mau) ===== */
    public const UPLOAD  = 'UPLOAD';
    public const APPROVE = 'APPROVE';
    public const REJECT  = 'REJECT';

    /* ===== Relasi ===== */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ===== Scopes generik (bebas dipakai di semua page) ===== */

    // filter berdasarkan scope_type + scope_id (mis. 'package', 7)
    public function scopeForScope($q, string $type, $id)
    {
        return $q->where('scope_type', $type)->where('scope_id', $id);
    }

    // filter opsional per revision_id
    public function scopeForRevision($q, $revisionId = null)
    {
        if (!is_null($revisionId)) {
            $q->where('revision_id', $revisionId);
        }
        return $q;
    }

    // filter satu atau banyak activity_code
    public function scopeActions($q, array|string $actions)
    {
        return is_array($actions)
            ? $q->whereIn('activity_code', $actions)
            : $q->where('activity_code', $actions);
    }

    // helper khusus untuk package (biar ringkas di controller)
    public function scopeForPackage($q, $packageId)
    {
        return $q->forScope('package', $packageId);
    }

    /* ===== Atribut untuk UI (ikon/label) - opsional ===== */
    public function getUiActionAttribute(): string
    {
        return match (strtoupper($this->activity_code)) {
            self::UPLOAD  => 'uploaded',
            self::APPROVE => 'approved',
            self::REJECT  => 'rejected',
            default       => strtolower($this->activity_code),
        };
    }
}
