<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;
use App\Http\Requests\DoctorRequest;
use Yajra\DataTables\Facades\DataTables;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Doctor::with(['user', 'address'])
                ->withCount('ecgs')
                ->withCount('xrays')
                ->withCount('labors')
                ->withCount('echos')
                ->withCount('prescriptions')
                ->withCount('invoices');

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => 'DT-' . str_pad($r->id, 6, '0', STR_PAD_LEFT),
                        'name' => d_obj($r, ['name_en', 'name_kh']),
                        'gender' => d_obj($r, 'gender', ['title_kh', 'title_kh']),
                        'phone' => d_text($r->phone),
                        'email' => d_text($r->email),
                        'address' => d_obj($r, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh']),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'module' => 'setting.doctor', 'moduleAbility' => 'Doctor', 
                            'disableEdit' => $r->trashed() || $r->status != '1',
                            'showBtnShow' => false, 
                            'disableDelete' => $r->ecgs_count > 0 || $r->xrays_count > 0 || $r->labors_count > 0 || $r->echos_count > 0 || $r->prescriptions_count > 0 || $r->invoices_count > 0 || $r->status != '1',
                        ]),
                    ];
                })
                ->rawColumns(['dt.ecgs_count', 'dt.status', 'dt.action'])
                ->make(true);
        } else {
            return view('doctor.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'genders' => getParentDataSelection('gender'),
        ];
        return view('doctor.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DoctorRequest $request)
    {
        $doctor = Doctor::create([
            'name_kh' => $request->name_kh,
            'name_en' => $request->name_en,
            'id_card_no' => $request->id_card_no,
            'gender_id' => $request->gender_id,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);
        $doctor->update(['address_id' => update4LevelAddress($request)]);

        $url = route('setting.doctor.index');
        if ($request->save_opt == 'save_create') {
            $url = route('setting.doctor.create');
        } else if ($request->save_opt == 'save_edit') {
            $url = route('setting.doctor.edit', $doctor->id);
        }
        return redirect($url)->with('success', __('alert.message.success.crud.create'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $data = [
            'doctor' => $doctor,
            'genders' => getParentDataSelection('gender'),
        ];
        return view('doctor.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DoctorRequest $request, Doctor $doctor)
    {
        $doctor->update([
            'name_kh' => $request->name_kh,
            'name_en' => $request->name_en,
            'id_card_no' => $request->id_card_no,
            'gender_id' => $request->gender_id,
            'email' => $request->email,
            'phone' => $request->phone,
            'address_id' => update4LevelAddress($request, $doctor->address_id),
        ]);

        return back()->with('success', __('alert.message.success.crud.update'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        if ($doctor->delete()) {
            return back()->with('success', __('alert.message.success.crud.delete'));
        }
        return back()->with('error', __('alert.message.error.crud.delete'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $row = Doctor::onlyTrashed()->findOrFail($id);
        if ($row->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $row = Doctor::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }

    // get Product Select2
    public function getSelect2()
    {
        return Doctor::getSelect2([], ['id', 'asc'], ['id', 'name_kh']);
    }
}
