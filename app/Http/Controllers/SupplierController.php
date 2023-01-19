<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory\Supplier;
use Illuminate\Support\Facades\File;
use App\Models\Inventory\ProductType;
use App\Http\Requests\SupplierRequest;
use App\Models\Inventory\ProductCategory;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->_type == 'query') {
                // For select2 Ajx
                $term = Supplier::where('name_en', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('name_kh', 'LIKE', '%' . $request->term . '%')
                    ->limit(100)->get(['id', 'name_kh', 'name_en']);

                $result = [];
                foreach ($term as $t) {
                    $result[] = [
                        'id' => $t->id,
                        'text' => d_obj($t, ['name_kh', 'name_en']),
                    ];
                }
                return ['results' => $result];
            }
            $data = Supplier::with(['user', 'type', 'category'])->withCount('stockins')->filter();

            return DataTables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'type' => d_obj($r, 'type', 'link'),
                        'category' => d_obj($r, 'category', 'link'),
                        'contact_name' => d_text($r->contact_name),
                        'contact_number' => d_text($r->contact_number),
                        'address' => d_obj($r, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh'] ),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),

                        'action' => d_action([
                            'module' => "inventory.supplier",
                            'moduleAbility' => "Supplier",
                            'id' => $r->id,
                            'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed(),
                            'disableDelete' => $r->stockins_count > 0,
                            'showBtnShow' => false,
                        ]),
                    ];
                })
                ->rawColumns(['dt.status', 'dt.code', 'dt.unit', 'dt.type', 'dt.category', 'dt.qty_remain', 'dt.user', 'dt.action'])
                ->make(true);
        } else {
            return view('supplier.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'categories' => ProductCategory::where('status', 1)->orderBy('name_en', 'asc')->get(),
            'types' => ProductType::where('status', 1)->orderBy('name_en', 'asc')->get(),
        ];
        return view('supplier.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SupplierRequest $request)
    {
        $address_id = update4LevelAddress($request);
        if ($supplier = Supplier::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'contact_name' => $request->contact_name,
            'contact_number' => $request->contact_number,
            'description' => $request->description,
            'payment_info' => $request->payment_info,
            'other' => $request->other,
            'type_id' => $request->type_id,
            'category_id' => $request->category_id,
            'address_id' => $address_id
        ])) {

            // Check if no exist folder/directory then create folder/directory
            $path = public_path('/images/suppliers/');
            File::makeDirectory($path, 0777, true, true);
            $logo =  create_image($request->logo, $path, (time() . '_logo_' . rand(111, 999) . '.png'));
            $supplier->update(['logo' => $logo]);

            return redirect()->route('inventory.supplier.index')->with('success', __('alert.message.success.crud.create'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        $data = [
            'row' => $supplier,
            'categories' => ProductCategory::where('status', 1)->orderBy('name_en', 'asc')->get(),
            'types' => ProductType::where('status', 1)->orderBy('name_en', 'asc')->get(),
        ];
        return view('supplier.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $address_id = update4LevelAddress($request, $supplier->address_id);
        // Check if no exist folder/directory then create folder/directory
        $path = public_path('images/suppliers/');
        File::makeDirectory($path, 0777, true, true);
        $logo = update_image($request->logo, $path, (time() . '_logo_' . rand(111, 999) . '.png'), $supplier->logo);
        if ($supplier->update(array_merge(
            ['logo' => $logo],
            [
                'name_en' => $request->name_en,
                'name_kh' => $request->name_kh,
                'contact_name' => $request->contact_name,
                'contact_number' => $request->contact_number,
                'description' => $request->description,
                'payment_info' => $request->payment_info,
                'other' => $request->other,
                'type_id' => $request->type_id,
                'category_id' => $request->category_id,
                'address_id' => $address_id
            ]
        ))) {

            return redirect()->route('inventory.supplier.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource to trash.
     */
    public function destroy(Supplier $supplier)
    {
        if ($supplier->delete()) {
            return redirect()->route('inventory.supplier.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $row = Supplier::onlyTrashed()->findOrFail($id);
        if ($row->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Force Delete the specified resource from storage.
     */
    public function force_delete($id)
    {
        $row = Supplier::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }

    public function getProduct(Request $request)
    {
        $supplier = Supplier::with([
            'category.products' => function($q) {
                $q->orderBy('name_en');
            }
        ])->findOrFail($request->id);
        $options = '<option value="">---- None ----</option>';
        foreach ($supplier->category->products ?? [] as $product) {
            $options .= '<option value="'. $product->id .'" data-remain="'. $product->qty_remain .'">'. d_obj($product, ['name_kh', 'name_en']) .'</option>';
        }

        return response()->json([
            'success' => true,
            'supplier' => $supplier,
            'options' => $options,
        ]);
    }
}
