<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Echography;
use App\Models\Laboratory;
use App\Models\Prescription;
use App\Models\Xray;
use App\Models\Ecg;
use App\Models\Inventory\Product;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data =  Invoice::with(['doctor', 'patient', 'gender', 'address', 'user'])
                ->paraFilter();

            return Datatables::of($data)
                ->addColumn('dt', function ($r) {
                    return [
                        'code' => d_link($r->code, "javascript:getDetail(" . $r->id . ", '" . route('invoice.getDetail', 'Invoice Detail') . "')"),
                        'patient' => d_obj($r, 'patient', 'link'),
                        'gender' => d_obj($r, 'gender', ['title_en', 'title_kh']),
                        'age' => d_obj($r, 'age'),
                        'address' => d_obj($r, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh']),
                        'requested_at' => d_date($r->requested_at),
                        'doctor' => d_obj($r, 'doctor', 'link'),
                        'exchange_rate' => d_number($r->exchange_rate),
                        'total' => d_currency($r->total, 2, '$'),
                        'payment_status' => d_paid_status($r->payment_status),
                        'user' => d_obj($r, 'user', 'name'),
                        'status' => d_para_status($r->status),
                        'action' => d_action([
                            'moduleAbility' => 'Invoice', 'module' => 'invoice', 'id' => $r->id, 'isTrashed' => $r->trashed(),
                            'disableEdit' => $r->trashed() || !($r->status == '1' && $r->payment_status == 0), 'showBtnShow' => false,
                            'disableDelete' => !($r->status == '1' && $r->payment_status == 0),
                            'showBtnPrint' => true,
                        ]),
                    ];
                })
                ->rawColumns(['dt.code', 'dt.type', 'dt.patient', 'dt.gender', 'dt.doctor', 'dt.payment_status', 'dt.status', 'dt.action'])
                ->make(true);
        } else {
            return view('invoice.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'code' => generate_code('INV', 'invoices', false),
            'patient' => [],
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'is_edit' => false
        ];

        return view('invoice.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInvoiceRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($inv = Invoice::create([
            'code' => generate_code('INV', 'invoices'),
            'inv_date' => $request->inv_date ?: date('Y-m-d H:i:s'),
            'requested_at' => $request->inv_date ?: date('Y-m-d H:i:s'),
            'patient_id' => $request->patient_id ?: null,
            'age' => $request->age ?: '0',
            'age_type' => 1, // Will link with data-patent to get age type and disply dropdown at form
            'gender_id' => $request->gender_id ?: null,
            'doctor_id' => $request->doctor_id ?: Auth()->user()->doctor_id ?: null,
            'remark' => $request->remark ?: '',
            'payment_type' => $request->payment_type ?: null,
            'exchange_rate' => d_exchange_rate(),
            'total' => array_sum($request->total ?: []),
        ])) {
            update4LevelAddress($request, $inv->patient()->first()->address_id);
            $inv->update(['address_id' => update4LevelAddress($request)]);
            return redirect()->route('invoice.edit', $inv->id)->with('success', 'Data created success');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        //
    }

    public function print($id)
    {
        $invoice = Invoice::with(['patient', 'gender', 'doctor', 'detail'])
            ->where('invoices.id', $id)
            ->with('detail')
            ->first();
        if ($invoice) {
            $data['row'] = $invoice;
            return view('invoice.print', $data);
        } else {
            abort(404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        $data = [
            'row' => $invoice,
            'code' => generate_code('INV', 'invoices', false),
            'inv_number' => "PT-" . str_pad($invoice->id, 4, '0', STR_PAD_LEFT),
            'patient' => Patient::where('id', $invoice->patient_id)->get(),
            'doctor' => Doctor::orderBy('id', 'asc')->get(),
            'payment_type' => getParentDataSelection('payment_type'),
            'gender' => getParentDataSelection('gender'),
            'invoice_detail_service' => $invoice->detail()->where('service_type', 'service')->get(),
            'invoice_detail_medicine' => $invoice->detail()->where('service_type', 'medicine')->get(),
            'is_edit' => true
        ];

        // Invoice item selection
        $selection = [
            'medicine' => Product::avaiableStock()->whereIn('id', $invoice->detail()->where('service_type', 'medicine')->get('service_id'))->get(),
            'service' => Service::where('status', '>=', '1')->orderBy('name', 'asc')->get(),
            'prescription' => Prescription::where('patient_id', $invoice->patient_id)->where('payment_status', 0)->where('status', 2)->get(),
            'echography' => Echography::with(['type'])->where('patient_id', $invoice->patient_id)->where('payment_status', 0)->where('status', 2)->get(),
            'laboratory' => Laboratory::where('patient_id', $invoice->patient_id)->where('payment_status', 0)->where('status', 2)->get(),
            'xray' => Xray::with(['type'])->where('patient_id', $invoice->patient_id)->where('payment_status', 0)->where('status', 2)->get(),
            'ecg' => Ecg::with(['type'])->where('patient_id', $invoice->patient_id)->where('payment_status', 0)->where('status', 2)->get(),
        ];

        // Invoice item selected
        $count_check = $invoice->detail()->count();
        $data['invoice_selection'] = [];
        foreach ($selection as $type => $items) {
            $items_selected = $invoice->detail()->where('service_type', $type)->get()->keyBy('service_id');
            $item_selection = [];
            foreach ($items as $item) {
                if ($items_selected && isset($items_selected[$item['id']])) {
                    $item['chk'] = 1;
                    $item['qty'] = $items_selected[$item['id']]->qty;
                    $item['price'] = $items_selected[$item['id']]->price;
                    $item['total'] = $items_selected[$item['id']]->total;
                    $item['description'] = $items_selected[$item['id']]->description;
                }

                if ($count_check == 0) {
                    $item['chk'] = 1;
                }

                $item_selection[] = $item;
            }
            $data['invoice_selection'][$type] = $item_selection;
        }

        return view('invoice.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInvoiceRequest  $request
     * @param  \App\Models\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Invoice $invoice)
    {
        $request->address_id = update4LevelAddress($request, $invoice->address_id);
        if ($invoice->update($request->except(['total', 'status']))) {
            update4LevelAddress($request, $invoice->patient()->first()->address_id);
            $validator = Validator::make([], []);

            // Invoice items For Para-Clinic + Service + Medicine
            $items = array_merge($request->echography ?: [], $request->ecg ?: [], $request->xray ?: [], $request->laboratory ?: [], $request->prescription ?: []);
            $items = array_filter($items, function ($item) {
                return isset($item['chk']);
            });
            $items = array_map(function ($item) {
                $item['exchange_rate'] = d_exchange_rate();
                $item['total'] = ($item['qty'] ?: 0) * ($item['price'] ?: 0);
                return $item;
            }, $items);

            // Delete all old details
            $invoice->detail()->where('status', '1')->delete();

            // Para-Clinic
            $invoice->detail()->createMany($items);

            // Service + Medicine
            $services = [];
            foreach ($request->service_id ?: [] as $index => $id) {
                $services[] = [
                    'service_type'  => $request->service_type[$index] ?: '',
                    'service_name'  => $request->service_name[$index] ?: '',
                    'service_id'    => $request->service_id[$index] ?: 0,
                    'unit_id'       => $request->unit_id[$index] ?: null,
                    'qty'           => $request->qty[$index] ?: 0,
                    'price'         => $request->price[$index] ?: 0,
                    'total'         => ($request->qty[$index] ?: 0) * ($request->price[$index] ?: 0),
                    'exchange_rate' => d_exchange_rate(),
                    'description'   => $request->description[$index] ?: '',
                ];
            }

            // Insert into database
            if (sizeof($services) > 0) {
                $invoice->detail()->createMany($services);
            }

            // Save & Save paid
            $param_update['total'] = $invoice->detail()->sum('total');
            if ($request->status == 2) {
                foreach ($invoice->detail()->where('service_type', 'medicine')->get() as $detail) {
                    if ($product = $detail->product) {
                        $validated = $product->validateStockExist($detail->qty, $detail->unit_id);
                        if ($validated['status'] != true) {
                            $validator->errors()->add($index, $validated['errMsg']);
                        }
                    }
                }

                // Return back with error message
                if ($validator->errors()->all()) {
                    return redirect()->route('invoice.index')->withErrors($validator);
                }

                // Stock calculation
                foreach ($invoice->detail()->where('service_type', 'medicine')->get() as $detail) {
                    if ($product = $detail->product) {
                        $detail['type'] = 'Invoice';
                        $detail['parent_id'] = $detail->id;
                        $detail['document_no'] = $invoice->code;
                        $product->deductStock($detail->qty, $detail->unit_id, $detail);
                    }
                }

                foreach ($invoice->detail()->where('status', '1')->get() as $detail) {
                    if (in_array($detail->service_type, ['echography', 'ecg', 'xray', 'laboratory', 'prescription'])) {
                        $detail->paraClinicItem()->update(['payment_status' => 1, 'status' => 2]);
                    }
                }
                $invoice->detail()->update(['status' => '2']);

                $param_update['payment_status'] = '1';
                $param_update['paid_date'] = date('Y-m-d');
                $param_update['status'] = '2';
            }

            $invoice->update($param_update);

            return redirect()->route('invoice.index')->with('success', 'Data update success');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        if ($invoice->delete()) {
            return redirect()->route('invoice.index')->with('success', 'Data delete success');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function restore($id)
    {
        $row = Invoice::onlyTrashed()->findOrFail($id);
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
        $row = Invoice::onlyTrashed()->findOrFail($id);
        if ($row->forceDelete()) {
            return back()->with('success', __('alert.message.success.crud.force_detele'));
        }
        return back()->with('error', __('alert.message.error.crud.force_detele'));
    }

    /**
     * Display the specified resource.
     */
    public function getDetail(Request $request)
    {
        $row = Invoice::where('id', $request->id)->with(['patient', 'doctor', 'payment', 'detail'])->first();
        if ($row) {
            $row->price = $row->total;
            $body = '';
            $tbody = '';
            foreach ($row->detail as $i => $detail) {
                $tbody .= '<tr>
                    <td class="text-center">' . str_pad(++$i, 2, '0', STR_PAD_LEFT) . '</td>
                    <td class="tw-bg-gray-100">' . $detail->service_name . '</td>
                    <td>' . $detail->description . '</td>
                    <td style="text-align: center;">' . $detail->qty . '</td>
                    <td style="text-align: center;">' . $detail->price . '</td>
                    <th style="text-align: center;">' . number_format($detail->total, 2) . ' USD</th>
                </tr>';
            }
            $body = '<table class="table-form tw-mt-3 table-detail-result">
                <thead>
                    <tr class="text-center">
                        <th class="text-center" style="text-align: center;">N&deg;</th>
                        <th class="tw-bg-gray-100">Service</th>
                        <th>Description</th>
                        <th style="text-align: center;">QTY</th>
                        <th style="text-align: center;">Price</th>
                        <th style="text-align: center;">Total</th>
                    </tr>
                </thead>
                <tbody>' . (sizeof($row->detail) == 0 ? '
                    <tr><th colspan="100%" class="text-center">No result</th></tr>' : $tbody) . '
                    <tr>
                        <td colspan="4" class="text-right">Total USD: </td>
                        <th colspan="2" style="text-align: right;">USD ' . number_format($row->total, 2) . '</th>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right">Echange Rate : </td>
                        <th colspan="2" style="text-align: right;">KHR/USD ' . number_format($row->exchange_rate, 0) . '</th>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-right">Total KHR : </td>
                        <th colspan="2" style="text-align: right;">KHR ' . number_format($row->total * $row->exchange_rate, 0) . '</th>
                    </tr>
                </tbody>
            </table>';
            return response()->json([
                'success' => true,
                'header' => getParaClinicHeaderDetail($row),
                'body' => $body,
                'print_url' => route('invoice.print', $row->id),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Echography not found!',
            ], 404);
        }
    }
}
