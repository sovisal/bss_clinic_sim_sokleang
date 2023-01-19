<?php

namespace App\Http\Controllers;

use App\Models\LaborType;
use App\Http\Requests\LaborTypeRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class LaborTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = LaborType::with('user')->with('parent')->withCount('items');

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'index' => d_number($r->index),
                        'parent' => d_obj($r, 'parent', ['name_en', 'name_kh']),
                        'items_count' => d_badge($r->items_count),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'module' => 'setting.labor-type', 'moduleAbility' => 'LaborType', 
                            'disableEdit' => $r->trashed() || $r->status != '1',
                            'disableDelete' => $r->items_count > 0 || $r->status != 1,
                            'showCustomBtn' => ['ViewAnyLaborItem', route('setting.labor-item.index', $r->id)]
                        ]),
                    ];
                })
                ->rawColumns(['dt.items_count', 'dt.status', 'dt.action'])
                ->make(true);
        } else {
            return view('labor_type.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['index'] = LaborType::getNextIndex();
        $data['parents'] = LaborType::select(['id', 'name_en', 'name_kh'])
            ->where('parent_id', null)
            ->orderBy('index', 'asc')->get()
            ->map(function ($parent) {
                $parent->rendered_name = d_obj($parent, ['name_en', 'name_kh']);
                return $parent;
            })
            ->pluck('rendered_name', 'id');
        return view('labor_type.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LaborTypeRequest $request)
    {
        LaborType::create([
            'name_kh' => $request->name_kh,
            'name_en' => $request->name_en,
            'index' => $request->index,
            'parent_id' => $request->parent_id,
        ]);
        return redirect(route('setting.labor-type.index'))->with('success', __('alert.message.success.crud.create'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaborType $laborType)
    {
        $data['row'] = $laborType;
        $data['parents'] = LaborType::select(['id', 'name_en', 'name_kh'])
            ->where('parent_id', null)
            ->where('id', '!=', $laborType->id)
            ->orderBy('index', 'asc')->get()
            ->map(function ($parent) {
                $parent->rendered_name = d_obj($parent, ['name_en', 'name_kh']);
                return $parent;
            })
            ->pluck('rendered_name', 'id');
        return view('labor_type.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LaborTypeRequest $request, LaborType $laborType)
    {
        $laborType->update([
            'name_kh' => $request->name_kh,
            'name_en' => $request->name_en,
            'index' => $request->index,
            'parent_id' => $request->parent_id,
        ]);
        return redirect(route('setting.labor-type.index'))->with('success', __('alert.message.success.crud.update'));
    }

    public function sort_order()
    {
        $data['rows'] = LaborType::where('status', 1)->orderBy('index', 'asc')->get();
        $data['url'] = route('setting.labor-type.update_order');
        $data['back_url'] = route('setting.labor-type.index');
        return view('shared.setting_service.order', $data);
    }

    public function update_order(Request $request)
    {
        LaborType::saveOrder($request);
        return redirect(route('setting.labor-type.index'))->with('success', __('alert.message.success.sort'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaborType $laborType)
    {
        if ($laborType->delete()) {
            return redirect(route('setting.labor-type.index'))->with('success', 'Data delete success');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $laborType = LaborType::onlyTrashed()->findOrFail($id);
        if ($laborType->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $laborType = LaborType::onlyTrashed()->findOrFail($id);
        if ($laborType->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
