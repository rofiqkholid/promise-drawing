<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // Untuk password hashing
use Illuminate\Support\Facades\Mail; // Untuk mengirim email
use Illuminate\Support\Str;        // Untuk token random
use Carbon\Carbon;
// use App\Mail\ShareLinkMail;      // Anda perlu membuat Mailable ini
// use App\Models\SharedLink;       // Anda perlu membuat Model dan tabel ini

class ShareController extends Controller
{
    /**
     * Membuat link sharing untuk revisi dan mengirimkannya via email.
     *
     * @param Request $request
     * @param int $revisionId
     * @return JsonResponse
     */
    public function sendShareLink(Request $request, $revisionId): JsonResponse
    {
        // 1. Validasi Input
        $validated = $request->validate([
            'emails'        => 'required|array|min:1',
            'emails.*'      => 'required|email:rfc,dns', // Validasi setiap email
            'duration_days' => 'required|integer|min:1|max:365', // Batasi durasi (misal max 1 tahun)
            'password'      => 'nullable|string|min:6|required_with:use_password', // Wajib jika checkbox dicentang
            'use_password'  => 'sometimes|boolean', // Untuk checkbox (dikirim jika dicentang)
            'message'       => 'nullable|string|max:1000',
        ]);

        $revisionId = (int) $revisionId;
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }

        // 2. Ambil data revisi & paket untuk info dan permission check
        $revision = DB::table('doc_package_revisions as r')
            ->join('doc_packages as p', 'r.package_id', '=', 'p.id')
            ->join('products as pr', 'p.product_id', '=', 'pr.id')
            ->where('r.id', $revisionId)
            ->select('r.id', 'r.package_id', 'r.revision_no', 'pr.part_no') // Ambil data yang relevan
            ->first();

        if (!$revision) {
            return response()->json(['message' => 'Revision not found.'], 404);
        }

        // 3. !!! PENTING: Implementasikan Pengecekan Permission di sini !!!
        // Contoh: Apakah user yang login boleh membagikan revisi ini?
        // $user = $request->user();
        // if (!$user || !$user->can('share', $revision)) {
        //     Log::warning("Share Link: Permission denied", ['revisionId' => $revisionId, 'userId' => $userId]);
        //     return response()->json(['message' => 'Permission denied to share this revision.'], 403);
        // }

        // 4. Buat Data Share Link
        $token = Str::random(40); // Token unik
        $expiresAt = Carbon::now()->addDays($validated['duration_days']);
        $passwordHash = isset($validated['password']) ? Hash::make($validated['password']) : null;
        $recipientEmails = implode(',', $validated['emails']); // Simpan sebagai string comma-separated

        // --- BUTUH TABEL BARU: `shared_links` ---
        // Anda perlu membuat migrasi untuk tabel ini, contoh kolom:
        // id, token (unique index), revision_id, shared_by_user_id, recipient_emails (text),
        // expires_at (datetime), password (nullable string), created_at, updated_at
        try {
             DB::table('shared_links')->insert([ // Ganti dengan Model jika Anda membuatnya
                 'token' => $token,
                 'revision_id' => $revisionId,
                 'shared_by_user_id' => $userId,
                 'recipient_emails' => $recipientEmails,
                 'expires_at' => $expiresAt,
                 'password' => $passwordHash,
                 'created_at' => Carbon::now(),
                 'updated_at' => Carbon::now(),
             ]);
            // Atau jika pakai Model: SharedLink::create([...]);

        } catch (\Exception $e) {
            Log::error("Share Link: Failed to create share record in DB", [
                'revisionId' => $revisionId, 'userId' => $userId, 'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'Could not create share link record.'], 500);
        }
        // --- AKHIR BAGIAN TABEL BARU ---

        // 5. Buat URL Publik (Anda perlu definisikan route ini)
        // Contoh nama route: 'public.share.view'
        try {
            $shareUrl = route('public.share.view', ['token' => $token]);
        } catch(\Exception $e) {
             Log::error("Share Link: Route 'public.share.view' not defined.", ['exception' => $e]);
             // Hapus record DB yang baru dibuat jika URL gagal dibuat
             DB::table('shared_links')->where('token', $token)->delete();
             return response()->json(['message' => 'Server configuration error (route missing).'], 500);
        }


        // 6. Kirim Email ke Setiap Penerima
        // --- BUTUH MAILABLE BARU: `ShareLinkMail` ---
        // Buat mailable: php artisan make:mail ShareLinkMail
        // Desain email di resource/views/emails/share_link.blade.php
        $mailData = [
            'shareUrl' => $shareUrl,
            'sharerName' => Auth::user()->name ?? 'A user', // Nama pengirim
            'partNo' => $revision->part_no,
            'revisionNo' => $revision->revision_no,
            'expiresAt' => $expiresAt->format('Y-m-d H:i'),
            'isPasswordProtected' => !is_null($passwordHash),
            'customMessage' => $validated['message'] ?? null,
        ];

        try {
            foreach ($validated['emails'] as $email) {
                // Gunakan Queue jika memungkinkan untuk performa lebih baik
                Mail::to($email)->send(new \App\Mail\ShareLinkMail($mailData)); // Ganti namespace jika perlu
            }
        } catch (\Exception $e) {
            Log::error("Share Link: Failed to send email", [
                'revisionId' => $revisionId, 'emails' => $validated['emails'], 'error' => $e->getMessage()
            ]);
            // Email gagal terkirim, tapi link sudah dibuat. Beri tahu user.
            return response()->json([
                'success' => true, // Link tetap berhasil dibuat
                'message' => 'Share link created, but failed to send email automatically. Please check logs.'
            ], 207); // 207 Multi-Status
        }
        // --- AKHIR BAGIAN MAILABLE ---

        // 7. Berhasil
        return response()->json([
            'success' => true,
            'message' => 'Share link sent successfully to ' . count($validated['emails']) . ' recipient(s).'
        ]);
    }

    // Anda perlu membuat method (dan route) publik untuk menangani $shareUrl
    // public function viewSharedLink($token) { ... }
    // Method ini akan: validasi token, cek expiry, minta password jika ada,
    // tampilkan detail revisi & file, sediakan tombol download ZIP (memanggil downloadPackageZip)
}