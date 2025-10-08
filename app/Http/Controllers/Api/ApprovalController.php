<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApprovalController extends Controller
{
    /**
     * Menyediakan daftar file untuk approval dalam format JSON.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listApprovals(Request $request): JsonResponse
    {
        // Data dummy untuk halaman approval
        $dummyData = [
            [
                'id' => 1,
                'customer' => 'MMKI',
                'model' => '4L45W',
                'doc_type' => 'Part DWG',
                'category' => '2D',
                'part_no' => '5251D644',
                'revision' => 'Rev1',
                'status' => 'Reject'
            ],
            [
                'id' => 2,
                'customer' => 'XYZ Corp',
                'model' => '7KL2',
                'doc_type' => 'Assy DWG',
                'category' => '3D',
                'part_no' => '9876F321',
                'revision' => 'Rev3',
                'status' => 'Waiting'
            ],
            [
                'id' => 3,
                'customer' => 'ABC Ltd',
                'model' => '9MN4',
                'doc_type' => 'Specification',
                'category' => 'Document',
                'part_no' => '1234G789',
                'revision' => 'Rev2',
                'status' => 'Complete'
            ],
            [
                'id' => 4,
                'customer' => 'MMKI',
                'model' => '5JH5',
                'doc_type' => 'Setup Sheet',
                'category' => 'NC Data',
                'part_no' => '5251D655',
                'revision' => 'Rev1A',
                'status' => 'Waiting'
            ]
        ];

        return response()->json(['data' => $dummyData]);
    }


    public function showDetail($id)
    {

        return view('approvals.approval_detail', [
            'approvalId' => $id,
            // 'approvalData' => $approvalData,
        ]);
    }
}
