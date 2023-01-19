<x-app-layout>
    <x-slot name="header">
        @can('CreateProduct')
        <x-form.button href="{{ route('inventory.product.create') }}" icon="bx bx-plus" label="Create" />
        @endcan
    </x-slot>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.code', name: 'code'},
                {data: 'dt.name', name: 'name_kh'},
                {data: 'dt.cost', name: 'cost'}, 
                {data: 'dt.price', name: 'price'}, 
                {data: 'dt.unit', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.type', name: 'id', orderable: false, searching: false},
                {data: 'dt.category', name: 'id', orderable: false, searching: false},
                {data: 'dt.qty_alert', name: 'qty_alert'},
                {data: 'dt.qty_remain', name: 'qty_remain'},
                //{data: 'dt.user', name: 'user.name', orderable: false, searching: false},
                {data: 'dt.status', name: 'id', orderable: false, searching: false },
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>

    <x-filter-product>
        <div class="col-sm-3 col-md-2">
            <x-form.select name="ft_status" class="filter-input" label="{{ __('form.status') }}">
                <option value="">{{ __('form.all') }}</option>
                @foreach (['remain' => 'Remain', 'out_of_stock' => 'Out of stock'] as $id => $value)
                <option value="{{ $id }}" @selected($id == request()->ft_status)>{{ d_text($value) }}</option>
                @endforeach
            </x-form.select>
        </div>
    </x-filter-product>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server">
            <x-slot name="thead">
                <tr>
                    <th width="8%">Code</th>
                    <th>Name</th>
                    <th width="8%">Cost</th>
                    <th width="8%">Price</th>
                    <th width="10%">Base Unit</th>
                    <th width="10%">Type</th>
                    <th width="10%">Category</th>
                    <th width="8%">Alert</th>
                    <th width="8%">Remain</th>
                    {{-- <th width="8%">User</th> --}}
                    <th width="8%">Status</th>
                    <th width="6%">Action</th>
                </tr>
            </x-slot>
            <!-- Dynamic data table -->
            @foreach([] as $i => $row)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{!! $row->code !!}</td>
                    <td>{!! d_obj($row, ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_currency($row->cost) !!}</td>
                    <td>{!! d_currency($row->price) !!}</td>
                    <td>{!! d_obj($row, 'unit', 'link') !!}</td>
                    <td>{!! d_obj($row, 'type', 'link') !!}</td>
                    <td>{!! d_obj($row, 'category', 'link') !!}</td>
                    <td>{{ d_number($row->qty_alert) }}</td>
                    <td><span style="color: {{ d_number($row->qty_remain) == 0 ? 'red' : 'green' }};">
                        {!! d_number($row->qty_remain) !!}
                    </span></td>
                    <td>{{ d_obj($row, 'user', 'name') }}</td>

                    @if ($row->qty_remain == 0)
                        <td>{!! d_status(false, 'Out of Stock') !!}</td>
                    @elseif ($row->qty_remain > 0 && $row->qty_remain <= $row->qty_alert) 
                        <td>{!! d_status(false, 'Almost Out of Stock', '', 'badge-warning') !!}</td>
                    @else
                        <td>{!! d_status(true) !!}</td>
                    @endif

                    <td>
                        <x-table-action-btn
                            module="inventory.product"
                            module-ability="Product"
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