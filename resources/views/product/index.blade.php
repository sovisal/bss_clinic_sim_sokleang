<x-app-layout>
    <x-slot name="header">
        <x-form.button href="{{ route('inventory.product.create') }}" icon="bx bx-plus" label="Create" />
    </x-slot>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables">
            <x-slot name="thead">
                <tr>
                    <th width="5%">No</th>
                    <th width="8%">Code</th>
                    <th>Name</th>
                    <th width="8%">Cost</th>
                    <th width="8%">Price</th>
                    <th width="10%">Unit</th>
                    <th width="10%">Type</th>
                    <th width="10%">Category</th>
                    <th width="10%">User</th>
                    <th width="8%">Status</th>
                    <th width="8%">Action</th>
                </tr>
            </x-slot>
            @foreach($rows as $i => $row)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{!! $row->code !!}</td>
                    <td>{!! d_obj($row, ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_currency($row->cost) !!}</td>
                    <td>{!! d_currency($row->price) !!}</td>
                    <td>{!! d_obj($row, 'unit', ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_obj($row, 'type', ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_obj($row, 'category', ['name_kh', 'name_en']) !!}</td>
                    <td>{!! d_obj($row, 'user', 'name') !!}</td>
                    <td>{!! d_status($row->status) !!}</td>
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