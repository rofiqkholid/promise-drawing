<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response; // Untuk response download
use ZipArchive; // Untuk membuat file ZIP
use Illuminate\Support\Str; // Untuk nama file random

class DownloadController extends Controller
{
    protected string $disk = 'datacenter'; // Pastikan disk storage sesuai

    /**
     * Mengunduh satu file berdasarkan ID file, memvalidasi signed URL.
     *
     * @param Request $request
     * @param int $fileId ID dari tabel doc_package_revision_files
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadSingleFile(Request $request, $fileId)
    {
        // Validasi Signed URL (Otomatis oleh middleware 'signed')
        // Jika validasi gagal, Laravel akan otomatis return 403 Forbidden

        // 1. Ambil data file dari database
        $fileRecord = DB::table('doc_package_revision_files')
            ->where('id', $fileId)
            ->first(['filename', 'storage_path']);

        if (!$fileRecord) {
            Log::warning("Download Single File: File record not found", ['fileId' => $fileId]);
            // Mungkin return halaman error atau JSON error
             return response()->json(['message' => 'File not found.'], 404);
            // abort(404, 'File not found.'); // Atau redirect ke halaman error
        }

        // 2. !!! PENTING: Implementasikan Pengecekan Permission di sini !!!
        // Contoh: Apakah user yang login boleh mengunduh file ini?
        // $user = $request->user();
        // if (!$user || !$user->can('download', $fileRecord)) { // Ganti 'can' dengan logic Anda
        //     Log::warning("Download Single File: Permission denied", ['fileId' => $fileId, 'userId' => $user->id ?? null]);
        //     return response()->json(['message' => 'Permission denied.'], 403);
        //     // abort(403, 'Permission denied.');
        // }

        // 3. Cek apakah file fisik ada di storage
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk($this->disk);

        if (!$storage->exists($fileRecord->storage_path)) {
            Log::error("Download Single File: File not found in storage", [
                'fileId' => $fileId,
                'path' => $fileRecord->storage_path
            ]);
            return response()->json(['message' => 'File storage error.'], 500);
        }

        try {
            return $storage->download($fileRecord->storage_path, $fileRecord->filename);
        } catch (\Exception $e) {
            Log::error("Download Single File: Storage download error", [
                'fileId' => $fileId,
                'path' => $fileRecord->storage_path,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Could not process download.'], 500);
        }
    }

    /**
     * Mengunduh semua file dari satu revisi dalam bentuk ZIP.
     *
     * @param Request $request
     * @param int $revisionId ID dari tabel doc_package_revisions
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
     */
    public function downloadPackageZip(Request $request, $revisionId)
    {
        $revisionId = (int) $revisionId;

        // 1. Ambil data revisi dan paket untuk nama file ZIP dan permission check
        $revision = DB::table('doc_package_revisions as r')
            ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->join('products as pr', 'p.product_id', '=', 'pr.id') // Join ke produk untuk Part No
            ->where('r.id', $revisionId)
            ->select('r.*', 'p.package_no', 'pr.part_no') // Ambil semua data revisi + package_no + part_no
            ->first();

        if (!$revision) {
            Log::warning("Download Package ZIP: Revision not found", ['revisionId' => $revisionId]);
            return response()->json(['message' => 'Revision not found.'], 404);
            // abort(404, 'Revision not found.');
        }

        // 2. !!! PENTING: Implementasikan Pengecekan Permission di sini !!!
        // Contoh: Apakah user yang login boleh mengunduh paket revisi ini?
        // $user = $request->user();
        // if (!$user || !$user->can('downloadPackage', $revision)) { // Ganti 'can' dengan logic Anda
        //     Log::warning("Download Package ZIP: Permission denied", ['revisionId' => $revisionId, 'userId' => $user->id ?? null]);
        //     return response()->json(['message' => 'Permission denied.'], 403);
        //     // abort(403, 'Permission denied.');
        // }

        // 3. Ambil semua file untuk revisi ini
        $files = DB::table('doc_package_revision_files')
            ->where('revision_id', $revisionId)
            ->get(['filename', 'storage_path', 'category']);

        if ($files->isEmpty()) {
            // Mungkin return pesan error atau redirect kembali dengan notifikasi
            return response()->json(['message' => 'No files found in this revision to download.'], 404);
            // return redirect()->back()->with('error', 'No files found in this revision.');
        }

        // 4. Buat file ZIP sementara
        $zipFileName = Str::slug($revision->part_no . '-rev' . $revision->revision_no . '-' . $revision->ecn_no, '-') . '.zip';
        $tempZipPath = storage_path('app/temp/' . Str::random(16) . '.zip'); // Path di storage/app/temp

        // Pastikan direktori temp ada
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Log::error("Download Package ZIP: Cannot create temporary ZIP file", ['path' => $tempZipPath]);
            return response()->json(['message' => 'Could not create archive.'], 500);
            // abort(500, 'Could not create archive.');
        }

        // 5. Tambahkan file ke ZIP
        $addedFilesCount = 0;
        foreach ($files as $file) {
            if (Storage::disk($this->disk)->exists($file->storage_path)) {
                // Buat struktur folder di dalam ZIP (opsional)
                $pathInsideZip = strtolower($file->category ?? 'other') . '/' . $file->filename;
                // Ambil konten file dari storage dan tambahkan ke zip
                $zip->addFromString($pathInsideZip, Storage::disk($this->disk)->get($file->storage_path));
                $addedFilesCount++;
            } else {
                Log::warning("Download Package ZIP: File missing from storage, skipped.", ['revisionId' => $revisionId, 'path' => $file->storage_path]);
                // Anda bisa memilih untuk menghentikan proses atau melanjutkan tanpa file yang hilang
            }
        }
        $zip->close();

        if ($addedFilesCount === 0) {
             // Jika semua file hilang dari storage
            unlink($tempZipPath); // Hapus file zip kosong
            return response()->json(['message' => 'All files for this revision are missing from storage.'], 500);
        }

        // 6. Kembalikan response download ZIP dan hapus file sementara setelah dikirim
        return Response::download($tempZipPath, $zipFileName)->deleteFileAfterSend(true);

    }
}