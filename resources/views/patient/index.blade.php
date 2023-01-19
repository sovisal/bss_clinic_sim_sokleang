<x-app-layout>
    <x-slot name="header">
        @can('CreatePatient')
        <x-form.button href="{{ route('patient.create') }}" class="btn-sm" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-slot name="js">
        <script>
            localStorage.setItem("treament_plan_tab", '');

            let table_columns   = [
                {data: 'dt.code', name: 'id'},
                {data: 'dt.patient', name: 'id', orderable: false, searching: false},
                {data: 'dt.gender', name: 'gender.title_kh', orderable: false, searching: false}, 
                {data: 'dt.age', name: 'age'}, 
                {data: 'dt.phone', name: 'phone'}, 
                {data: 'dt.address', name: 'id', orderable: false, searching: false},
                {data: 'dt.registered_at', name: 'registered_at'},
                {data: 'dt.user', name: 'id', orderable: false, searching: false},
                {data: 'dt.status', orderable: false, name: 'name_en'},
                {data: 'dt.action', orderable: false, name: 'name_kh'},
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>

    <x-filter>
        <div class="col-sm-3 col-md-2">
            <x-bss-form.select-row name="ft_address_id" class="filter-input" label="Province">{!! $address_options !!}</x-bss-form.select-row>
        </div>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_gender_id" class="filter-input" label="Gender">
                <option value="">{{ __('form.all') }}</option>
                @foreach ($genders as $id => $value)
                <option value="{{ $id }}" {{ ((request()->ft_gender_id == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_marital_status_id" class="filter-input" label="Marital">
                <option value="">{{ __('form.all') }}</option>
                @foreach ($marital_statuses as $id => $value)
                <option value="{{ $id }}" {{ ((request()->ft_marital_status_id == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_nationality_id" class="filter-input" label="Nationality">
                <option value="">{{ __('form.all') }}</option>
                @foreach ($nationalities as $id => $value)
                <option value="{{ $id }}" {{ ((request()->ft_nationality_id == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_enterprise_id" class="filter-input" label="Enterprise">
                <option value="">{{ __('form.all') }}</option>
                @foreach ($enterprises as $id => $value)
                <option value="{{ $id }}" {{ ((request()->ft_enterprise_id == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
    </x-filter>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped table-padding-sm" id="datatables_server" data-table="patients">
            <x-slot name="thead">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Age</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Registered at</th>
                    <th>User</th>
                    <th>Status</th>
                    <th width="10%">{!! __('table.action') !!}</th>
                </tr>
            </x-slot>
            <!-- Dynamic data table -->
        </x-table>
    </x-card>
    <x-modal-confirm-delete />
    
</x-app-layout>
