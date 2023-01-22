<table class="table-stock-out-item table-form striped {{ ((isset($row->error) && $row->error !== '')? 'is-invalid' : '') }}">
    <tr>
        <th colspan="6" class="tw-bg-gray-100">
            <div class="d-flex justify-content-between align-items-center">
                <div><i class='bx bx-grid-vertical mr-25 font-medium-4 cursor-move'></i> Information</div>
                <x-form.button href="javascript:void(0)" color="danger" class="btn-remove-stock-out" icon="bx bx-trash" onclick="$(this).closest('tr.stock-out-item').remove();" label="Remove"/>
            </div>
        </th>
    </tr>
    <tr>
        <x-bss-form.input-row name="date[]" id="" labelWidth="15%" :tr="false" class="date-picker" value="{{ $row->date ?? date('Y-m-d') }}" required hasIcon="right" icon="bx bx-calendar" label="Date" />
        <x-bss-form.select-row name="product_id[]" id="" labelWidth="15%" :url="route('inventory.product.index')" :tr="false" :select2="false" required label="Product">
            @if (isset($row) && $row->product_option != '')
                {!! $row->product_option !!}
            @else
                <option value="">---- None ----</option>
            @endif
        </x-bss-form.select-row>
        <x-bss-form.input-row name="qty[]" id="" labelWidth="15%" :tr="false" class="is_number qty" value="{{ @$row->qty }}" required label="QTY Out" />
    </tr>
    <tr>
        {{-- <x-bss-form.input-row name="reciept_no[]" id="" labelWidth="15%" :tr="false" value="{{ @$row->reciept_no }}" label="Reciept Number" /> --}}
        <td colspan="2"></td>
        <x-bss-form.select-row name="unit_id[]" id="" labelWidth="15%" :tr="false" class="unit_id" :select2="false" label="Unit">
            @if (isset($row) && $row->unit_option != '')
                {!! $row->unit_option !!}
            @else
                <option value="">---- None ----</option>
            @endif
        </x-bss-form.select-row>
        <x-bss-form.input-row name="price[]" id="" labelWidth="15%" :tr="false" class="is_number price" value="{{ @$row->price }}" required label="Price" />
    </tr>
    <input type="hidden" name="qty_based[]" value="{{ @$row->qty_based }}" readonly/>
    <tr>
        @if (isset($module) && $module == 'inventory.stock_adjustment')
            <td class="text-right">
                <label for="note">Reason <small class="required">*</small></label>
            </td>
            <td colspan="3">
                <x-bss-form.textarea name="note[]" rows="1" required>{!! @$row->note !!}</x-bss-form.textarea>
            </td>
        @else
            <td class="text-right">
                <label for="note">Reason</label>
            </td>
            <td colspan="3">
                <x-bss-form.textarea name="note[]" rows="1">{!! @$row->note !!}</x-bss-form.textarea>
            </td>
        @endif
        <x-bss-form.input-row name="total[]" id="" labelWidth="15%" :tr="false" class="is_number total" value="{{ @$row->total }}" readonly label="Total" />
    </tr>
</table>
@if(isset($row->error) && $row->error !== '')
    <small class="text-danger"><i class="bx bx-radio-circle tw-text-base"></i> {!! $row->error !!}</small>
@endif