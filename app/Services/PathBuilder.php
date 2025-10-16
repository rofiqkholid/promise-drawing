<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class PathBuilder
{
    protected static function seg(string $v): string
    {
        $v = trim($v);
        if ($v === '' || str_contains($v, '/') || str_contains($v, '\\') || str_contains($v, '..')) {
            throw new InvalidArgumentException('Segmen folder tidak valid');
        }
        $v = preg_replace('/[<>:"?*|\x00-\x1F]/', '', $v);
        return str_replace(' ', ' ', $v);
    }

    /** Root: customer/model/doctype_group/part_group/part_no */
    public static function root(array $m): string
    {
        return implode('/', [
            self::seg((string) $m['customer_code']),
            self::seg((string) $m['model_name']),
            self::seg((string) $m['doctype_group_name']),
            self::seg((string) $m['part_group']),
            self::seg((string) $m['part_no']),
        ]);
    }

    /** Validasi subfolder konten */
    protected static function assertDocFolder(string $folder): void
    {
        $f = strtolower($folder);
        if (!in_array($f, ['2d', '3d', 'ecn'], true)) {
            throw new InvalidArgumentException('doc_folder harus 2d / 3d / ecn');
        }
    }

    /** {root}/rev{rev}/{doc_folder}/{slug}.{ext} */
    public static function build(array $m): string
    {
        $rev = (int) ($m['rev'] ?? -1);
        if ($rev < 0) {
            throw new InvalidArgumentException('rev wajib >= 0');
        }
        self::assertDocFolder((string) ($m['doc_folder'] ?? ''));

        $base = self::root($m) . "/rev{$rev}/" . strtolower((string) $m['doc_folder']);
        $name = Str::slug((string) ($m['filename'] ?? 'document'), '_');
        $ext  = strtolower((string) ($m['ext'] ?? 'dat'));

        return $base . '/' . $name . '.' . $ext;
    }

    /** Daftar folder default untuk sebuah rev */
    public static function defaultFolders(array $m): array
    {
        $rev  = max(0, (int) ($m['rev'] ?? 0));
        $root = self::root($m) . "/rev{$rev}";
        return ["$root/2d", "$root/3d", "$root/ecn"];
    }

    /** List semua revN di bawah root (return: array<int>) */
    public static function listRevisions(string $disk, array $meta): array
    {
        $root = self::root($meta);
        if (!Storage::disk($disk)->exists($root)) {
            return [];
        }
        $dirs = Storage::disk($disk)->directories($root);
        $revs = [];
        foreach ($dirs as $d) {
            $seg = basename($d);
            if (preg_match('/^rev(\d+)$/i', $seg, $m)) {
                $revs[] = (int) $m[1];
            }
        }
        sort($revs, SORT_NUMERIC);
        return $revs;
    }

    /** Ambil rev berikutnya (>=1). Jika belum ada rev sama sekali → 1. */
    public static function nextRevision(string $disk, array $meta): int
    {
        $revs = self::listRevisions($disk, $meta);
        if (empty($revs)) return 1;
        return max($revs) + 1;
    }

    /** Ekstrak part_group dari path (generik) */
    public static function extractPartGroupFromPath(string $path): ?string
    {
        // .../{doctype_group}/{part_group}/{part_no}/revN/...
        $p = explode('/', trim($path, '/'));
        $iRev = null;
        foreach ($p as $i => $seg) {
            if (preg_match('/^rev\d+$/i', $seg)) { $iRev = $i; break; }
        }
        if ($iRev === null || $iRev < 3) return null;
        return $p[$iRev - 3] ?? null;
    }
}
