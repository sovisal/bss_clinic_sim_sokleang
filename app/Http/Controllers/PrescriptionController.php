<?php

namespace App\Http\Controllers;

use App\Models\DataParent;
use App\Models\Patient;
use App\Models\Inventory;
use App\Models\Medicine;
use App\Models\Prescription;
use App\Models\Inventory\Product;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class PrescriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  Prescription::with(['patient', 'gender', 'doctor', 'doctor_requested', 'address'])
                ->paraFilter();

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => d_link($r->code, "javascript:getDetail(" . $r->id . ", '" . route('prescription.getDetail', 'Priscription Detail') . "')"),
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
                            'moduleAbility' => 'Prescription', 'module' => 'prescription', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed() || !($r->status == '1' && $r->payment_status == 0), 'showBtnShow' => false,
                            'disableDelete' => !($r->status == '1' && $r->payment_status == 0),
                            'showBtnPrint' => true,
                        ]),
                    ];
                })
                ->rawColumns(['dt.code', 'dt.type', 'dt.patient', 'dt.gender', 'dt.doctor', 'dt.payment_status', 'dt.status', 'dt.action', 'dt.doctor_requested'])
                ->make(true);
        } else {
            return view('prescription.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = [
            'patient' => [],
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'usages' => getParentDataSelection('comsumption'),
            'time_usage' => DataParent::where('type', 'time_usage')->get(),
            'medicine' => [],
            'prescription_detail' => [],
            'is_edit' => false
        ];

        return view('prescription.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if ($pre = Prescription::create([
            'code' => generate_code('PRE', 'prescriptions'),
            'patient_id' => $request->patient_id ?: null,
            'age' => $request->age ?: null,
            'age_type' => 1, // Will link with data-patent to get age type and disply dropdown at form
            'doctor_id' => $request->doctor_id ?: null,
            'gender_id' => $request->gender_id ?: null,
            'requested_by' => $request->requested_by ?: Auth()->user()->doctor_id ?: null,
            'payment_type' => $request->payment_type ?: null,
            'payment_status' => 0,
            'requested_at' => $request->requested_at ?: date('Y-m-d H:i:s'),
            'analysis_at' => $request->analysis_at ?: null,
            'diagnosis' => $request->diagnosis,
            'price' => $request->price ?: 0,
            'exchange_rate' => d_exchange_rate(),
            'attribite' => $request->attribite,
        ])) {
            if ($request->is_treament_plan) {
                $patient = $pre->patient()->first();
                $pre->update(['address_id' => duplicate4LevelAddress($request, $patient->address()->first()), 'age' => $patient->age, 'gender_id' => $patient->gender_id]);
            } else {
                update4LevelAddress($request, $pre->patient()->first()->address_id);
                $pre->update(['address_id' => update4LevelAddress($request)]);
            }

            $this->refresh_prescriotion_detail($request, $pre);
            if ($request->is_treament_plan) {
                return redirect()->route('patient.consultation.edit', $request->consultation_id)->with('success', 'Data created success');
            } else {
                return redirect()->route('prescription.edit', $pre->id)->with('success', 'Data created success');
            }
        }
    }

    /**
     * Display the specified resource.
     */
    public function getDetail(Request $request)
    {
        $row = Prescription::where('prescriptions.id', $request->id)
            ->with([
                'patient',
                'detail' => function ($q) {
                    $q->with(['product', 'usage', 'unit']);
                }
            ])
            ->first();

        if ($row) {
            $tbody = '';
            foreach ($row->detail as $i => $detail) {
                $j = 0;
                $usage_time_str = '';
                $time_usage = getParentDataSelection('time_usage');
                foreach ($time_usage as $id => $data) {
                    if (in_array($id, explode(',', $detail->usage_times ?? []))) {
                        if ($j == 0) {
                            $usage_time_str = $data;
                            $j++;
                        } else {
                            $usage_time_str .= ' - ' . $data;
                        }
                    }
                }
                $tbody .= '<tr>
						<td class="text-center">' . str_pad(++$i, 2, '0', STR_PAD_LEFT) . '</td>
						<td>' . d_obj($detail, 'product', ['name_en', 'name_kh']) . '</td>
						<td class="text-center">' . d_number($detail->qty) . '</td>
						<td class="text-center">' . d_number($detail->upd) . '</td>
						<td class="text-center">' . d_number($detail->nod) . '</td>
						<th class="text-center"><strong>' . d_number($detail->total) . '</strong></th>
						<td>' . d_obj($detail, 'unit', ['name_en', 'name_kh']) . '</td>
						<td>' . d_text($usage_time_str) . '</td>
						<td>' . d_obj($detail, 'usage', ['title_en', 'title_kh']) . '</td>
						<td>' . d_text($detail->other) . '</td>
					</tr>';
            }
            $body = '<table class="table-form  tw-mt-3 table-detail-result">
					<tr class="text-center">
						<th class="text-center">N&deg;</th>
						<th>Medicine</th>
						<th width="50px">QTY</th>
						<th width="50px">U/D</th>
						<th width="50px">NoD</th>
						<th width="50px">Total</th>
						<th width="50px">Unit</th>
						<th width="160px">Usage Time</th>
						<th>Usage</th>
						<th>Note</th>
					</tr>
					' . (($tbody == '') ? '<tr colspan="10" class="text-center">No result</td>' : $tbody) . '
				</table>';

            return response()->json([
                'success' => true,
                'header' => getParaClinicHeaderDetail($row),
                'body' => $body,
                'print_url' => route('prescription.print', $row->id),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Prescription not found!',
            ], 404);
        }
    }

    public function print($id)
    {
        $prescription = Prescription::where('prescriptions.id', $id)
            ->with([
                'doctor', 'patient', 'gender',
                'detail' => function ($q) {
                    $q->with(['product', 'usage', 'unit']);
                }
            ])
            ->first();
        if ($prescription) {
            $data['row'] = $prescription;
            $data['time_usage'] = getParentDataSelection('time_usage');
            return view('prescription.print', $data);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prescription $prescription)
    {
        $data = [
            'row' =>  $prescription,
            'patient' => Patient::where('id', $prescription->patient_id)->get(),
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'usages' => getParentDataSelection('comsumption'),
            'time_usage' => DataParent::where('type', 'time_usage')->get(),
            'is_edit' => true
        ];

        $data['prescription_detail'] = $prescription->detail()->with(['product', 'product.packages', 'product.unit', 'product.packages.unit'])->get();
        $data['medicine'] = Inventory\Product::whereIn('id', $prescription->detail()->get('medicine_id'))->avaiableStock()->orderBy('name_en', 'asc')->get();

        return view('prescription.edit', $data);
    }

    public function show(Prescription $prescription)
    {
        if ($prescription ?? false) {
            $data['row'] = $prescription;
            $data['patient'] = Patient::orderBy('name_en', 'asc')->get();
            $data['doctor'] = Doctor::orderBy('id', 'asc')->get();
            $data['medicine'] = Medicine::orderBy('name', 'asc')->get();
            $data['usages'] = getParentDataSelection('comsumption');
            $data['time_usage'] = getParentDataSelection('time_usage');
        }
        $data['prescription_detail'] = $prescription->detail()->get();
        $data['is_edit'] = true;
        return view('prescription.show', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        $request['address_id'] = update4LevelAddress($request, $prescription->address_id);

        if ($prescription->update($request->except(['total', 'other', 'submit_option']))) {
            update4LevelAddress($request, $prescription->patient()->first()->address_id);
            $this->refresh_prescriotion_detail($request, $prescription);
            $validator = Validator::make([], []);

            // When button complete clicked
            if ($request->submit_option == '2') {

                // Start stock exist validatin
                foreach ($prescription->detail()->get() as $index => $detail) {
                    if ($product = $detail->product) {
                        $validated = $product->validateStockExist($detail->total, $detail->unit_id);
                        if ($validated['status'] != true) {
                            $validator->errors()->add($index, $validated['errMsg']);
                        }
                    }
                }

                // Return back with error message
                if ($validator->errors()->all()) {
                    return redirect()->route('prescription.index')->withErrors($validator);
                }

                // Stock calculation
                foreach ($prescription->detail()->get() as $detail) {
                    if ($product = $detail->product) {
                        $detail['type'] = 'Prescription';
                        $detail['parent_id'] = $detail->id;
                        $detail['document_no'] = $prescription->code;
                        $product->deductStock($detail->total, $detail->unit_id, $detail);
                    }
                }

                $prescription->update(['status' => '2', 'analysis_at' => now()]);
            }

            return redirect()->route('prescription.index')->with('success', __('alert.message.success.crud.update'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescription $prescription)
    {
        if ($prescription->delete()) {
            return redirect()->route('prescription.index')->with('success', 'Data delete success');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $row = Prescription::onlyTrashed()->findOrFail($id);
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
        $row = Prescription::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }

    public function refresh_prescriotion_detail($request, $prescription = null)
    {
        $prescription->detail()->where('status', 1)->delete();

        // Do update the labor detail
        $detail_ids = $request->test_id ?: [];
        $detail_values = [];
        $time_usage = getParentDataSelection('time_usage');

        foreach ($detail_ids as $index => $id) {
            if ($product = Product::where('id', $request->medicine_id[$index])->first()) {
                $detail_values[$index] = [
                    'medicine_id'     => $product->id,
                    'unit_id'         => $request->unit_id[$index] ?: '',
                    'price'           => $product->getAccuratePrice($request->unit_id[$index]),
                    'qty'             => $request->qty[$index] ?: 0,
                    'upd'             => $request->upd[$index] ?: 0,
                    'nod'             => $request->nod[$index] ?: 0,
                    'total'           => $request->total[$index] ?: 0,
                    'usage_id'        => $request->usage_id[$index] ?: 0,
                    'other'           => $request->other[$index] ?: '',
                ];

                $tmp_usage_time = [];
                foreach ($time_usage as $tm_id => $tm_name) {
                    if (isset($request->{'time_usage_' . $tm_id}[$index]) && $request->{'time_usage_' . $tm_id}[$index] != "OFF") {
                        $tmp_usage_time[] = $tm_id;
                    }
                }
                $detail_values[$index]['usage_times'] = implode(',', $tmp_usage_time ?: []);
            }
        }

        $prescription->detail()->createMany($detail_values);
        $prescription->updateQty();
    }
}
