<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileExtensions extends Model
{
    use HasFactory;

    protected $table = 'file_extensions';

    protected $fillable = [
        'name',
        'code',
        'icon',        // VARBINARY(MAX)
        'icon_mime',   // NVARCHAR(100)
        'is_viewer',
    ];

    protected $casts = [
        'is_viewer'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Jangan expose biner mentah
    protected $hidden = ['icon'];


    /**
     * Bangun data URL dari kolom biner + mime.
     * Handling: driver sqlsrv bisa mengembalikan resource stream.
     */
    public function getIconSrcAttribute(): ?string
    {
        // Ambil nilai asli dari DB (tanpa cast/mutasi)
        $raw = $this->getRawOriginal('icon');

        if (empty($raw)) {
            return null;
        }

        // Kalau resource (stream) -> baca seluruh konten
        if (is_resource($raw)) {
            $raw = stream_get_contents($raw);
        }

        // Pastikan string; kalau bukan, jangan paksa
        if (!is_string($raw)) {
            return null;
        }

        $mime = $this->icon_mime ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode($raw);
    }
}
