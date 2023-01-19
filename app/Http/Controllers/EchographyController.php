<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\EchoType;
use App\Models\Echography;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use App\Http\Requests\EchographyRequest;
use Yajra\DataTables\Facades\DataTables;

class EchographyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  Echography::with(['address', 'doctor', 'doctor_requested', 'patient', 'type', 'address', 'gender'])
                ->paraFilter();

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => d_link($r->code, "javascript:getDetail(" . $r->id . ", '" . route('para_clinic.echography.getDetail', 'Echography Detail') . "')"),
                        'type' => $r->typeLink,
                        'patient' => d_obj($r, 'patient', 'link'),
                        'gender' => d_obj($r, 'gender', ['title_en', 'title_kh']),
                        'age' => d_obj($r, 'age'),
                        'address' => d_obj($r, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh']),
                        'requested_at' => d_date($r->requested_at),
                        'doctor_requested' => d_obj($r, 'doctor_requested', 'link'),
                        'analysis_at' => d_date($r->analysis_at),
                        'doctor' => d_obj($r, 'doctor', 'link'),
                        'price' => d_currency($r->price),
                        'payment_status' => d_paid_status($r->payment_status),
                        // 'user' => d_obj($r, 'user', 'name'),
                        'status' => d_para_status($r->status),
                        'action' => d_action([
                            'moduleAbility' => 'Echography', 'module' => 'para_clinic.echography', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed() || !($r->status == '1' && $r->payment_status == 0), 'showBtnShow' => false,
                            'disableDelete' => !($r->status == '1' && $r->payment_status == 0),
                            'paraImage' => [$r->image_1, $r->image_2], 'showBtnPrint' => true,
                        ]),
                    ];
                })
                ->rawColumns(['dt.code', 'dt.type', 'dt.patient', 'dt.gender', 'dt.doctor', 'dt.payment_status', 'dt.status', 'dt.action', 'dt.doctor_requested'])
                ->make(true);
        } else {
            return view('echography.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'type' => EchoType::where('status', 1)->orderBy('index', 'asc')->get(),
            'patient' => [],
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'is_edit' => false
        ];

        return view('echography.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EchographyRequest $request)
    {
        $echo_type = $request->type_id ? EchoType::where('id', $request->type_id)->first() : null;
        if ($echo = Echography::create([
            'code' => generate_code('ECH', 'echographies'),
            'type_id' => $request->type_id ?: null,
            'patient_id' => $request->patient_id ?: null,
            'age' => $request->age ?: null,
            'age_type' => 1, // Will link with data-patent to get age type and disply dropdown at form
            'doctor_id' => $request->doctor_id ?: null,
            'gender_id' => $request->gender_id ?: null,
            'requested_by' => $request->requested_by ?: Auth()->user()->doctor_id ?: null,
            'payment_type' => $request->payment_type ?: null,
            'payment_status' => 0,
            'requested_at' => $request->requested_at,
            'image_1' => $request->image_1,
            'image_2' => $request->image_2,
            'price' => $request->price ?: ($echo_type ? $echo_type->price : 0),
            'exchange_rate' => d_exchange_rate(),
            'attribute' => $echo_type ? $echo_type->attribite : null,
        ])) {
            if ($request->is_treament_plan) {
                $patient = $echo->patient()->first();
                $echo->update(['address_id' => duplicate4LevelAddress($request, $patient->address()->first()), 'age' => $patient->age, 'gender_id' => $patient->gender_id]);
            } else {
                update4LevelAddress($request, $echo->patient()->first()->address_id);
                $echo->update(['address_id' => update4LevelAddress($request)]);
            }

            // Check if no exist folder/directory then create folder/directory
            $path = public_path('/images/echographies/');
            File::makeDirectory($path, 0777, true, true);
            $image_1 =  create_image($request->img_1, $path, (time() . '_image_1_' . rand(111, 999) . '.png'));
            $image_2 =  create_image($request->img_2, $path, (time() . '_image_2_' . rand(111, 999) . '.png'));
            $echo->update(['image_1' => $image_1, 'image_2' => $image_2]);

            if ($request->is_treament_plan) {
                return redirect()->route('patient.consultation.edit', $request->consultation_id)->with('success', __('alert.message.success.crud.create'));
            } else {
                return redirect()->route('para_clinic.echography.edit', $echo->id)->with('success', __('alert.message.success.crud.create'));
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function getDetail(Request $request)
    {
        $row = Echography::where('echographies.id', $request->id)->with(['patient', 'doctor', 'type', 'doctor_requested', 'payment'])->first();
        if ($row) {
            $body = '';
            $tbody = '';
            $attributes = $row->filterAttr;
            foreach ($attributes as $label => $attr) {
                $tbody .= '<tr>
                                <td width="30%" class="text-right tw-bg-gray-100">' . __('form.echography.' . $label) . '</td>
                                <td>' . $attr . '</td>
                            </tr>';
            }
            $body = '<table class="table-form tw-mt-3 table-detail-result">
                        <thead>
                            <tr>
                                <th colspan="4" class="text-left tw-bg-gray-100">Result</th>
                            </tr>
                        </thead>
                        <tbody>' . ((empty($attributes)) ? '<tr><th colspan="4" class="text-center">No result</th></tr>' : $tbody) . '</tbody>
                    </table>';
            return response()->json([
                'success' => true,
                'header' => getParaClinicHeaderDetail($row),
                'body' => $body,
                'print_url' => route('para_clinic.echography.print', $row->id),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Echography not found!',
            ], 404);
        }
    }

    /**
     * Print the specified Resourse.
     */
    public function print($id)
    {
        $echography = Echography::with(['patient', 'gender', 'doctor', 'type'])->find($id);
        $data['echography'] = $echography;
        return view('echography.print', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Echography $echography)
    {
        append_array_to_obj($echography, unserialize($echography->attribute) ?: []);

        $data = [
            'row' => $echography,
            'type' => EchoType::where('status', 1)->orderBy('index', 'asc')->get(),
            'patient' => Patient::where('id', $echography->patient_id)->get(),
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'is_edit' => true,
        ];
        return view('echography.edit', $data);
    }

    // public function show(Echography $echography)
    // {
    //     append_array_to_obj($echography, unserialize($echography->attribute) ?: []);
    //     if ($echography ?? false) {
    //         $data['row'] = $echography;
    //         $data['type'] = EchoType::where('status', 1)->orderBy('index', 'asc')->get();
    //         $data['patient'] = Patient::orderBy('name_en', 'asc')->get();
    //         $data['doctor'] = Doctor::orderBy('id', 'asc')->get();
    //     }
    //     $data['payment_type'] = getParentDataSelection('payment_type');
    //     $data['is_edit'] = true;
    //     return view('echography.show', $data);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(EchographyRequest $request, Echography $echography)
    {
        // serialize all post into string
        $serialize = $request->except(['_method', '_token', 'img_1', 'img_2', 'file-browse-img_1', 'file-browse-img_2']);
        $request['attribute'] = serialize($serialize);

        $echo_type = $request->type_id ? EchoType::where('id', $request->type_id)->first() : null;

        $request['price'] = $request->price ?: ($echo_type ? $echo_type->price : 0);
        $request['address_id'] = update4LevelAddress($request, $echography->address_id);

        // Check if no exist folder/directory then create folder/directory
        $path = public_path('/images/echographies/');
        File::makeDirectory($path, 0777, true, true);
        $request['image_1'] = update_image($request->img_1, $path, (time() . '_image_1_' . rand(111, 999) . '.png'), $echography->image_1);
        $request['image_2'] = update_image($request->img_2, $path, (time() . '_image_2_' . rand(111, 999) . '.png'), $echography->image_2);

        if ($request->status == '2') {
            $request['analysis_at'] = now();
        }
        if ($echography->update($request->all())) {
            update4LevelAddress($request, $echography->patient()->first()->address_id);
            return redirect()->route('para_clinic.echography.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Echography $echography)
    {
        if ($echography->delete()) {
            return redirect()->route('para_clinic.echography.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $echography = Echography::onlyTrashed()->findOrFail($id);
        if ($echography->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $echography = Echography::onlyTrashed()->findOrFail($id);
        $image_1 = $echography->image_1;
        $image_2 = $echography->image_2;
        if ($echography->forceDelete()) {
            $path = public_path('/images/echographies/');
            remove_file($image_1, $path);
            remove_file($image_2, $path);
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
