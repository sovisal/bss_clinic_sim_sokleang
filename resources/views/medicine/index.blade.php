<x-app-layout>
    <x-slot name="header">
        @can('CreateMedicine')
        <x-form.button href="{{ route('setting.medicine.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-slot name="js">
        <script>
            let table_columns   = [
                //{data: 'dt.code', name: 'code'},
                {data: 'dt.name', name: 'name_kh'},
                {data: 'dt.cost', name: 'cost'}, 
                {data: 'dt.price', name: 'price'}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false },
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    {{-- <th width="10%">Code</th> --}}
                    <th>Name</th>
                    <th width="10%">Cost</th>
                    <th width="10%">Price</th>
                    <th width="10%">Status</th>
                    <th width="10%">Action</th>
                </tr>
            </x-slot>
        </x-table>
    </x-card>

    <x-modal-confirm-delete />
</x-app-layout>