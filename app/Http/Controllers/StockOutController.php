<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use App\Models\Inventory\StockOut;
use App\Http\Requests\StockOutRequest;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StockOutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // if ($request->ajax()) {
        //     return $data['rows'];
        // }

        if ($request->ajax()) {
            $data = StockOut::with(['product.unit', 'unit'])->stockFilter();

            return DataTables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'date' => d_date($r->date, 'Y-m-d'),
                        'document_no' => d_text($r->document_no),
                        'code' => d_obj($r, 'product', 'code'),
                        'product' => d_obj($r, 'product', 'link'),
                        'qty' => d_number($r->qty),
                        'unit' => d_obj($r, 'unit', 'link'),
                        'price' => d_currency($r->price),
                        'total' => d_currency($r->total),
                        'qty_based' => d_number($r->qty_based),
                        'p_unit' => d_obj($r, 'product', 'unit', 'link'),
                        'type' => d_text($r->type),
                        'action' => d_action([
                            'moduleAbility' => $module_ability ?? 'StockOut', 'module' => $module ?? 'inventory.stock_out', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed(), 'showBtnShow' => false,
                            'disableDelete' => true,
                        ]),
                    ];
                })
                ->rawColumns(['dt.status', 'dt.product', 'dt.action', 'dt.unit', 'dt.p_unit'])
                ->make(true);
        } else {
            return view('stock_out.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [];
        return view('stock_out.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockOutRequest $request)
    {
        $validator = $this->storeStockOut($request);
        if ($validator->errors()) {
            return redirect()->route('inventory.stock_out.index')->withErrors($validator);
        }
        return redirect()->route('inventory.stock_out.index')->with('success', __('alert.message.success.crud.create'));
    }

    static function storeStockOut($request, $type = 'StockOut')
    {
        $validator = Validator::make([], []);
        $allProducts = Product::with([
            'stockins' => function ($q) {
                $q->where('qty_remain', '>', 0)->orderBy('date');
            }
        ])->whereIn('id', $request->input('product_id', []))->get();

        foreach ($request->input('product_id', []) as $index => $value) {
            if ($product = $allProducts->where('id', $request->product_id[$index] ?? '')->first()) {
                if ($product->stockins->sum('qty_remain') >= $request->qty_based[$index]) {
                    $req = (object)[
                        'type' => $type,
                        'date' => $request->date[$index],
                        'document_no' => generate_code('SO', 'stock_outs'),
                        'product_id' => $request->product_id[$index],
                        'unit_id' => $request->unit_id[$index],
                        'price' => $request->price[$index],
                        'qty_based' => $request->qty_based[$index],
                        'qty' => $request->qty[$index],
                        'note' => $request->note[$index],
                        'total' => $request->total[$index],
                    ];
                    $product->deductStock($request->qty[$index], $request->unit_id[$index], $req);
                } else {
                    // If requested stock is larger then stock available add error for msg
                    $validator->errors()->add($index, 'Insufficient stock on product: ' . d_obj($product, ['name_kh', 'name_en']) . '! total requested stock is ' . d_number($request->qty_based[$index]) . ' but total stock available is ' . d_number($product->stockins->sum('qty_remain')));
                }
            }
        }
        return $validator;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOut $stockOut)
    {
        $data = [
            'row' => $stockOut
        ];
        return view('stock_out.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockOutRequest $request, StockOut $stockOut)
    {
        if ($this->updateStockOut($stockOut, $request)) {
            return redirect()->route('inventory.stock_out.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    public function updateStockOut($stockOut, $request)
    {
        return $stockOut->update([
            'date' => $request->date,
            // 'document_no' => $request->reciept_no,
            'price' => $request->price,
            'total' => ($stockOut->qty * $request->price),
            'note' => $request->note,
        ]);
    }

    /**
     * Remove the specified resource to trash.
     */
    public function destroy(StockOut $stockOut)
    {
        $product =  $stockOut->product;
        if ($this->deleteStockOut($stockOut)) {
            $product->updateQty();
            return redirect()->route('inventory.stock_out.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $row = StockOut::onlyTrashed()->findOrFail($id);
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
        $row = StockOut::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }

    public function deleteStockOut($stockOut)
    {
        foreach ($stockOut->stock_ins as $stockIn) {
            $stockIn->update([
                'qty_used' => $stockIn->qty_used - ($stockIn->pivot->qty ?? 0),
                'qty_remain' => $stockIn->qty_remain + ($stockIn->pivot->qty ?? 0),
            ]);
        }
        $stockOut->stock_ins()->sync([]);

        return $stockOut->delete();
    }
}
