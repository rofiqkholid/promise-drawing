<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerRevisionLabel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerRevisionLabelController extends Controller
{
   
    public function data(Request $request)
    {
        
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);
        $search = trim((string) data_get($request->input('search'), 'value', ''));

        
        $orderReq = $request->input('order.0', []);
        $columns = [
            0 => null,            
            1 => 'c.code',          
            2 => 'crl.label',        
            3 => 'crl.sort_order',   
            4 => 'crl.is_active',   
            5 => null,               
        ];
        $orderCol = (int) data_get($orderReq, 'column', 3);
        $orderDir = data_get($orderReq, 'dir', 'asc') === 'desc' ? 'desc' : 'asc';
        $orderBy  = $columns[$orderCol] ?? 'crl.sort_order';

        
        $base = CustomerRevisionLabel::from('customer_revision_labels as crl')
            ->leftJoin('customers as c', 'c.id', '=', 'crl.customer_id')
            ->selectRaw('crl.*, c.code as customer_code');

    
        $recordsTotal = (clone $base)->count('crl.id');

    
        if ($request->filled('customer_id')) {
            $base->where('crl.customer_id', (int) $request->input('customer_id'));
        }
        if ($search !== '') {
            $base->where(function ($q) use ($search) {
                $q->where('crl.label', 'like', "%{$search}%")
                  ->orWhere('c.code', 'like', "%{$search}%");
            });
        }

    
        $recordsFiltered = (clone $base)->count('crl.id');

        
        $rows = $base->orderBy($orderBy, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        
        $data = $rows->map(function ($r) {
            return [
                'id'            => $r->id,
                'customer_code' => $r->customer_code,
                'label'         => $r->label,
                'sort_order'    => $r->sort_order,
                'is_active'     => (bool) $r->is_active,
            ];
        });

        return response()->json([
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
        ]);
    }

    
    public function dropdowns(Request $request)
{
    $term = (string) $request->input('term', '');    
    $page = (int) $request->input('page', 1);         
    $perPage = 20;

    $query = \App\Models\Customers::query()
       
        ->when($term !== '', fn($q) => $q->where('code', 'like', "%{$term}%"))
        ->orderBy('code');

    $paginator = $query->paginate($perPage, ['id','code'], 'page', $page);

    $results = $paginator->getCollection()
        ->map(fn($c) => ['id' => $c->id, 'text' => $c->code])
        ->values();

    
    return response()->json([
        'results' => $results,
        'pagination' => ['more' => $paginator->hasMorePages()],
    ]);
}


    
    public function show(CustomerRevisionLabel $rev_label)
    {
        $rev_label->load('customer:id,code');
        return response()->json([
            'id'            => $rev_label->id,
            'customer_id'   => $rev_label->customer_id,
            'customer_code' => optional($rev_label->customer)->code,
            'label'         => $rev_label->label,
            'sort_order'    => $rev_label->sort_order,
            'is_active'     => (bool) $rev_label->is_active,
        ]);
    }

    
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_active'] = $request->boolean('is_active');

        $item = CustomerRevisionLabel::create($data);
        return response()->json(['success' => true, 'message' => 'Saved', 'data' => $item]);
    }

   
    public function update(Request $request, CustomerRevisionLabel $rev_label)
    {
        $data = $this->validated($request, $rev_label->id);
        $data['is_active'] = $request->boolean('is_active');

        $rev_label->update($data);
        return response()->json(['success' => true, 'message' => 'Updated']);
    }

   
    public function destroy(CustomerRevisionLabel $rev_label)
    {
        $rev_label->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }


    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'label'       => [
                'required', 'string', 'max:30',
                Rule::unique('customer_revision_labels')
                    ->where(fn ($q) => $q->where('customer_id', $request->customer_id))
                    ->ignore($ignoreId),
            ],
            'sort_order'  => ['nullable', 'integer'],
            'is_active'   => ['nullable'],
        ], [
            'label.unique' => 'Label sudah dipakai untuk customer tersebut.',
        ]);
    }
}
