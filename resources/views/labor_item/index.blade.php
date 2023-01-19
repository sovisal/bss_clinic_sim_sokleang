<x-app-layout>
    <x-slot name="header">
        <div class="d-flex tw-gap-1 align-items-center">
            <x-form.button color="danger" href="{{ route('setting.labor-type.index') }}" label="Back" icon="bx bx-left-arrow-alt" />
            @can('CreateLaborItem')
            <x-form.button href="{{ route('setting.labor-item.create', $laborType->id) }}" label="Create" icon="bx bx-plus" />
            @endcan
            @can('UpdateLaborItem')
            <x-form.button color="dark" href="{!! route('setting.labor-item.sort_order', $laborType->id) !!}" label="Sort Order" icon="bx bx-sort-alt-2" />
            @endcan
            <div class="ml-1 d-flex tw-gap-1 tw-mt-0.5">
                <i class="bx bxs-right-arrow"></i>
                <span>
                    {{ d_obj($laborType, ['name_kh', 'name_en']) }}
                </span>
            </div>
        </div>
    </x-slot>
    <x-slot name="js">
        <script>
            let table_columns   = [
                {data: 'dt.name', name: 'name_en'},
                {data: 'dt.range', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.type', name: 'id', orderable: false, searching: false},
                {data: 'dt.other', name: 'other'},
                {data: 'dt.index', name: 'index'},
                {data: 'dt.labor_details_count', name: 'id', orderable: false, searching: false},
                {data: 'dt.user', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.status', name: 'id', orderable: false, searching: false}, 
                {data: 'dt.action', name: 'id', orderable: false, searching: false },
            ];

            initDatatableDynamic('#datatables_server', '', table_columns);
        </script>
    </x-slot>
    <x-card :foot="false" :head="false">
        <x-table class="table-hover table-striped" id="datatables_server" data-table="patients">
            <x-slot name="thead">
                <tr>
                    <th>Name</th>
                    <th>Range - Unit</th>
                    <th>Labor Type</th>
                    <th>Item Type</th>
                    <th>Order</th>
                    <th>Total Labor</th>
                    <th>Status</th>
                    <th>User</th>
                    <th>Action</th>
                </tr>
            </x-slot>
            @php($i=0)
            @foreach([] as $row)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ d_obj($row, ['name_kh', 'name_en']) }}</td>
                <td>{!! d_labor_range($row->min_range, $row->max_range) .' '. apply_markdown_character($row->unit) !!}</td>
                <td>{!! d_number(Str::after($row->index, '.')) !!}</td>
                <td>{!! d_obj($row, 'type', ['name_en', 'name_kh']) !!}</td>
                <td>{!! d_text($row->other) !!}</td>
                <td>{!! d_status($row->status) !!}</td>
                <td>{!! d_obj($row, 'user', 'name') !!}</td>
                <td>
                    @can('UpdateLaborItem')
                    <x-form.button color="secondary" href="{{ route('setting.labor-item.edit', [$laborType->id, $row->id]) }}" icon="bx bx-edit-alt" />
                    @endcan
                    @if ($row->trashed())
                        @can('RestoreLaborItem')
                            <x-form.button
                                color="success"
                                data-id="{{ $row->id }}"
                                data-toggle="tooltip"
                                data-placement="top"
                                onclick="if(confirm('Do you really want to restore this record?')){$('#restore-form-{{ $row->id }}').submit()}"
                                title="{{ __('button.crud.restore') }}"
                                icon="bx bx-refresh"
                            />
                            <form class="d-inline" id="restore-form-{{ $row->id }}" action="{{ route('setting.labor-item.restore', $row->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                            </form>
                        @endcan
                        @can('ForceDeleteLaborItem')
                            <x-form.button
                                color="danger"
                                class="confirmDelete"
                                data-id="{{ $row->id }}"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="{{ __('button.crud.force_delete') }}"
                                icon="bx bx-x"
                            />
                            <form class="sr-only" id="form-delete-{{ $row->id }}" action="{{ route('setting.labor-item.force_delete', $row->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="sr-only" id="btn-{{ $row->id }}">Delete</button>
                            </form>
                        @endcan
                    @else
                        @can('DeleteLaborItem')
                            <x-form.button
                                color="danger"
                                class="confirmDelete"
                                data-id="{{ $row->id }}"
                                data-toggle="tooltip"
                                data-placement="top"
                                title="{{ __('button.crud.delete') }}"
                                icon="bx bx-trash"
                            />
                            <form class="sr-only" id="form-delete-{{ $row->id }}" action="{{ route('setting.labor-item.delete', [$laborType->id, $row->id]) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="sr-only" id="btn-{{ $row->id }}">Delete</button>
                            </form>
                        @endcan
                    @endif
                </td>
            </tr>
            @endforeach
        </x-table>
    </x-card>

    <x-modal-confirm-delete />

</x-app-layout>