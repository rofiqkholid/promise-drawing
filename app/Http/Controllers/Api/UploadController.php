<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UploadController extends Controller
{
    public function listFiles(Request $request): JsonResponse
    {
        // Dummy data for DataTable
        $dummyData = [
            [
                'id' => 1,
                'customer' => 'MMKI',
                'model' => '5JH5',
                'part_no' => '5251D644',
                'revision' => 'Rev1',
                'uploaded_at' => '2025-01-01 12:00:00',
                'status' => 'Rejected'
            ],
            [
                'id' => 2,
                'customer' => 'XYZ Corp',
                'model' => '7KL2',
                'part_no' => '9876F321',
                'revision' => 'Rev2',
                'uploaded_at' => '2025-01-02 13:00:00',
                'status' => 'Approved'
            ],
            [
                'id' => 3,
                'customer' => 'ABC Ltd',
                'model' => '9MN4',
                'part_no' => '1234G789',
                'revision' => 'Rev3',
                'uploaded_at' => '2025-01-03 14:00:00',
                'status' => 'Pending'
            ]
        ];

        return response()->json([
            'data' => $dummyData
        ]);
    }
}
