<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\Models;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductsController extends Controller
{
    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw');
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $orderObs = $request->input('order', []);
        
        $orderColIdx = (int) ($orderObs[0]['column'] ?? 0);
        $orderDir    = $orderObs[0]['dir'] ?? 'desc';

        $columnsMap = [
            0 => 'p.id',
            1 => 'c.code',
            2 => 'm.name',
            3 => 'ps.name',
            4 => 'p.part_no',
            5 => 'p.part_name',
        ];
        $orderCol = $columnsMap[$orderColIdx] ?? 'p.id';

        $query = DB::table('products as p')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->where('p.is_delete', 0)
            ->select([
                'p.id',
                'p.customer_id',
                'p.model_id',
                'p.part_no',
                'p.part_name',
                'p.group_id',
                DB::raw("COALESCE(m.name,'') as model_name"),
                DB::raw("COALESCE(c.code,'') as customer_code"),
                DB::raw("COALESCE(ps.name,'No Status') as status_name"),
            ]);

        $recordsTotal = (clone $query)->count();

        // Global Search
        $allParams = $request->all();
        $search = $allParams['search'] ?? '';
        $searchValue = is_string($search) ? $search : ($search['value'] ?? '');
        
        if (!empty($searchValue)) {
            $terms = explode(' ', $searchValue);
            $query->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $t = trim($term);
                    if ($t === '') continue;
                    $q->where(function ($sub) use ($t) {
                        $sub->where('p.part_no', 'like', "%{$t}%")
                            ->orWhere('p.part_name', 'like', "%{$t}%")
                            ->orWhere('m.name', 'like', "%{$t}%")
                            ->orWhere('c.code', 'like', "%{$t}%")
                            ->orWhere('ps.name', 'like', "%{$t}%");
                    });
                }
            });
        }


        $recordsFiltered = $query->count();

        $data = $query->orderByRaw("$orderCol $orderDir")
            ->skip($start)
            ->take($length)
            ->get()
            ->map(fn($r) => [
                'id'            => $r->id,
                'customer_id'   => $r->customer_id,
                'customer_code' => $r->customer_code,
                'model_id'      => $r->model_id,
                'model_name'    => $r->model_name,
                'status'        => $r->status_name, 
                'part_no'       => $r->part_no,
                'part_name'     => $r->part_name,
                'group_id'      => $r->group_id,
            ]);

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }



    public function getModels(Request $request)
    {
        $customerId = $request->get('customer_id');
        $searchTerm = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip = ($page - 1) * $limit;

        $query = DB::connection('sqlsrv')
            ->table('models as m')
            ->leftJoin('project_status as ps', 'm.status_id', '=', 'ps.id')
            ->select('m.id', 'm.name', 'm.customer_id', 'ps.name as status_name');

        if (!empty($customerId)) {
            $query->where('m.customer_id', (int)$customerId);
        }

        if ($searchTerm !== '') {
            $query->where('m.name', 'LIKE', "%{$searchTerm}%");
        }

        $totalCount = $query->count();

        $results = $query->orderBy('m.name')
            ->skip($skip)
            ->take($limit)
            ->get();

        $formattedResults = $results->map(fn($model) => [
            'id'   => $model->id,
            'text' => "{$model->name} - {$model->status_name}",
            'customer_id' => $model->customer_id,
            'status' => $model->status_name,
        ]);

        return response()->json([
            'results' => $formattedResults,
            'pagination' => [
                'more' => ($page * $limit) < $totalCount
            ]
        ]);
    }


    // ===== Select2-friendly: Customers =====
    public function getCustomers(Request $request)
    {
        $q     = trim((string) $request->get('q', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $limit = 10;
        $skip  = ($page - 1) * $limit;

        $base = Customers::query()->select('id', 'code');
        if ($q !== '') {
            $base->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%");
            });
        }

        if ($request->has('q') || $request->has('page') || $request->wantsJson()) {
            $total = (clone $base)->count();
            $rows  = $base->orderBy('code')->skip($skip)->take($limit)->get();

            return response()->json([
                'results' => $rows->map(fn($c) => [
                    'id'   => $c->id,
                    'text' => $c->code,
                ]),
                'pagination' => ['more' => ($skip + $limit) < $total],
            ]);
        }

        // Fallback "mode lama"
        return $base->orderBy('code')
            ->get()
            ->map(fn($c) => ['id' => $c->id, 'code' => $c->code]);
    }

    public function getPairableProducts(Request $request)
    {
        $term = trim($request->get('q', ''));
        $customerId = $request->get('customer_id');

        $query = Products::query()
            ->select('products.id', 'products.part_name', 'products.part_no', 'products.group_id')
            ->where('is_delete', 0)

            ->when($customerId, function ($q) use ($customerId) {
                return $q->where('customer_id', $customerId);
            })
            ->when($request->has('exclude_id'), function ($q) use ($request) {
                return $q->where('products.id', '!=', $request->exclude_id);
            })
            ->where(function ($q) use ($term) {
                $q->where('part_no', 'like', "%{$term}%")
                    ->orWhere('part_name', 'like', "%{$term}%");
            });

        $products = $query->limit(20)->get();

        $results = $products->map(function ($p) {
            $status = $p->group_id ? ' (Paired)' : ' (Single)';
            return [
                'id' => $p->id,
                'text' => $p->part_no . ' - ' . $p->part_name . $status,
                'group_id' => $p->group_id
            ];
        });

        return response()->json(['results' => $results]);
    }

    public function store(Request $r)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'    => ['required', 'exists:models,id'],
            'part_no'     => ['required', 'string', 'max:20'],
            'part_name'   => ['required', 'string', 'max:50'],
            'partner_id'  => ['nullable', 'exists:products,id'],
        ]);

        DB::beginTransaction();
        try {
            $groupId = null;

            // Logic Pairing
            if (!empty($v['partner_id'])) {
                $partner = Products::find($v['partner_id']);
                if ($partner) {
                    if ($partner->group_id) {
                        $groupId = $partner->group_id;
                    } else {
                        $groupId = \Illuminate\Support\Str::uuid()->toString();
                        $partner->update(['group_id' => $groupId]);
                    }
                }
            }

            $product = Products::create([
                'customer_id' => $v['customer_id'],
                'model_id'    => $v['model_id'],
                'part_no'     => $v['part_no'],
                'part_name'   => $v['part_name'],
                'group_id'    => $groupId
            ]);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $row = DB::table('products as p')
            ->leftJoin('customers as c', 'c.id', '=', 'p.customer_id')
            ->leftJoin('models as m', 'm.id', '=', 'p.model_id')
            ->where('p.id', $id)
            ->where('p.is_delete', 0)
            ->selectRaw("
            p.id,
            p.customer_id,
            p.model_id,
            p.part_no,
            p.part_name,
            p.group_id,
            ISNULL(c.code, '')  AS customer_label,
            ISNULL(m.name, '')  AS model_label
        ")
            ->first();

        abort_if(!$row, 404);
    
        $partnerVal  = null;
        $partnerText = null;

        if ($row->group_id) {
            $partners = DB::table('products')
                ->where('group_id', $row->group_id)
                ->where('id', '!=', $row->id)
                ->where('is_delete', 0)
                ->select('id', 'part_no', 'part_name')
                ->get();
            
            if ($partners->isNotEmpty()) {
                $partnerVal  = $partners->first()->id;
                $partnerText = $partners->map(function($p) {
                    return [
                        'id' => $p->id,
                        'text' => $p->part_no . ' - ' . $p->part_name
                    ];
                });
            }
        }

        $row->partner_id    = $partnerVal;
        $row->partner_label = $partnerText;

        return response()->json($row);
    }


    public function update(Request $r, $id)
    {
        $v = $r->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'model_id'    => ['required', 'exists:models,id'],
            'part_no'     => ['required', 'string', 'max:20'],
            'part_name'   => ['required', 'string', 'max:50'],
            'partner_id'  => ['nullable', 'exists:products,id'], 
            'unlink_pair' => ['nullable', 'boolean'],
            'create_new_partner' => ['nullable', 'boolean'],
            'new_partner_part_no' => ['required_if:create_new_partner,1', 'nullable', 'string', 'max:20', 'unique:products,part_no'],
            'new_partner_part_name' => ['required_if:create_new_partner,1', 'nullable', 'string', 'max:50'],
        ]);

        DB::beginTransaction();
        try {
            $product = Products::findOrFail($id);
            
            $dataToUpdate = [
                'customer_id' => $v['customer_id'],
                'model_id'    => $v['model_id'],
                'part_no'     => $v['part_no'],
                'part_name'   => $v['part_name'],
            ];

            if (!empty($v['unlink_pair']) && $v['unlink_pair'] == true) {
                $oldGroupId = $product->group_id;
                $dataToUpdate['group_id'] = null;
                
                if ($oldGroupId) {
                     $remainingCount = Products::where('group_id', $oldGroupId)->where('id', '!=', $id)->count();
                     if ($remainingCount <= 1) {
                         Products::where('group_id', $oldGroupId)->update(['group_id' => null]);
                     }
                }
            }
            elseif (!empty($v['create_new_partner']) && $v['create_new_partner'] == true) {
                 // Determine Group ID
                 $groupId = $product->group_id;
                 if (!$groupId) {
                     $groupId = \Illuminate\Support\Str::uuid()->toString();
                     $dataToUpdate['group_id'] = $groupId;
                 }
                 
                 // Create new partner
                 Products::create([
                     'customer_id' => $v['customer_id'], 
                     'model_id'    => $v['model_id'],    
                     'part_no'     => $v['new_partner_part_no'],
                     'part_name'   => $v['new_partner_part_name'],
                     'group_id'    => $groupId
                 ]);
            }
            elseif (!empty($v['partner_id'])) {
                $partner = Products::find($v['partner_id']);
                if ($partner && $partner->id != $product->id) {
                    
                    if ($partner->group_id) {
                        $dataToUpdate['group_id'] = $partner->group_id;
                    } 
                    elseif ($product->group_id) {
                        $partner->update(['group_id' => $product->group_id]);
                        $dataToUpdate['group_id'] = $product->group_id;
                    } 
                    else {
                        $newGroup = \Illuminate\Support\Str::uuid()->toString();
                        $partner->update(['group_id' => $newGroup]);
                        $dataToUpdate['group_id'] = $newGroup;
                    }
                }
            } 

            $product->update($dataToUpdate);

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        Products::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
