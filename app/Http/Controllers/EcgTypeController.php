<?php

namespace App\Http\Controllers;

use App\Models\EcgType;
use Illuminate\Http\Request;
use App\Http\Requests\EcgTypeRequest;
use Yajra\DataTables\Facades\DataTables;

class EcgTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  EcgType::with('user')
            ->withCount('ecgs');

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'price' => d_currency($r->price),
                        'index' => d_number($r->index),
                        'user' => d_obj($r, 'user', 'name'),
                        'age' => d_obj($r, 'age'),
                        'ecgs_count' => d_badge($r->ecgs_count),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'module' => 'setting.ecg-type', 'moduleAbility' => 'EcgType', 
                            'disableEdit' => $r->trashed() || $r->status != '1',
                            'showBtnShow' => false, 
                            'disableDelete' => $r->ecgs_count > 0 || $r->status != '1',
                        ]),
                    ];
                })
                ->rawColumns(['dt.ecgs_count', 'dt.status', 'dt.action'])
                ->make(true);
        } else {
            return view('ecg_type.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['index'] = EcgType::getNextIndex();
        return view('ecg_type.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EcgTypeRequest $request)
    {
        // serialize all post into string
        $serialize = array_except($request->all(), ['_method', '_token']);
        $request['attribite'] = serialize($serialize);
        if (EcgType::create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'price' => $request->price ?: 0,
            'attribite' => $request->attribite,
            'index' => $request->index ?: 999,
            'default_form' => $request->default_form,
        ])) {
            return redirect()->route('setting.ecg-type.index')->with('success', __('alert.message.success.crud.create'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EcgType $ecgType)
    {
        append_array_to_obj($ecgType, unserialize($ecgType->attribite) ?: []);
        $data['row'] = $ecgType;
        return view('ecg_type.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EcgTypeRequest $request, EcgType $ecgType)
    {
        // serialize all post into string
        $serialize = array_except($request->all(), ['_method', '_token']);
        $request['attribite'] = serialize($serialize);

        if ($ecgType->update([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'price' => $request->price ?: 0,
            'attribite' => $request->attribite,
            'index' => $request->index ?: 999,
            'default_form' => $request->default_form,
        ])) {
            return redirect()->route('setting.ecg-type.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    public function sort_order()
    {
        $data['rows'] = EcgType::where('status', 1)->orderBy('index', 'asc')->get();
        $data['url'] = route('setting.ecg-type.update_order');
        $data['back_url'] = route('setting.ecg-type.index');
        return view('shared.setting_service.order', $data);
    }

    public function update_order(Request $request)
    {
        EcgType::saveOrder($request);
        return redirect(route('setting.ecg-type.index', request()->only(['parent'])))->with('success', __('alert.message.success.sort'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EcgType $ecgType)
    {
        if ($ecgType->delete()) {
            return redirect()->route('setting.ecg-type.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $ecgType = EcgType::onlyTrashed()->findOrFail($id);
        if ($ecgType->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $ecgType = EcgType::onlyTrashed()->findOrFail($id);
        if ($ecgType->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
