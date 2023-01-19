<?php

namespace App\Http\Controllers;

use App\Models\EchoType;
use Illuminate\Http\Request;
use App\Http\Requests\EchoTypeRequest;
use Yajra\DataTables\Facades\DataTables;

class EchoTypeController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  EchoType::with('user')
            ->withCount('echos');

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'name' => d_obj($r, ['name_kh', 'name_en']),
                        'price' => d_currency($r->price),
                        'index' => d_number($r->index),
                        'user' => d_obj($r, 'user', 'name'),
                        'age' => d_obj($r, 'age'),
                        'echos_count' => d_badge($r->echos_count),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'module' => 'setting.echo-type', 'moduleAbility' => 'EchoType', 
                            'disableEdit' => $r->trashed() || $r->status != '1',
                            'showBtnShow' => false, 
                            'disableDelete' => $r->echos_count > 0 || $r->status != '1',
                        ]),
                    ];
                })
                ->rawColumns(['dt.echos_count', 'dt.status', 'dt.action'])
                ->make(true);
        } else {
            return view('echo_type.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data['index'] = EchoType::getNextIndex();
        return view('echo_type.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EchoTypeRequest $request)
    {
        // serialize all post into string
        $serialize = array_except($request->all(), ['_method', '_token']);
        $request['attribite'] = serialize($serialize);

        $dataParent = new EchoType();
        if ($dataParent->create([
            'name_en' => $request->name_en,
            'name_kh' => $request->name_kh,
            'price' => $request->price ?: 0,
            'attribite' => $request->attribite,
            'index' => $request->index ?: 999,
            'default_form' => $request->default_form,
        ])) {
            return redirect()->route('setting.echo-type.index')->with('success', __('alert.message.success.crud.create'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EchoType $echoType)
    {
        append_array_to_obj($echoType, unserialize($echoType->attribite) ?: []);
        $data['row'] = $echoType;
        return view('echo_type.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EchoTypeRequest $request, EchoType $echoType)
    {
        // serialize all post into string
        $serialize = array_except($request->all(), ['_method', '_token']);
        $request['attribite'] = serialize($serialize);

        if ($echoType->update($request->all())) {
            return redirect()->route('setting.echo-type.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    public function sort_order()
    {
        $data['rows'] = EchoType::where('status', 1)->orderBy('index', 'asc')->get();
        $data['url'] = route('setting.echo-type.update_order');
        $data['back_url'] = route('setting.echo-type.index');
        return view('shared.setting_service.order', $data);
    }

    public function update_order(Request $request)
    {
        EchoType::saveOrder($request);
        return redirect(route('setting.echo-type.index'))->with('success', __('alert.message.success.sort'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EchoType $echoType)
    {
        if ($echoType->delete()) {
            return redirect()->route('setting.echo-type.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $echoType = EchoType::onlyTrashed()->findOrFail($id);
        if ($echoType->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $echoType = EchoType::onlyTrashed()->findOrFail($id);
        if ($echoType->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
