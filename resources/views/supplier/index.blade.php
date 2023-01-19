<x-app-layout>
    <x-slot name="header">
        @can('CreateSupplier')
        <x-form.button href="{{ route('inventory.supplier.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.name', name: 'name_kh'},
                {data: 'dt.type', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.category', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.contact_name', name: 'contact_name'},
                {data: 'dt.contact_number', name: 'contact_number'},
                {data: 'dt.address', name: 'id', orderable: false, searching: false}, 
                // {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', name: 'status', searching: false}, 
                {data: 'dt.action', name: 'id', orderable: false, searching: false}, 
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    
    <x-filter-product>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_status" class="filter-input" label="{{ __('form.status') }}">
                <option value="">{{ __('form.all') }}</option>
                @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $id => $value)
                <option value="{{ $id }}" @selected($id == request()->ft_status)>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
    </x-filter-product>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Address</th>
                    {{-- <th>User</th> --}}
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </x-slot>
            <!-- Dynamic data table -->
            @foreach([] as $i => $row)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{!! d_obj($row, ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_obj($row, 'type', 'link') !!}</td>
                    <td>{!! d_obj($row, 'category', 'link') !!}</td>
                    <td>{!! d_text($row->contact_name) !!}</td>
                    <td>{{ d_obj($row, 'address', ['village_kh', 'commune_kh', 'district_kh', 'province_kh'] ) }}</td>
                    <td>{!! d_obj($row, 'user', 'name') !!}</td>
                    <td>{!! d_status($row->status) !!}</td>
                    <td>
                        <x-table-action-btn
                            module="inventory.supplier"
                            module-ability="Supplier"
                            :id="$row->id"
                            :is-trashed="$row->trashed()"
                            :disable-edit="$row->trashed()"
                            :show-btn-show="false"
                            :show-btn-force-delete="true"
                        />
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-modal-confirm-delete />
</x-app-layout>