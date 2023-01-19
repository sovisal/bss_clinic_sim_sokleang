<x-app-layout>
    <x-slot name="header">
        
    </x-slot>
    <x-slot name="js">
        <script>
            function get_stockin_history (product_id) {
                if (product_id != '') {
                    $.ajax({
                        url: "{{ route('inventory.stock_in.index') }}?ft_product_id=" + product_id,
                        type: "get",
                        success: function (rs) {
                            if (rs) {
                                var total_qty = 0;
                                var build_rows = '';
                                rs.forEach(function (r, i) {
                                    build_rows += `<tr>
                                        <td>${ i + 1 }</td>
                                        <td>${ r.date }</td>
                                        <td>${ r.product.code }</td>
                                        <td>${ r.product.name_en ?? r.product.name_kh }</td>
                                        <td>${ r.supplier.name_en ?? r.supplier.name_kh }</td>
                                        <td>${ r.qty }</td>
                                        <td>${ r.unit.name_en ?? r.unit.name_kh }</td>
                                        <td>${ r.qty_based }</td>
                                        <td>${ r.product.unit.name_en ?? r.product.unit.name_kh }</td>
                                    </tr>`;

                                    total_qty += r.qty_based;
                                });

                                var build_footer = `<tr>
                                    <td colspan="7" class="text-right">Total : </td>
                                    <th colspan="2" class="tw-bg-gray-100">${ total_qty }</th>
                                </tr>`;

                                $("#detail-stockin-history .modal-body .body table tbody").html(build_rows);
                                $("#detail-stockin-history .modal-body .body table tfoot").html(build_footer);
                                $("#detail-stockin-history").modal();
                            }
                        },
                        error: function (rs) {
                            flashMsg("danger", 'Error', rs.message)
                        },
                    })
                }
            }

            function get_stockout_history (product_id) {
                if (product_id != '') {
                    $.ajax({
                        url: "{{ route('inventory.stock_out.index') }}?ft_product_id=" + product_id,
                        type: "get",
                        success: function (rs) {
                            if (rs) {
                                var total_qty = 0;
                                var build_rows = '';
                                rs.forEach(function (r, i) {
                                    build_rows += `<tr>
                                        <td>${ i + 1 }</td>
                                        <td>${ r.date }</td>
                                        <td>${ r.product.code }</td>
                                        <td>${ r.product.name_en ?? r.product.name_kh }</td>
                                        <td>${ r.qty }</td>
                                        <td>${ r.unit.name_en ?? r.unit.name_kh }</td>
                                        <td>${ r.qty_based }</td>
                                        <td>${ r.product.unit.name_en ?? r.product.unit.name_kh }</td>
                                        <td>${ r.type }</td>
                                    </tr>`;

                                    total_qty += r.qty_based;
                                });

                                var build_footer = `<tr>
                                    <td colspan="6" class="text-right">Total : </td>
                                    <th colspan="3" class="tw-bg-gray-100">${ total_qty }</th>
                                </tr>`;

                                $("#detail-stockout-history .modal-body .body table tbody").html(build_rows);
                                $("#detail-stockout-history .modal-body .body table tfoot").html(build_footer);
                                $("#detail-stockout-history").modal();
                            }
                        },
                        error: function (rs) {
                            flashMsg("danger", 'Error', rs.message)
                        },
                    })
                }
            }
        </script>
        <script>
            let table_columns   = [
                {data: 'dt.code', name: 'code'},
                {data: 'dt.link', name: 'name_kh'},
                {data: 'dt.unit', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.type', name: 'id', orderable: false, searching: false},
                {data: 'dt.category', name: 'id', orderable: false, searching: false},
                {data: 'dt.qty_in', name: 'qty_in'},
                {data: 'dt.qty_out', name: 'qty_out'},
                {data: 'dt.qty_alert', name: 'qty_alert'},
                {data: 'dt.qty_remain', name: 'qty_remain'},
                {data: 'dt.status', name: 'id', orderable: false, searching: false },
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
                    <th width="10%">Base Unit</th>
                    <th width="10%">Type</th>
                    <th width="10%">Category</th>
                    <th width="8%">QTY IN</th>
                    <th width="8%">QTY OUT</th>
                    <th width="8%">QTY Alert</th>
                    <th width="8%">QTY Remain</th>
                    <th width="8%">Status</th>
                </tr>
            </x-slot>
            <!-- Dynamic data table -->
            @foreach([] as $i => $row)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{!! $row->code !!}</td>
                    <td>{!! $row->link !!}</td>
                    <td>{!! d_obj($row, 'unit', 'link') !!}</td>
                    <td>{!! d_obj($row, 'type', 'link') !!}</td>
                    <td>{!! d_obj($row, 'category', 'link') !!}</td>
                    <td>{!! d_link(d_number($row->qty_in), "javascript:get_stockin_history(" . $row->id . ")") !!}</td>
                    <td>{!! d_link(d_number($row->qty_out), "javascript:get_stockout_history(" . $row->id . ")") !!}</td>
                    <td>{!! d_number($row->qty_alert) !!}</td>
                    <td><span style="color: {{ d_number($row->qty_remain) == 0 ? 'red' : 'green' }};">
                        {!! d_number($row->qty_remain) !!}
                    </span></td>

                    @if ($row->qty_remain == 0)
                        <td>{!! d_status(false, 'Out of Stock') !!}</td>
                    @elseif ($row->qty_remain > 0 && $row->qty_remain < $row->qty_alert) 
                        <td>{!! d_status(false, 'Almost Out of Stock', '', 'badge-warning') !!}</td>
                    @else
                        <td>{!! d_status(true) !!}</td>
                    @endif
                </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-modal id="detail-stockin-history" dialogClass="modal-xl">
        <x-slot name="header">Total Stockin History</x-slot>
        <div class="body">
            <table class="table-hover table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="tw-bg-gray-100" width="3%">No</th>
                        <th class="tw-bg-gray-100" width="8%">Date</th>
                        <th class="tw-bg-gray-100" width="10%">Code</th>
                        <th class="tw-bg-gray-100" width="10%">Product</th>
                        <th class="tw-bg-gray-100" width="10%">Supplier</th>
                        <th class="tw-bg-gray-100" width="5%">Qty_In</th>
                        <th class="tw-bg-gray-100" width="5%">Unit</th>
                        <th class="tw-bg-gray-100" width="5%">Base_Qty</th>
                        <th class="tw-bg-gray-100" width="5%">Unit</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
        </div>
    </x-modal>
    <x-modal id="detail-stockout-history" dialogClass="modal-xl">
        <x-slot name="header">Total Stockout History</x-slot>
        <div class="body">
            <table class="table-hover table-bordered" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="tw-bg-gray-100" width="3%">No</th>
                        <th class="tw-bg-gray-100" width="8%">Date</th>
                        <th class="tw-bg-gray-100">Code</th>
                        <th class="tw-bg-gray-100">Product</th>
                        <th class="tw-bg-gray-100" width="8%">QTY_Out</th>
                        <th class="tw-bg-gray-100" width="10%">Unit</th>
                        <th class="tw-bg-gray-100" width="8%">Base_QTY</th>
                        <th class="tw-bg-gray-100" width="8%">Unit</th>
                        <th class="tw-bg-gray-100" width="8%">Type</th>
                    </tr>
                </thead>
                <tbody></tbody>
                <tfoot></tfoot>
            </table>
        </div>
    </x-modal>
</x-app-layout>