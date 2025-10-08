<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ExportController extends Controller
{
    /**
     * Menyediakan daftar file yang dapat diunduh dalam format JSON untuk DataTables.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listDownloadableFiles(Request $request): JsonResponse
    {
        // Data dummy untuk halaman download/export (tanpa file_name)
        $dummyData = [
            [
                'id' => 1,
                'customer' => 'MMKI',
                'model' => '4L45W',
                'part_no' => '5251D644',
                'doc_type' => 'Part DWG',
                'sub_category' => '2D',
                'revision' => 'Rev2'
            ],
            [
                'id' => 2,
                'customer' => 'XYZ Corp',
                'model' => '7KL2',
                'part_no' => '9876F321',
                'doc_type' => 'Assy DWG',
                'sub_category' => '3D',
                'revision' => 'Rev3'
            ],
            [
                'id' => 3,
                'customer' => 'ABC Ltd',
                'model' => '9MN4',
                'part_no' => '1234G789',
                'doc_type' => 'Specification',
                'sub_category' => 'Document',
                'revision' => 'Rev2'
            ],
            [
                'id' => 4,
                'customer' => 'MMKI',
                'model' => '5JH5',
                'part_no' => '5251D655',
                'doc_type' => 'Setup Sheet',
                'sub_category' => 'NC Data',
                'revision' => 'Rev1'
            ],
            [
                'id' => 5,
                'customer' => 'XYZ Corp',
                'model' => '7KL2',
                'part_no' => '9876F321',
                'doc_type' => 'Inspection Report',
                'sub_category' => 'QA',
                'revision' => 'Rev3'
            ],
        ];

        return response()->json([
            'data' => $dummyData
        ]);
    }
}
