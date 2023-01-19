<x-app-layout>
    <x-slot name="header">
        @if(isset($_GET['back']))
        <x-form.button-back href="{!! route('setting.xray-type.index') !!}" />
        @endif
        @can('CreateEcg')
        <x-form.button href="{{ route('para_clinic.ecg.create') }}" label="Create" icon="bx bx-plus" />
        @endcan
    </x-slot>
    <x-slot name="css">
        <style>
            #image-slider {
                width: 600px;
                margin: 10px auto;
                overflow: hidden;
            }

            #image-slider .carousel-inner {
                border-radius: 0;
            }
        </style>
    </x-slot>
    <x-slot name="js">
        <script>
            function getImage(img_1, img_2) {
                var inner_slider;
                $('#image-modal .modal-body .no-photo').remove();
                if (img_1 != '' || img_2 != '') {
                    $('#image-modal #image-slider').removeClass('sr-only');
                    if (img_1 != '') {
                        inner_slider = `<div class="carousel-item active">
                                            <img src="/images/ecgs/${ img_1 }" class="d-block w-100" alt="...">
                                        </div>`;
                    }
                    if (img_2 != '') {
                        inner_slider += `<div class="carousel-item ${ ((img_1 == '')? 'active' : '') }">
                                            <img src="/images/ecgs/${ img_2 }" class="d-block w-100" alt="...">
                                        </div>`;
                    }
                    $('#image-modal #image-slider .carousel-inner').html(inner_slider);
                } else {
                    $('#image-modal #image-slider').addClass('sr-only');
                    $('#image-modal .modal-body').append('<div class="no-photo text-center py-1">No photo</div>');
                }
                $('#image-modal').modal();
            }
        </script>

        <script>
            let table_columns   = [
                {data: 'dt.code', name: 'code'},
                {data: 'dt.type', name: 'id', orderable: false, searching: false}, 
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

    <x-filter-report url="{{ route('para_clinic.ecg.index') }}" />
    <x-card :foot="false" :action-show="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server" data-table="patients">
            <x-slot name="thead">
                <tr>
                    <th>Code</th>
                    <th>Form</th>
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
                <td>{!! d_link($row->code, "javascript:getDetail(" . $row->id . ", '" . route('para_clinic.ecg.getDetail', 'ECG Detail') . "')") !!}
                </td>
                <td>{!! $row->typeLink !!}</td>
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
                    <x-table-action-btn module="para_clinic.ecg" module-ability="Ecg" :id="$row->id" :is-trashed="$row->trashed()"
                        :disable-edit="$row->trashed() || !($row->status=='1' && $row->payment_status == 0)"
                        :disable-delete="!($row->status=='1' && $row->payment_status == 0)" :show-btn-show="false">
                        <x-form.button color="warning" class="btn-sm" onclick="getImage('{{ $row->image_1 }}', '{{ $row->image_2 }}')"
                            icon="bx bx-image" />
                        @can('PrintEcg')
                        <x-form.button color="dark" class="btn-sm" onclick="printPopup('{{ route('para_clinic.ecg.print', $row->id) }}')"
                            icon="bx bx-printer" />
                        @endcan
                    </x-table-action-btn>
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-para-clinic.modal-detail />
    <x-modal :foot="false" id="image-modal" dialogClass="modal-lg">
        <x-slot name="header">
            View ECG Photo
        </x-slot>
        <x-slider id="image-slider" :autoplay="false" />
    </x-modal>
    <x-modal-confirm-delete />
</x-app-layout>