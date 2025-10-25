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

    protected static function assertDocFolder(string $folder): void
    {
        $f = strtolower($folder);
        if (!in_array($f, ['2d', '3d', 'ecn'], true)) {
            throw new InvalidArgumentException('doc_folder harus 2d / 3d / ecn');
        }
    }

    /** {root}/{rev_folder}/{doc_folder}/{slug}.{ext} */
    public static function build(array $m): string
    {
        self::assertDocFolder((string) ($m['doc_folder'] ?? ''));

        $revFolder = self::revisionFolderName($m);
        $base = self::root($m) . '/' . $revFolder . '/' . strtolower((string) $m['doc_folder']);
        $name = Str::slug((string) ($m['filename'] ?? 'document'), '_');
        $ext  = strtolower((string) ($m['ext'] ?? 'dat'));

        return $base . '/' . $name . '.' . $ext;
    }

    public static function defaultFolders(array $m): array
    {
        $revFolder = self::revisionFolderName($m);
        $root = self::root($m) . '/' . $revFolder;
        return ["$root/2d", "$root/3d", "$root/ecn"];
    }

    public static function listRevisions(string $disk, array $meta): array
    {
        $root = self::root($meta);
        if (!Storage::disk($disk)->exists($root)) {
            return [];
        }
        $dirs = Storage::disk($disk)->directories($root);
        $revs = [];

        $pattern = '/(?:^|-)(?:Rev)(\d+)-/i';

        foreach ($dirs as $d) {
            $seg = basename($d);
            if (preg_match($pattern, $seg, $matches)) {
                $revs[] = (int) $matches[1];
            }
        }
        sort($revs, SORT_NUMERIC);
        return array_unique($revs);
    }

    public static function nextRevision(string $disk, array $meta): int
    {
        $revs = self::listRevisions($disk, $meta);
        if (empty($revs)) return 0;
        return max($revs) + 1;
    }

    public static function extractPartGroupFromPath(string $path): ?string
    {
        $p = explode('/', trim($path, '/'));
        $iRev = null;
        foreach ($p as $i => $seg) {
            if (preg_match('/(?:^|\w+-)Rev\d+-/i', $seg)) {
                $iRev = $i;
                break;
            }
        }
        if ($iRev === null || $iRev < 3) return null;
        return $p[$iRev - 3] ?? null;
    }

    public static function revisionFolderName(array $m): string
    {
        $rev = (int) ($m['rev'] ?? -1);
        if ($rev < 0) {
            throw new InvalidArgumentException('rev wajib >= 0');
        }
        if (empty($m['ecn_no'])) {
            throw new InvalidArgumentException('ecn_no wajib diisi');
        }

        $ecn = self::seg($m['ecn_no']);
        $revStr = "Rev{$rev}";

        if (!empty($m['revision_label_name'])) {
            $label = self::seg($m['revision_label_name']);
            return "{$label}-{$revStr}-{$ecn}";
        }

        return "{$revStr}-{$ecn}";
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        $filename = preg_replace('/[<>:"?*|\x00-\x1F]/', '', $filename);
        $filename = str_replace(['/', '\\'], '', $filename);
        if (empty($filename) || $filename === '.' || $filename === '..') {
            return 'invalid_filename';
        }
        return $filename;
    }
}
