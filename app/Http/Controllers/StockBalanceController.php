<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory\Product;
use Yajra\DataTables\Facades\DataTables;

class StockBalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Product::with(['unit', 'type', 'category'])->filter();

            return DataTables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => $r->code,
                        'link' => $r->link,
                        'unit' => d_obj($r, 'unit', 'link'),
                        'type' => d_obj($r, 'type', 'link'),
                        'category' => d_obj($r, 'category', 'link'),
                        'qty_in' => d_link(d_number($r->qty_in), "javascript:get_stockin_history(" . $r->id . ")"),
                        'qty_out' => d_link(d_number($r->qty_out), "javascript:get_stockout_history(" . $r->id . ")"),
                        'qty_alert' => $r->qty_alert,
                        'qty_remain' => ('<span style="color: ' . (d_number($r->qty_remain) == 0 ? 'red' : 'green') . '">' . d_number($r->qty_remain) . '</span>'),
                        'status' => $r->qty_remain == 0 ? d_status(false, 'Out of Stock') : ($r->qty_remain > 0 && $r->qty_remain <= $r->qty_alert ? d_status(false, 'Almost Out of Stock', '', 'badge-warning') : d_status(true)),
                    ];
                })
                ->rawColumns(['dt.status', 'dt.code', 'dt.link', 'dt.unit', 'dt.type', 'dt.category', 'dt.qty_remain', 'dt.qty_in', 'dt.qty_out'])
                ->make(true);
        } else {
            return view('stock_balance.index');
        }
    }
}
