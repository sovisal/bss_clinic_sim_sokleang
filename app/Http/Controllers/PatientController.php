<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\PatientRequest;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Collection;
use Yajra\DataTables\Facades\DataTables;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            if ($request->_type == 'query') {
                // For select2 Ajx
                $term = Patient::where('name_en', 'LIKE', '%' . $request->term . '%')
                    ->orWhere('name_kh', 'LIKE', '%' . $request->term . '%')
                    ->filter()
                    ->limit(100)->get(['id', 'name_kh', 'name_en']);

                $result = [];
                foreach ($term as $t) {
                    $result[] = [
                        'id' => $t->id,
                        'text' => d_obj($t, ['name_kh', 'name_en']),
                    ];
                }
                return ['results' => $result];
            }

            $data = Patient::with(['address', 'user', 'gender', 'hasOneConsultation'])
                ->withCount('ecgs')
                ->withCount('echos')
                ->withCount('xrays')
                ->withCount('labors')
                ->withCount('prescriptions')
                ->withCount('invoices')
                ->filter();

            return DataTables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => $r->hasOneConsultation ? d_link('PT-' . str_pad($r->id, 6, '0', STR_PAD_LEFT), route('patient.consultation.edit', $r->hasOneConsultation->id)) : 'PT-' . str_pad($r->id, 6, '0', STR_PAD_LEFT),
                        'patient' => d_obj($r, ['name_en', 'name_kh']),
                        'gender' => d_obj($r, 'gender', ['title_en', 'title_kh']),
                        'age' => d_number($r->age),
                        'phone' => d_text($r->phone),
                        'address' => d_obj($r, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh']),
                        'registered_at' => d_date_time($r->registered_at),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_status($r->status),
                        'action' => d_action([
                            'module' => 'patient', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableShow' => $r->trashed(), 
                            'disableEdit' => $r->trashed(), 
                            'disableDelete' => $r->hasOneConsultation || $r->ecgs_count > 0 || $r->echos_count > 0 || $r->xrays_count > 0 || $r->labors_count > 0 || $r->prescriptions_count > 0 || $r->invoices_count > 0
                        ]),
                    ];
                })
                ->rawColumns(['dt.status', 'dt.code', 'dt.action'])
                ->make(true);
        } else {
            $data = getParentDataSelection([
                'gender',
                'nationality',
                'marital_status',
                'enterprise'
            ]);
            if (request()->ft_address_id) {
                $data['address_options'] = get4LevelAdressSelectorByID(request()->ft_address_id, ...['xx', 'option'])[0] ?? [];
            } else {
                $data['address_options'] = get4LevelAdressSelector('xx', 'option')[0] ?? [];
            }
            return view('patient.index', $data);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $data = getParentDataSelection([
            'blood_type',
            'nationality',
            'gender',
            'marital_status',
            'enterprise'
        ]);
        return view('patient.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        if ($request->ajax()) {
            $request->merge([
                'name_en' => $request->name,
                'name_kh' => $request->name,
            ]);
        }
        $address_id = update4LevelAddress($request);
        $patient = Patient::create($this->compileRequestColumns($request, $address_id));
        if ($patient) {
            $saved_consultation = Consultation::create([
                'patient_id' => $patient->id,
                'doctor_id' => 1,
                'payment_type' => null,
                'evaluated_at' => now(),
                'attribute' => '',
            ]);
        }

        if ($request->ajax()) {
            return $patient;
        }

        if ($request->file('photo')) {
            $path = public_path('/images/patients/');
            File::makeDirectory($path, 0777, true, true);
            $photo = $request->file('photo');
            $photo_name = time() . '_' . $patient->id . '.png';
            Image::make($photo->getRealPath())->save($path . $photo_name);
            $patient->update(['photo' => $photo_name]);
        }
        if (can('ViewAnyConsultation')) {
            return redirect()->route('patient.consultation.edit', $saved_consultation->id)->with('success', __('alert.message.success.crud.create'));
        }
        return redirect()->route('patient.index')->with('success', __('alert.message.success.crud.create'));

    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'address',
            'prescriptions' => function ($q) { $q->with(['user', 'doctor', 'doctor_requested']); },
            'labors' => function ($q) { $q->with(['user', 'doctor', 'doctor_requested']); },
            'xrays' => function ($q) { $q->with(['user', 'doctor', 'doctor_requested', 'type']); },
            'echos' => function ($q) { $q->with(['user', 'doctor', 'doctor_requested', 'type']); },
            'ecgs' => function ($q) { $q->with(['user', 'doctor', 'doctor_requested', 'type']); },
        ]);
        
        $history = new Collection;
        $history = $history->concat($patient->prescriptions->map(function ($row) {
            $row->row_type = 'prescription';
            $row->url = route('prescription.print', $row->id);
            return $row;
        }));
        $history = $history->concat($patient->labors->map(function ($row) {
            $row->row_type = 'labor';
            $row->url = route('para_clinic.labor.print', $row->id);
            return $row;
        }));
        $history = $history->concat($patient->xrays->map(function ($row) {
            $row->row_type = 'xray';
            $row->url = route('para_clinic.xray.print', $row->id);
            return $row;
        }));
        $history = $history->concat($patient->echos->map(function ($row) {
            $row->row_type = 'echo';
            $row->url = route('para_clinic.echography.print', $row->id);
            return $row;
        }));
        $history = $history->concat($patient->ecgs->map(function ($row) {
            $row->row_type = 'ecg';
            $row->route_name = 'para_clinic.ecg';
            return $row;
        }));
        $patient->history = $history->sortByDesc('requested_at');

        $data = [
            'patient' => $patient,
        ];
        return view('patient.show', $data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $data = getParentDataSelection([
            'blood_type',
            'nationality',
            'gender',
            'marital_status',
            'enterprise'
        ]);
        $data['patient'] = $patient;

        return view('patient.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request, Patient $patient)
    {
        $address_id = update4LevelAddress($request, $patient->address_id);
        $patient->update($this->compileRequestColumns($request, $address_id));

        if ($request->file('photo')) {
            $path = public_path('/images/patients/');
            File::makeDirectory($path, 0777, true, true);
            $photo = $request->file('photo');
            $patient_photo = (($patient->photo != '') ? $patient->photo : time() . '_' . $patient->id . '.png');
            Image::make($photo->getRealPath())->save($path . $patient_photo);
            $patient->update(['photo' => $patient_photo]);
        }

        return redirect()->back()->with('success', __('alert.message.success.crud.update'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $address_id = $patient->address_id;
        $patient_photo = $patient->photo;
        if ($patient->delete()) {
            $old_path = public_path('/images/patients/' . $patient_photo);
            if (File::exists($old_path)) {
                File::delete($old_path);
            }

            // if ($address_id && $address_id > 0) delete4LevelAddress($address_id);
            return back()->with('success', __('alert.message.success.crud.delete'));
        }
        return back()->with('error', __('alert.message.error.crud.delete'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $user = Patient::onlyTrashed()->findOrFail($id);
        if ($user->restore()) {
            return back()->with('success', __('alert.message.success.crud.restore'));
        }
        return back()->with('error', __('alert.message.error.crud.restore'));
    }

    // get Product Select2
    public function getSelect2()
    {
        return Patient::getSelect2(['status' => 'active'], ['name_kh', 'asc'], ['id', 'name_kh']);
    }

    public function getSelectDetail(Request $request)
    {
        $patient = Patient::find($request->id);

        $return_result['patient'] = $patient;
        if ($patient->address_id) {
            $return_result['address'] = get4LevelAdressSelectorByID($patient->address_id, ...['xx', 'option']);
        }
        return response()->json($return_result);
    }

    public function compileRequestColumns($request, $address_id)
    {
        return [
            'name_kh' => $request->name_kh,
            'name_en' => $request->name_en,
            'id_card_no' => $request->id_card_no,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'age' => $request->age,
            'education' => $request->education,
            'position' => $request->position,
            'father_name' => $request->father_name,
            'father_position' => $request->father_position,
            'mother_name' => $request->mother_name,
            'mother_position' => $request->mother_position,
            'house_no' => $request->house_no,
            'street_no' => $request->street_no,
            'postal_code' => $request->postal_code,
            'address_id' => $address_id,
            'registered_at' => $request->registered_at,
            'gender_id' => $request->gender_id,
            'marital_status_id' => $request->marital_status_id,
            'nationality_id' => $request->nationality_id,
            'enterprise_id' => $request->enterprise_id,
            'blood_type_id' => $request->blood_type_id,
        ];
    }
}
