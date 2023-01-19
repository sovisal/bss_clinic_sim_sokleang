<x-app-layout>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.date', name: 'date'},
                {data: 'dt.document_no', name: 'document_no'},
                {data: 'dt.code', name: 'id', orderable: false, searching: false},
                {data: 'dt.product', name: 'id', orderable: false, searching: false},
                {data: 'dt.qty', name: 'qty'},
                {data: 'dt.unit', name: 'id', orderable: false, searching: false},
                {data: 'dt.price', name: 'price'},
                {data: 'dt.total', name: 'total'},
                {data: 'dt.qty_based', name: 'qty_based'},
                {data: 'dt.p_unit', name: 'id', orderable: false, searching: false},
                {data: 'dt.type', name: 'type'},
                {data: 'dt.action', name: 'id', orderable: false, searching: false},
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-slot name="header">
        @can(('Create'. ($module_ability ?? 'StockOut')))
        <x-form.button href="{{ route(($module ?? 'inventory.stock_out') . '.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>

    <x-filter-stock url="{{ route(($module ?? 'inventory.stock_out') . '.index') }}">
        @if (!isset($module))
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_type" onchange="$('#form-filter').submit()" label="{{ __('form.stock.type') }}">
                <option value="">{{ __('form.all') }}</option>
                @foreach ([
                'StockOut' => 'Stock Out',
                'StockAdjustment' => 'Stock Adjustment',
                'Prescription' => 'Prescription',
                'Invoice' => 'Invoice',
                ] as $key => $name)
                <option value="{{ $key }}" @selected($key==request()->ft_type)>{{ d_text($name) }}</option>
                @endforeach
            </x-form.select>
        </div>
        @endif
    </x-filter-stock>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th>Date</th>
                    <th>Doc.no</th>
                    <th>Code</th>
                    <th>Product</th>
                    <th>QTY_Out</th>
                    <th>Unit</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Base_QTY</th>
                    <th>Unit</th>
                    <th>Type</th>
                    {{-- <th>User</th> --}}
                    <th>Action</th>
                </tr>
            </x-slot>
            @foreach([] as $i => $row)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ d_date($row->date, 'Y-m-d') }}</td>
                <td>{{ d_text($row->document_no) }}</td>
                <td>{!! d_obj($row, 'product', 'code') !!}</td>
                <td>{!! d_obj($row, 'product', 'link') !!}</td>
                <td>{!! d_number($row->qty) !!}</td>
                <td>{!! d_obj($row, 'unit', 'link') !!}</td>
                <td>{!! d_currency($row->price) !!}</td>
                <td>{!! d_currency($row->total) !!}</td>
                <td>{!! d_number($row->qty_based) !!}</td>
                <td>{!! d_obj($row, 'product', 'unit', 'link') !!}</td>
                <td>{!! d_text($row->type) !!}</td>
                {{-- <td>{!! d_obj($row, 'user', 'name') !!}</td> --}}
                <td>
                    <x-table-action-btn module="{{ $module ?? 'inventory.stock_out' }}" module-ability="{{ $module_ability ?? 'StockOut' }}"
                        :id="$row->id" :is-trashed="$row->trashed()" :disable-edit="$row->trashed()" :show-btn-show="false"
                        :show-btn-restore="false" />
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>
    <x-modal-confirm-delete />
</x-app-layout>