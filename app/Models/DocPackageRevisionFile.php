<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocPackageRevisionFile extends Model
{
    use HasFactory;

    protected $guarded = [];

     protected $casts = [
        'ori_position'   => 'integer',
        'copy_position'  => 'integer',
        'obslt_position' => 'integer',
        'blocks_position' => 'array',
    ];

    /**
     * Mendapatkan revisi induk dari file ini.
     */
    public function revision(): BelongsTo
    {
        return $this->belongsTo(DocPackageRevision::class, 'revision_id');
    }

    /**
     * Mendapatkan pengguna yang mengunggah file.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Mendapatkan detail ekstensi file.
     */
    public function extension(): BelongsTo
    {
        return $this->belongsTo(FileExtensions::class, 'file_extension_id');
    }
}
