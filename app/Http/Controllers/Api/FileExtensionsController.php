<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FileExtensions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;


class FileExtensionsController extends Controller
{
    public function data(Request $request)
    {
        $agg = "STRING_AGG(CAST(cf.category_name AS NVARCHAR(MAX)), ', ')";

        $query = FileExtensions::query()
            ->select([
                'file_extensions.id',
                DB::raw('CAST(file_extensions.name AS NVARCHAR(200)) AS name'),
                DB::raw('CAST(file_extensions.code AS NVARCHAR(50))  AS code'),
                'file_extensions.is_viewer',
                DB::raw("($agg) AS categories_list"),
            ])
            ->leftJoin('category_file_extension as cf', 'cf.file_extension_id', '=', 'file_extensions.id')
            ->groupBy('file_extensions.id', 'file_extensions.name', 'file_extensions.code', 'file_extensions.is_viewer');

        $totalRecords = FileExtensions::count();

        // Apply Search
        if ($search = $request->input('search.value')) {
            $query->where(function ($q) use ($search) {
                $q->where('file_extensions.name', 'like', "%{$search}%")
                    ->orWhere('file_extensions.code', 'like', "%{$search}%");
            });
        }

        // Count filtered records
        $filteredRecords = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query->getQuery())
            ->count();

        // Apply Sorting
        // Columns mapping from DataTables: 0:No, 1:name, 2:code, 3:icon, 4:is_viewer, 5:action
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir         = $request->input('order.0.dir', 'asc');

        switch ($orderColumnIndex) {
            case 1:
                $query->orderBy('file_extensions.name', $orderDir);
                break;
            case 2:
                $query->orderBy('file_extensions.code', $orderDir);
                break;
            case 4:
                $query->orderBy('file_extensions.is_viewer', $orderDir);
                break;
            default:
                // Default sorting
                $query->orderBy('file_extensions.name', 'asc');
                break;
        }

        // Pagination
        $start  = (int) $request->get('start', 0);
        $length = (int) $request->get('length', 10);

        $rows = $query->skip($start)->take($length)->get();

        $ids = $rows->pluck('id');
        $icons = $ids->isEmpty()
            ? []
            : FileExtensions::whereIn('id', $ids)
            ->get(['id', 'icon', 'icon_mime'])
            ->mapWithKeys(fn(FileExtensions $m) => [$m->id => $m->icon_src])
            ->all();

        $sanitize = function ($s) {
            if ($s === null) return null;
            if (!is_string($s)) return $s;
            $s = mb_convert_encoding($s, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
        };

        $payload = [
            'draw'            => (int) $request->get('draw', 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows->map(fn($r) => [
                'id'              => $r->id,
                'name'            => $sanitize($r->name),
                'code'            => $sanitize($r->code),
                'icon_src'        => $icons[$r->id] ?? null,
                'is_viewer'       => (bool) $r->is_viewer,
                'categories_list' => $sanitize($r->categories_list),
            ])->values(),
        ];

        return response()->json(
            $payload,
            200,
            [],
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:50',
                'code'         => 'required|string|max:10|unique:file_extensions,code',
                'categories'   => 'nullable|array',
                'categories.*' => ['required', Rule::in(['2D', '3D', 'ECN'])],
                'icon'         => 'nullable|file|mimetypes:image/png,image/jpeg,image/webp,image/svg+xml|max:250',
                'is_viewer'    => 'nullable|boolean',
            ]);

            DB::transaction(function () use ($validated, $request) {
                $m = new FileExtensions();
                $m->name      = $validated['name'];
                $m->code      = $validated['code'];
                $m->is_viewer = $request->boolean('is_viewer');
                $m->save();

                if ($request->hasFile('icon')) {
                    [$bin, $mime] = $this->uploadedToBinary($request->file('icon'));
                    $hex = bin2hex($bin);
                    DB::table('file_extensions')
                        ->where('id', $m->id)
                        ->update([
                            'icon_mime'  => $mime,
                            'updated_at' => now(),
                            'icon'       => DB::raw("CONVERT(VARBINARY(MAX), 0x{$hex})"),
                        ]);
                }

                if (!empty($validated['categories'])) {
                    $rows = collect($validated['categories'])->map(fn($c) => [
                        'file_extension_id' => $m->id,
                        'category_name'     => $c,
                    ])->all();
                    DB::table('category_file_extension')->insert($rows);
                }
            });


            return response()->json(
                ['success' => true, 'message' => 'File Extension created successfully.'],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Throwable $e) {
            Log::error('fileext.store failed', [
                'msg'    => $e->getMessage(),
                'fields' => $request->except(['icon', 'icon_base64']),
            ]);

            return response()->json(
                ['success' => false, 'message' => $e->getMessage()],
                422,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }

    public function show(FileExtensions $fileExtension)
    {
        $categories = DB::table('category_file_extension')
            ->where('file_extension_id', $fileExtension->id)
            ->pluck('category_name');
        $fileExtension->append('icon_src');
        return response()->json([
            'id'         => $fileExtension->id,
            'name'       => $fileExtension->name,
            'code'       => $fileExtension->code,
            'icon_src'   => $fileExtension->icon_src,
            'is_viewer'  => (bool) $fileExtension->is_viewer,
            'categories' => $categories,
        ]);
    }




    /**
     * Memperbarui data yang ada.
     */
    public function update(Request $request, FileExtensions $fileExtension)
    {
        try {
            $validated = $request->validate([
                'name'         => 'required|string|max:50',
                'code'         => ['required', 'string', 'max:10', Rule::unique('file_extensions', 'code')->ignore($fileExtension->id)],
                'categories'   => 'nullable|array',
                'categories.*' => ['required', Rule::in(['2D', '3D', 'ECN'])],
                'icon'         => 'nullable|file|mimetypes:image/png,image/jpeg,image/webp,image/svg+xml|max:100',
                'is_viewer'    => 'nullable|boolean',
            ]);

            DB::transaction(function () use ($fileExtension, $validated, $request) {
                $data = [
                    'name'      => $validated['name'],
                    'code'      => $validated['code'],
                    'is_viewer' => $request->boolean('is_viewer'),
                ];
                $fileExtension->update($data);

                if ($request->hasFile('icon')) {
                    [$bin, $mime] = $this->uploadedToBinary($request->file('icon'));
                    $hex = bin2hex($bin);
                    DB::table('file_extensions')
                        ->where('id', $fileExtension->id)
                        ->update([
                            'icon_mime'  => $mime,
                            'updated_at' => now(),
                            'icon'       => DB::raw("CONVERT(VARBINARY(MAX), 0x{$hex})"),
                        ]);
                }

                // categories 
                DB::table('category_file_extension')->where('file_extension_id', $fileExtension->id)->delete();
                if (!empty($validated['categories'])) {
                    $rows = collect($validated['categories'])->map(fn($c) => [
                        'file_extension_id' => $fileExtension->id,
                        'category_name'     => $c,
                    ])->all();
                    DB::table('category_file_extension')->insert($rows);
                }
            });

            return response()->json(
                ['success' => true, 'message' => 'File Extension updated successfully.'],
                200,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        } catch (\Throwable $e) {
            Log::error('fileext.update failed', [
                'msg'    => $e->getMessage(),
                'fields' => $request->except(['icon', 'icon_base64']),
            ]);

            return response()->json(
                ['success' => false, 'message' => $e->getMessage()],
                422,
                [],
                JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
            );
        }
    }


    public function destroy(FileExtensions $fileExtension)
    {
        $fileExtension->delete();
        return response()->json(['success' => true, 'message' => 'File Extension deleted successfully.']);
    }

    private function uploadedToBinary(\Illuminate\Http\UploadedFile $file): array
    {
        $mime = $file->getMimeType() ?: 'application/octet-stream';
        $allowed = ['image/png', 'image/jpeg', 'image/webp', 'image/svg+xml'];
        if (!in_array($mime, $allowed, true)) {
            throw new \RuntimeException('Only PNG/JPEG/WEBP/SVG are allowed.');
        }
        $bin = file_get_contents($file->getRealPath());
        if ($bin === false) throw new \RuntimeException('Failed to read file.');
        if (strlen($bin) > 250 * 1024) {
            throw new \RuntimeException('Icon size must be less than 250KB.');
        }
        return [$bin, $mime];
    }
}
