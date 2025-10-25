<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocPackageRevisionFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FilePreviewController extends Controller
{
    public function show($id)
    {
        // Ambil record file
        $file = DocPackageRevisionFile::findOrFail($id);

        // Ambil path relatif (disimpan saat upload)
        $path = $file->storage_path ?: $file->path ?? null;
        if (!$path) abort(404, 'File path not set.');

        // Pakai DISK yang sama dengan upload: 'datacenter'
        // (opsional: bisa baca dari config('files.preview_disk','datacenter'))
        $disk = Storage::disk('datacenter');

        if (!$disk->exists($path)) {
            abort(404, 'File not found in storage.');
        }

        // Deteksi MIME
        $ext = strtolower(pathinfo($file->filename ?? $path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'           => 'application/pdf',
            'jpg', 'jpeg'   => 'image/jpeg',
            'png'           => 'image/png',
            'gif'           => 'image/gif',
            'webp'          => 'image/webp',
            'bmp'           => 'image/bmp',
            'tif', 'tiff'   => 'image/tiff',
            default         => $disk->mimeType($path) ?: 'application/octet-stream',
        };

        // Stream INLINE agar <img> / <iframe> bisa menampilkan
        $response = new StreamedResponse(function () use ($disk, $path) {
            $stream = $disk->readStream($path);
            if ($stream === false) abort(404, 'Unable to open file stream.');
            fpassthru($stream);
            if (is_resource($stream)) fclose($stream);
        });

        $filename = $file->filename ?? basename($path);
        $response->headers->set('Content-Type', $mime);
        $response->headers->set('Content-Disposition', 'inline; filename="'.$filename.'"');
        $response->headers->set('Cache-Control', 'private, max-age=0, no-cache');

        return $response;
    }
}
