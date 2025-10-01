<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mengambil data Document Group untuk filter AJAX.
     * Jika terjadi error, Laravel akan menanganinya secara default.
     */
    public function getDocumentGroups(Request $request): JsonResponse
    {
        $documentGroups = DB::table('doctype_groups')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($documentGroups);
    }
}
