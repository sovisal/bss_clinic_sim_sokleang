<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-bottom">
            <div>
                @can('CreateLaborType')
                <x-form.button href="{!! route('setting.labor-type.create', ['type' => request()->type]) !!}" label="Create" icon="bx bx-plus" />
                @endcan
                @can('UpdateLaborType')
                <x-form.button color="dark" href="{!! route('setting.labor-type.sort_order') !!}" label="Sort Order" icon="bx bx-sort-alt-2" />
                @endcan
            </div>
        </div>
    </x-slot>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.name', name: 'name_en'},
                {data: 'dt.parent', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.index', name: 'index'},
                {data: 'dt.items_count', name: 'id', orderable: false, searching: false},
                {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>

    @if (!request()->old)
    <x-card :foot="false" :head="false">
        <x-slot name="header">
            <h5>Labor Service</h5>
        </x-slot>
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th>Name</th>
                    <th>Parent</th>
                    <th>Order</th>
                    <th>Total Item</th>
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
                    <td>{{ d_number($row->index) }}</td>
                    <td>{!! d_obj($row, 'parent', ['name_en', 'name_kh']) !!}</td>
                    <td>{!! d_status($row->status) !!}</td>
                    <td>{!! d_obj($row, 'user', 'name') !!}</td>
                    <td>
                        <x-table-action-btn
                            module="setting.labor-type"
                            module-ability="LaborType"
                            :id="$row->id"
                            :is-trashed="$row->trashed()"
                            :disable-edit="$row->trashed()"
                            :show-btn-show="false"
                        >
                            @can('ViewAnyLaborItem')
                            <x-form.button href="{!! route('setting.labor-item.index', $row->id) !!}" icon="bx bx-detail" />
                            @endcan
                        </x-table-action-btn>
                    </td>
                </tr>
            @endforeach
        </x-table>
    </x-card>
    @endif

    <x-modal-confirm-delete />
</x-app-layout>