<?php

namespace App\Http\Controllers;

use App\Models\LaborItem;
use App\Models\LaborType;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\LaborItemRequest;
use Yajra\DataTables\Facades\DataTables;

class LaborItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, LaborType $laborType)
    {
        if ($request->ajax()) {
            $data = $laborType->items()->with('user')->withCount('labor_details');

            return Datatables::of($data)
                ->addColumn('dt', function ($r) use ($laborType) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'range' => d_labor_range($r->min_range, $r->max_range) .' '. apply_markdown_character($r->unit),
                        'index' => d_number(Str::after($r->index, '.')),
                        'type' => d_obj($r, 'type', ['name_en', 'name_kh']),
                        'labor_details_count' => d_badge($r->labor_details_count),
                        'other' => d_text($r->other),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'module' => 'setting.labor-item', 'moduleAbility' => 'LaborItem',
                            'showBtnShow' => false,
                            'disableEdit' => $r->trashed() || $r->status != '1',
                            'disableDelete' => $r->labor_details_count > 0 || $r->status != '1', 
                            'deleteCustomBtn' => ['DeleteLaborItem', route('setting.labor-item.delete', [$laborType->id, $r->id])],
                            'editCustomBtn' => ['UpdateLaborItem', route('setting.labor-item.edit', [$laborType->id, $r->id])],
                        ]),
                    ];
                })
                ->rawColumns(['dt.labor_details_count', 'dt.status', 'dt.action', 'dt.range'])
                ->make(true);
        } else {
            $data['laborType'] = $laborType;
            return view('labor_item.index', $data);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(LaborType $laborType)
    {
        $data['laborType'] = $laborType;
        $data['index'] = Str::after(LaborItem::getNextIndex(['type_id' => $laborType->id]), '.') + 1;
        return view('labor_item.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LaborType $laborType, LaborItemRequest $request)
    {
        if (LaborItem::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'min_range' => $request->min_range,
            'max_range' => $request->max_range,
            'unit' => $request->unit,
            'type_id' => $laborType->id,
            'index' => $request->index ?: 999,
            'other' => $request->other,
            'user_id' => auth()->user()->id,
        ])) {
            return redirect()->route('setting.labor-item.index', $laborType->id)->with('success', __('alert.message.success.crud.create'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaborType $laborType, LaborItem $laborItem)
    {
        $data['row'] = $laborItem;
        $data['laborType'] = $laborType;
        return view('labor_item.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LaborItemRequest $request, LaborType $laborType, LaborItem $laborItem)
    {
        if ($laborItem->update([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'min_range' => $request->min_range,
            'max_range' => $request->max_range,
            'unit' => $request->unit,
            'index' => $request->index ?: 999,
            'other' => $request->other,
        ])) {
            return redirect()->route('setting.labor-item.index', $laborType->id)->with('success', __('alert.message.success.crud.update'));
        }
    }

    public function sort_order(LaborType $laborType)
    {
        $data['rows'] = $laborType->items->where('status', 1);
        $data['url'] = route('setting.labor-item.update_order', $laborType->id);
        $data['back_url'] = route('setting.labor-item.index', $laborType->id);
        return view('shared.setting_service.order', $data);
    }

    public function update_order(LaborType $laborType, Request $request)
    {
        if (is_array($request->ids) && count($request->ids) > 0) {
            $rows = $laborType->items->where('status', 1)->whereIn('id', $request->ids);
            foreach ($request->ids as $index => $id) {
                $rows->where('id', $id)->first()->update(['index' => ++$index]);
            }
        }
        return redirect(route('setting.labor-item.index', $laborType->id))->with('success', __('alert.message.success.sort'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaborType $laborType, LaborItem $laborItem)
    {
        if ($laborItem->delete()) {
            return redirect()->route('setting.labor-item.index', $laborType->id)->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $laborItem = LaborItem::onlyTrashed()->findOrFail($id);
        if ($laborItem->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $laborItem = LaborItem::onlyTrashed()->findOrFail($id);
        if ($laborItem->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
