<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use App\Models\Inventory\StockOut;
use App\Http\Requests\StockOutRequest;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\StockOutController;
use Yajra\DataTables\Facades\DataTables;

class StockAdjustmentController extends Controller
{
    protected $stockOut;
    public function __construct(StockOutController $stockOut)
    {
        $this->stockOut = $stockOut;
        $this->data = [
            'title' => 'Stock Adjustment',
            'module' => 'inventory.stock_adjustment',
            'module_ability' => 'StockAdjustment'
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = StockOut::with(['user', 'product', 'product.unit'])->where('type', 'StockAdjustment')->stockFilter();

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
            return view('stock_out.index', $this->data);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->data += [
            'products' => Product::where('status', 1)->where('qty_remain', '>', '0')->orderBy('name_en', 'asc')->get(),
        ];
        return view('stock_out.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StockOutRequest $request)
    {
        $validator = StockOutController::storeStockOut($request, 'StockAdjustment');
        if ($validator->errors()) {
            return redirect()->route('inventory.stock_adjustment.index')->withErrors($validator);
        }
        return redirect()->route('inventory.stock_adjustment.index')->with('success', __('alert.message.success.crud.create'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(StockOut $stockOut)
    {
        $this->data += [
            'products' => Product::where('status', 1)->where('qty_remain', '>', '0')->orderBy('name_en', 'asc')->get(),
            'row' => $stockOut
        ];
        return view('stock_out.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StockOutRequest $request, StockOut $stockOut)
    {
        if ($this->stockOut->updateStockOut($stockOut, $request)) {
            return redirect()->route('inventory.stock_adjustment.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource to trash.
     */
    public function destroy(StockOut $stockOut)
    {
        if ($stockOut->delete()) {
            return redirect()->route('inventory.stock_adjustment.index')->with('success', __('alert.message.success.crud.delete'));
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
}
