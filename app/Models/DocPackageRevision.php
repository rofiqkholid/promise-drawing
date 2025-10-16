<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocPackageRevision extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Mendapatkan paket induk dari revisi ini.
     */
    public function package(): BelongsTo
    {
        return $this->belongsTo(DocPackage::class, 'package_id');
    }

    /**
     * Mendapatkan semua file dari revisi ini.
     */
    public function files(): HasMany
    {
        return $this->hasMany(DocPackageRevisionFile::class, 'revision_id');
    }

    /**
     * Mendapatkan semua data approval untuk revisi ini.
     */
    public function approvals(): HasMany
    {
        return $this->hasMany(PackageApproval::class, 'revision_id');
    }

    /**
     * Mendapatkan pengguna yang membuat revisi.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
