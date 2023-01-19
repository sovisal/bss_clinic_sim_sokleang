<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory\ProductUnit;
use App\Http\Requests\ProductUnitRequest;
use Yajra\DataTables\Facades\DataTables;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ProductUnit::with(['user'])->withCount('products')->withCount('packages');

            return DataTables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'description' => $r->description,
                        'products_count' => d_badge($r->products_count),
                        'packages_count' => d_badge($r->packages_count),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'moduleAbility' => 'ProductUnit', 'module' => 'setting.unit', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed(), 'showBtnShow' => false,
                            'disableDelete' => $r->products_count > 0 || $r->packages_count > 0,
                        ]),
                    ];
                })
                ->rawColumns(['dt.status', 'dt.products_count', 'dt.action', 'dt.packages_count'])
                ->make(true);
        } else {
            return view('unit.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('unit.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductUnitRequest $request)
    {
        if (ProductUnit::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'description' => $request->description,
        ])) {
            return redirect()->route('setting.unit.index')->with('success', __('alert.message.success.crud.create'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProductUnit $productUnit)
    {
        return view('unit.edit', ['row' => $productUnit]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductUnitRequest $request, ProductUnit $productUnit)
    {
        if ($productUnit->update([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'description' => $request->description,
        ])) {
            return redirect()->route('setting.unit.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource to trash.
     */
    public function destroy(ProductUnit $productUnit)
    {
        if ($productUnit->delete()) {
            return redirect()->route('setting.unit.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Restore the specified resource from storage.
     */
    public function restore($id)
    {
        $row = ProductUnit::onlyTrashed()->findOrFail($id);
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
        $row = ProductUnit::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
