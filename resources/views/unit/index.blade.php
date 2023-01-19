<x-app-layout>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.name', name: 'name_kh'},
                {data: 'dt.description', name: 'description'},
                {data: 'dt.products_count', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.packages_count', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-slot name="header">
        @can('CreateProductUnit')
        <x-form.button href="{{ route('setting.unit.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Total Product</th>
                    <th>Total Package</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </x-slot>
            @foreach([] as $i => $row)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ d_obj($row, ['name_kh', 'name_en']) }}</td>
                <td>{!! $row->description !!}</td>
                <td><x-badge>{{ $row->products_count }}</x-badge></td>
                <td>{!! d_obj($row, 'user', 'name') !!}</td>
                <td>{!! d_status($row->status) !!}</td>
                <td>
                    <x-table-action-btn
                        module="inventory.product_unit"
                        module-ability="ProductUnit"
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