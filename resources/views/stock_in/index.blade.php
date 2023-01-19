<x-app-layout>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.date', name: 'date'},
                {data: 'dt.code', name: 'id', orderable: false, searching: false},
                {data: 'dt.product', name: 'id', orderable: false, searching: false},
                {data: 'dt.supplier', name: 'id', orderable: false, searching: false},
                {data: 'dt.qty', name: 'qty'},
                {data: 'dt.unit', name: 'id', orderable: false, searching: false},
                {data: 'dt.price', name: 'price'},
                {data: 'dt.total', name: 'total'},
                {data: 'dt.qty_based', name: 'qty_based'},
                {data: 'dt.p_unit', name: 'id', orderable: false, searching: false},
                {data: 'dt.qty_used', name: 'qty_used'},
                {data: 'dt.qty_remain', name: 'qty_remain'},
                {data: 'dt.exp_status', name: 'id', orderable: false, searching: false},
                {data: 'dt.reciept_no', name: 'reciept_no'}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false},
                {data: 'dt.action', name: 'id', orderable: false, searching: false},
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-slot name="header">
        @can('CreateStockIn')
        <x-form.button href="{{ route('inventory.stock_in.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-filter-stock url="{{ route('inventory.stock_in.index') }}">
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_supplier_id" :url="route('inventory.supplier.index')" onchange="$('#form-filter').submit()"
                label="{{ __('form.stock.supplier') }}">
                <option value="">{{ __('form.all') }}</option>
                @foreach ($suppliers as $supplier)
                <option value="{{ $supplier->id }}" @selected($supplier->id == request()->ft_supplier_id)>{{ d_obj($supplier, ['name_en', 'name_kh'])
                    }}</option>
                @endforeach
            </x-form.select>
        </div>
        <div class="col-sm-4 col-md-3">
            <div class="row">
                <div class="col-md-6">
                    <x-form.select name="ft_status" data-no_search="true" onchange="$('#form-filter').submit()" label="{{ __('form.stock.status') }}">
                        <option value="">{{ __('form.all') }}</option>
                        @foreach ([
                        'active' => 'Active',
                        'closed' => 'Closed'
                        ] as $key => $name)
                        <option value="{{ $key }}" @selected($key==request()->ft_status)>{{ d_text($name) }}</option>
                        @endforeach
                    </x-form.select>
                </div>
                <div class="col-md-6">
                    <x-form.select name="ft_exp_status" data-no_search="true" onchange="$('#form-filter').submit()"
                        label="{{ __('form.stock.exp_status') }}">
                        <option value="">{{ __('form.all') }}</option>
                        @foreach ([
                        'active' => 'Active',
                        'expired' => 'Expired',
                        ] as $key => $name)
                        <option value="{{ $key }}" @selected($key==request()->ft_exp_status)>{{ d_text($name) }}</option>
                        @endforeach
                    </x-form.select>
                </div>
            </div>
        </div>
    </x-filter-stock>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th width="8%">Date</th>
                    <th width="10%">Code</th>
                    <th width="10%">Product</th>
                    <th width="10%">Supplier</th>
                    <th width="5%">Qty_In</th>
                    <th width="5%">Unit</th>
                    <th width="8%">Price</th>
                    <th width="8%">Total</th>
                    <th width="5%">Base_Qty</th>
                    <th width="5%">Unit</th>
                    <th width="5%">Used</th>
                    <th width="5%">Remain</th>
                    <th width="8%">Expire</th>
                    <th>Reciept no</th>
                    {{-- <th width="8%">User</th> --}}
                    <th width="5%">Status</th>
                    <th width="8%">Action</th>
                </tr>
            </x-slot>
            @foreach([] as $i => $row)
            <tr>
                <td>{{ d_date($row->date, 'Y-m-d') }}</td>
                <td>{!! d_obj($row, 'product', 'code') !!}</td>
                <td>{!! d_obj($row, 'product', 'link') !!}</td>
                <td>{!! d_obj($row, 'supplier', 'link') !!}</td>
                <td>{{ d_number($row->qty) }}</td>
                <td>{!! d_obj($row, 'unit', 'link') !!}</td>
                <td>{{ d_currency($row->price) }}</td>
                <td>{{ d_currency($row->total) }}</td>
                <td>{{ d_number($row->qty_based) }}</td>
                <td>{!! d_obj($row, 'product', 'unit', 'link') !!}</td>
                <td>{{ d_number($row->qty_used) }}</td>
                <td>{{ d_number($row->qty_remain) }}</td>

                @if ($row->exp_date > date('Y-m-d'))
                <td>{!! d_status(true, '', d_date($row->exp_date, 'Y-m-d'), '', 'badge-success') !!}</td>
                @else
                <td>{!! d_status(false, d_date($row->exp_date, 'Y-m-d')) !!}</td>
                @endif

                <td>{{ $row->reciept_no }}</td>
                {{-- <td>{{ d_obj($row, 'user', 'name') }}</td> --}}
                @if ($row->qty_remain > 0)
                <td>{!! d_status(true, '', 'Stock Active') !!}</td>
                @else
                <td>{!! d_status(false, 'Stock Closed', '', 'badge-light') !!}</td>
                @endif
                <td>
                    <x-table-action-btn module="inventory.stock_in" module-ability="StockIn" :id="$row->id" :is-trashed="$row->trashed()"
                        :disable-edit="$row->trashed()" :disable-delete="$row->stock_outs_count > 0" :show-btn-show="false" />
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>
    <x-modal-confirm-delete />
</x-app-layout>