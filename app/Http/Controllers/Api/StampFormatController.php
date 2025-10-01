<?php

// app/Http/Controllers/Api/StampFormatController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StampFormat;
use Illuminate\Http\Request;

class StampFormatController extends Controller
{
    public function index(Request $r)
    {
        $wantsJson = $r->wantsJson()
            || str_contains((string)$r->header('Accept'), 'application/json')
            || $r->query('format') === 'json';

        if ($wantsJson) {
            $q = StampFormat::query();

            // search: prefix / suffix
            if ($s = trim((string)$r->get('search'))) {
                $q->where(fn($w) => $w->where('prefix', 'like', "%{$s}%")
                                      ->orWhere('suffix', 'like', "%{$s}%"));
            }

            // sort
            $allowed = ['prefix','suffix','is_active','created_at'];
            $sortBy  = in_array($r->get('sort_by'), $allowed, true) ? $r->get('sort_by') : 'prefix';
            $sortDir = $r->get('sort_dir') === 'desc' ? 'desc' : 'asc';
            $q->orderBy($sortBy, $sortDir);

            return response()->json([
                'data' => $q->get(['id','prefix','suffix','is_active','created_at']),
            ]);
        }

        // sesuaikan dengan nama blade milik Tuan
        return view('master.stampFormat');
    }

    public function show($id)
    {
        return response()->json(
            StampFormat::findOrFail($id, ['id','prefix','suffix','is_active'])
        );
    }

    public function store(Request $r)
    {
        $val = $r->validate([
            'prefix'    => ['nullable','string','max:50'],
            'suffix'    => ['nullable','string','max:50'],
            'is_active' => ['nullable','boolean'],
        ]);

        // pastikan checkbox unchecked menjadi 0
        $val['is_active'] = $r->boolean('is_active');

        StampFormat::create($val);

        if ($r->wantsJson() || str_contains((string)$r->header('Accept'), 'application/json')) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('stampFormat.index')->with('success','Saved');
    }

    public function update(Request $r, $id)
    {
        $row = StampFormat::findOrFail($id);

        $val = $r->validate([
            'prefix'    => ['nullable','string','max:50'],
            'suffix'    => ['nullable','string','max:50'],
            'is_active' => ['nullable','boolean'],
        ]);
        $val['is_active'] = $r->boolean('is_active');

        $row->update($val);

        if ($r->wantsJson() || str_contains((string)$r->header('Accept'), 'application/json')) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('stampFormat.index')->with('success','Updated');
    }

    public function destroy(Request $r, $id)
    {
        StampFormat::whereKey($id)->delete();

        if ($r->wantsJson() || str_contains((string)$r->header('Accept'), 'application/json')) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('stampFormat.index')->with('success','Deleted');
    }
}
