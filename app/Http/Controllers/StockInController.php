<?php

namespace App\Http\Controllers;

use App\Models\Inventory\Product;
use App\Models\Inventory\StockIn;
use App\Models\Inventory\Supplier;
use App\Http\Requests\StockInRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockInController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            request()->merge([
                'ft_daterangepicker_drp_start' => request()->ft_daterangepicker_drp_start ?? date('Y-m-01'),
                'ft_daterangepicker_drp_end' => request()->ft_daterangepicker_drp_end ?? date('Y-m-t'),
            ]);

            $data = StockIn::with(['unit', 'supplier', 'product.unit'])
                ->withCount('stock_outs')
                ->stockFilter()
                ->orderBy('date', 'desc');

            return DataTables::of($data)
            ->addColumn('dt', function ($r) {
                return [
                    'date' => d_date($r->date, 'Y-m-d'),
                    'code' => d_obj($r, 'product', 'code'),
                    'product' => d_obj($r, 'product', 'link'),
                    'supplier' => d_obj($r, 'supplier', 'link'),
                    'qty' => d_number($r->qty),
                    'unit' => d_obj($r, 'unit', 'link'),
                    'price' => d_currency($r->price),
                    'total' => d_currency($r->total),
                    'qty_based' => d_number($r->qty_based),
                    'p_unit' => d_obj($r, 'product', 'unit', 'link'),
                    'qty_used' => d_number($r->qty_used),
                    'qty_remain' => d_number($r->qty_remain),
                    'exp_status' => $r->exp_date > date('Y-m-d') ? d_status(true, '', d_date($r->exp_date, 'Y-m-d'), '', 'badge-success') : d_status(false, d_date($r->exp_date, 'Y-m-d')),
                    'reciept_no' => d_text($r->reciept_no),
                    'status' => $r->qty_remain > 0 ? d_status(true, '', 'Stock Active') : d_status(false, 'Stock Closed', '', 'badge-light'),
                    'action' => d_action([
                        'module' => "inventory.stock_in",
                        'moduleAbility' => "StockIn",
                        'id' => $r->id,
                        'isTrashed' => $r->trashed(),
                        'disableEdit' => $r->trashed(),
                        'disableDelete' => $r->stock_outs_count > 0 || $r->qty_used > 0,
                        'showBtnShow' => false,
                    ]),
                ];
            })
            ->rawColumns(['dt.status', 'dt.product', 'dt.supplier', 'dt.unit', 'dt.p_unit', 'dt.exp_status', 'dt.status', 'dt.action'])
            ->make(true);
        } else {
            $data['suppliers'] = Supplier::where('id', request()->ft_supplier_id)->get();
            return view('stock_in.index', $data);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'suppliers' => [],
            'products' => [],
        ];
        return view('stock_in.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockInRequest $request)
    {
        $allProducts = Product::whereIn('id', $request->input('product_id', []))->get();

        foreach ($request->input('product_id', []) as $index => $value) {
            if ($product = $allProducts->where('id', $request->product_id[$index])->first()) {
                $stockIn = StockIn::create([
                    'type' => 'StockIn',
                    'date' => $request->date[$index] ?? date('Y-m-d'),
                    'exp_date' => $request->exp_date[$index] ?? null,
                    'reciept_no' => $request->reciept_no[$index] ?? '',
                    'price' => $request->price[$index] ?? 0,
                    'qty' => $request->qty[$index] ?? 0,
                    'qty_remain' => $request->qty_based[$index] ?? 0,
                    'qty_based' => $request->qty_based[$index] ?? 0,
                    'qty_remain' => $request->qty_based[$index] ?? 0,
                    'total' => $request->total[$index] ?? 0,
                    'supplier_id' => $request->supplier_id[$index] ?? null,
                    'product_id' => $request->product_id[$index] ?? null,
                    'unit_id' => $request->unit_id[$index] ?? null,
                ]);

                $stockIn->product()->first()->updateQty();
            }
        }
        return redirect()->route('inventory.stock_in.index')->with('success', __('alert.message.success.crud.create'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockIn $stockIn)
    {
        $data = [
            'row' => $stockIn
        ];
        return view('stock_in.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockInRequest $request, StockIn $stockIn)
    {
        if ($stockIn->update([
            'date' => $request->date,
            'exp_date' => $request->exp_date,
            'reciept_no' => $request->reciept_no,
            'price' => $request->price,
            'total' => ($stockIn->qty * $request->price),
        ])) {
            
            $stockIn->product()->first()->updateQty();
            return redirect()->route('inventory.stock_in.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource to trash.
     */
    public function destroy(StockIn $stockIn)
    {
        if ($stockIn->delete()) {
            $stockIn->product()->first()->updateQty();
            return redirect()->route('inventory.stock_in.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        // $row = StockIn::onlyTrashed()->findOrFail($id);
        // if ($row->restore()) {
        //     return back()->with('success', __('alert.message.success.crud.restore'));
        // }
        // return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Force Delete the specified resource from storage.
     */
    public function force_delete($id)
    {
        // $row = StockIn::onlyTrashed()->findOrFail($id);
        // if ($row->forceDelete()) {
        //     return back()->with('success', __('alert.message.success.crud.force_detele'));
        // }
        // return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
