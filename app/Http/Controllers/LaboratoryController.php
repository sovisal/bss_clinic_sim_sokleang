<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\LaborType;
use App\Models\Laboratory;
use App\Models\LaborDetail;
use Illuminate\Http\Request;
use App\Http\Requests\LaborRequest;
use Yajra\DataTables\Facades\DataTables;

class LaboratoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  Laboratory::with(['address', 'doctor_requested', 'doctor', 'patient', 'address', 'gender'])
                ->paraFilter();

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => d_link($r->code, "javascript:getDetail(" . $r->id . ", '" . route('para_clinic.labor.getDetail', 'ECG Detail') . "')"),
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
                            'moduleAbility' => 'Laboratory', 'module' => 'para_clinic.labor', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed() || !($r->status == '1' && $r->payment_status == 0), 'showBtnShow' => false,
                            'disableDelete' => !($r->status == '1' && $r->payment_status == 0),
                            'showBtnPrint' => true,
                        ]),
                    ];
                })
                ->rawColumns(['dt.code', 'dt.type', 'dt.patient', 'dt.gender', 'dt.doctor', 'dt.payment_status', 'dt.status', 'dt.action', 'dt.doctor_requested'])
                ->make(true);
        } else {
            return view('labor.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'type' => LaborType::where('status', 1)->orderBy('index', 'asc')->regroupe(),
            'patient' => [],
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'addresses' => get4LevelAdressSelector('xx', 'option'),
            'is_edit' => false
        ];
        return view('labor.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LaborRequest $request)
    {
        if (sizeof($request->labor_item_id ?? []) > 0) {
            if ($labor = Laboratory::create([
                'code' => generate_code('LAB', 'laboratories'),
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
                'price' => $request->price ?: 0,
                'exchange_rate' => d_exchange_rate(),
                'attribute' => null,
                'analysis_at' => $request->analysis_at,
                'result' => $request->result,
                'sample' => $request->sample,
                'diagnosis' => $request->diagnosis,
            ])) {
                if ($request->is_treament_plan) {
                    $patient = $labor->patient()->first();
                    $labor->update(['address_id' => duplicate4LevelAddress($request, $patient->address()->first()), 'age' => $patient->age, 'gender_id' => $patient->gender_id]);
                } else {
                    update4LevelAddress($request, $labor->patient()->first()->address_id);
                    $labor->update(['address_id' => update4LevelAddress($request)]);
                }

                foreach ($request->labor_item_id as $labor_item_id) {
                    $detail = new LaborDetail;
                    $detail->create([
                        'labor_id' => $labor->id,
                        'labor_item_id' => $labor_item_id,
                        'value' => 0,
                    ]);
                }
                if ($request->is_treament_plan) {
                    return redirect()->route('patient.consultation.edit', $request->consultation_id)->with('success', __('alert.message.success.crud.create'));
                } else {
                    return redirect()->route('para_clinic.labor.edit', $labor->id)->with('success', __('alert.message.success.crud.create'));
                }
            }
        } else {
            if ($request->is_treament_plan) {
                return redirect()->route('patient.consultation.edit', $request->consultation_id)->with('error', 'No Test has been selected');
            } else {
                return redirect()->route('para_clinic.labor.create')->with('error', 'No Test has been selected');
            }
        }
    }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Laboratory $labor)
    // {
    //     if ($labor ?? false) {
    //         $data['row'] = $labor;
    //         $data['patient'] = Patient::orderBy('name_en', 'asc')->get();
    //         $data['doctor'] = Doctor::orderBy('id', 'asc')->get();
    //     }
    //     $data['gender'] = getParentDataSelection('gender');
    //     $data['payment_type'] = getParentDataSelection('payment_type');
    //     $data['labor_detail'] = $labor->details;
    //     $data['is_edit'] = true;
    //     return view('labor.show', $data);
    // }

    /**
     * Display the specified resource.
     */
    public function getDetail(Request $request)
    {
        $row = Laboratory::where('id', $request->id)->with(['patient', 'doctor', 'doctor_requested', 'payment'])->first();
        if ($row) {
            $tbody = '';
            $labor_detail = $row->details;
            foreach ($labor_detail as $detail) {
                static $i = 1;
                $item = $detail->item;
                $tbody .= '<tr>
                                <td class="text-center">' . $i++ . '</td>
                                <td>' . render_synonyms_name($item->type->name_en, $item->type->name_kh) . '</td>
                                <td>' . render_synonyms_name($item->name_en, $item->name_kh) . '</td>
                                <th class="text-center"><strong>' . ($detail->value ?: 0) . '</strong></th>
                                <td class="text-center">' . apply_markdown_character($item->unit) . '</td>
                                <td class="text-center">' . $item->min_range . ' - ' . $item->max_range . '</td>
                            </tr>';
            }
            $body = '<table class="table-form table table-border mt-1">
                        <thead>
                            <tr>
                                <th colspan="6" class="tw-bg-gray-100 text-left">Detail</th>
                            </tr>
                            <tr>
                                <th class="text-center">N&deg;</th>
                                <th>Category</th>
                                <th>Tests</th>
                                <th width="15%">Result</th>
                                <th width="15%">Unit</th>
                                <th width="15%">Normal Range</th>
                            </tr>
                        </thead>
                        <tbody>' . (($tbody == '') ? '<tr><th colspan="7" class="text-center">No result</th></tr>' : $tbody . '<tr></tr>') . '</tbody>
                    </table>';
            return response()->json([
                'success' => true,
                'header' => getParaClinicHeaderDetail($row),
                'body' => $body,
                'print_url' => route('para_clinic.labor.print', $row->id),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Laboratory not found!',
            ], 404);
        }
    }

    /**
     * Display the specified Image.
     */
    public function print($id)
    {
        $labor = Laboratory::select([
            'laboratories.*',
            'patients.name_kh as patient_kh',
            'patients.age as patient_age',
            'data_parents.title_en as patient_gender',
            'doctors.name_kh as doctor_kh',
            'requestedBy.name_en as requested_by_name',
        ])
            ->with(['details.item'])
            ->leftJoin('patients', 'patients.id', '=', 'laboratories.patient_id')
            ->leftJoin('data_parents', 'data_parents.id', '=', 'patients.gender_id')
            ->leftJoin('doctors', 'doctors.id', '=', 'laboratories.doctor_id')
            ->leftJoin('doctors AS requestedBy', 'requestedBy.id', '=', 'laboratories.requested_by')
            ->find($id);
        $data['labor'] = $labor;

        // Prepare labor detail with 2 levels of groups
        //#1, get all labor detail concerned, and separate it by type id
        $labor_detail = [];
        foreach ($labor->details ?? [] as $item) {
            $type_id = $item->item->type_id;
            $labor_detail[$type_id][] = $item;
        }

        // #2, Get all template and apply Labor detail for it
        $print_result = [];
        $labor_types = LaborType::where('status', 1)->orderBy('index', 'asc')->regroupe() ?: [];
        foreach ($labor_types as $main_data) {
            $child_has_labor = [];
            foreach ($main_data->child as $sub_data) {
                if (in_array($sub_data->id, array_keys($labor_detail))) {
                    $child_has_labor[] = [
                        'type' => 'label',
                        'data' => trim($sub_data->name_en, '- ')
                    ];
                    $child_has_labor[] = [
                        'type' => 'result',
                        'data' => $labor_detail[$sub_data->id]
                    ];
                }
            }

            if (sizeof($child_has_labor) > 0) { // Check main type
                $print_result[] = [
                    'type' => 'main_label',
                    'data' => $main_data->name_en
                ];
                array_push($print_result, ...$child_has_labor);
            } elseif (in_array($main_data->id, array_keys($labor_detail))) {
                $print_result[] = [
                    'type' => 'main_label',
                    'data' => $main_data->name_en
                ];
                $print_result[] = [
                    'type' => 'result',
                    'data' => $labor_detail[$main_data->id]
                ];
            }
        }

        //#3, See the debug to get understand
        $data['labor_detail'] = $print_result;
        return view('labor.print', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Laboratory $labor)
    {
        $data = [
            'row' => $labor,
            'labor_detail' => $labor->details,
            'patient' => Patient::where('id', $labor->patient_id)->get(),
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'addresses' => get4LevelAdressSelector('xx', 'option'),
            'is_edit' => true,
        ];

        return view('labor.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LaborRequest $request, Laboratory $labor)
    {
        $request['address_id'] = update4LevelAddress($request, $labor->address_id);

        if ($request->status == '2') {
            $request['analysis_at'] = now();
        }
        if ($labor->update($request->all())) {
            update4LevelAddress($request, $labor->patient()->first()->address_id);

            // Do update the labor detail
            $detail_ids = $request->test_id ?: [];
            $detail_values = $request->test_value ?: [];
            if (sizeof($detail_ids) > 0) {
                foreach ($detail_ids as $index => $id) {
                    LaborDetail::find($id)->update(['value' => $detail_values[$index] ?: 0]);
                }
                // Clean old data
                $detailToDelete = LaborDetail::where('labor_id', $labor->id)->whereNotIn('id', $detail_ids);
                $detailToDelete->delete();
            }

            return redirect()->route('para_clinic.labor.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Laboratory $labor)
    {
        if ($labor->delete()) {
            return redirect()->route('para_clinic.labor.index')->with('success', __('alert.message.success.crud.delete'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $laboratory = Laboratory::onlyTrashed()->findOrFail($id);
        if ($laboratory->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function force_delete($id)
    {
        $laboratory = Laboratory::onlyTrashed()->findOrFail($id);
        if ($laboratory->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }
}
