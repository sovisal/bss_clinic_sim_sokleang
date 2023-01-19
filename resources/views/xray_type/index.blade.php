<x-app-layout>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.name', name: 'name_en'},
                {data: 'dt.price', name: 'price'},
                {data: 'dt.index', name: 'index'},
                {data: 'dt.xrays_count', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-slot name="header">
        @can('CreateXRayType')
        <x-form.button href="{{ route('setting.xray-type.create') }}" label="Create" icon="bx bx-plus" />
        @endcan
        <x-form.button color="dark" href="{!! route('setting.xray-type.sort_order') !!}" label="Sort Order" icon="bx bx-sort-alt-2" />
    </x-slot>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server" data-table="patients">
            <x-slot name="thead">
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Order</th>
                    <th>Total Xray</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </x-slot>
            @php($i=0)
            @foreach([] as $row)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ d_obj($row, ['name_kh', 'name_en']) }}</td>
                <td>{{ d_currency($row->price) }}</td>
                <td>{{ d_number($row->index) }}</td>
                <td>{{ d_obj($row, 'user', 'name') }}</td>
                <td>{!! d_status($row->status) !!}</td>
                <td>
                    <x-table-action-btn
                        module="setting.xray-type"
                        module-ability="XRayType"
                        :id="$row->id"
                        :is-trashed="$row->trashed()"
                        :disable-edit="$row->trashed()"
                        :show-btn-show="false"
                    />
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-modal-confirm-delete />
</x-app-layout>