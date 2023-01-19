<x-app-layout>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.code', name: 'code'},
                {data: 'dt.patient', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.gender', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.age', name: 'age'}, 
                {data: 'dt.address', name: 'id', orderable: false, searching: false},
                {data: 'dt.doctor_requested', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.requested_at', name: 'requested_at'}, 
                {data: 'dt.doctor', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.analysis_at', name: 'analysis_at'}, 
                {data: 'dt.price', name: 'price'}, 
                {data: 'dt.payment_status', name: 'payment_status'}, 
                // {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', orderable: false, name: 'patient.name_en'},
                {data: 'dt.action', orderable: false, name: 'patient.name_kh'},
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-slot name="header">
        @if(isset($_GET['back']))
        <x-form.button-back href="{!! route('setting.xray-type.index') !!}" />
        @endif
        @can('CreateLaboratory')
        <x-form.button href="{{ route('para_clinic.labor.create') }}" label="Create" icon="bx bx-plus" />
        @endcan
    </x-slot>

    <x-filter-report url="{{ route('para_clinic.labor.index') }}" />
    <x-card :foot="false" :action-show="false">
        <x-table class="table-hover table-striped" id="datatables_server" data-table="patients">
            <x-slot name="thead">
                <tr>
                    <th>Code</th>
                    <th>Patient</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Address</th>
                    <th>Requested By</th>
                    <th>Requested Date</th>
                    <th>Physician</th>
                    <th>Analysis At</th>
                    <th>Price</th>
                    <th>Payment</th>
                    <!-- <th>User</th> -->
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </x-slot>
            @foreach([] as $i => $row)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{!! d_link($row->code, "javascript:getDetail(" . $row->id . ", '" . route('para_clinic.labor.getDetail', 'ECG Detail') . "')") !!}
                </td>
                <td>{!! d_obj($row, 'patient', 'link') !!}</td>
                <td>{{ d_obj($row, 'gender', ['title_en', 'title_kh']) }}</td>
                <td>{{ d_obj($row, 'age') }}</td>
                <td>{{ d_obj($row, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh']) }}</td>
                <td>{{ render_readable_date($row->requested_at) }}</td>
                <td>{!! d_obj($row, 'doctor', 'link') !!}</td>
                <td>{{ d_currency($row->price) }}</td>
                <td>{!! d_paid_status($row->payment_status) !!}</td>
                <td>{{ d_obj($row, 'user', 'name') }}</td>
                <td>{!! d_para_status($row->status) !!}</td>
                <td>
                    <x-table-action-btn module="para_clinic.labor" module-ability="Laboratory" :id="$row->id" :is-trashed="$row->trashed()"
                        :disable-edit="$row->trashed() || !($row->status=='1' && $row->payment_status == 0)"
                        :disable-delete="!($row->status=='1' && $row->payment_status == 0)" :show-btn-show="false" :show-btn-force-delete="true">
                        @can('PrintLaboratory')
                        <x-form.button color="dark" class="btn-sm" onclick="printPopup('{{ route('para_clinic.labor.print', $row->id) }}')"
                            icon="bx bx-printer" />
                        @endcan
                    </x-table-action-btn>
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-para-clinic.modal-detail />
    <x-modal-confirm-delete />

</x-app-layout>