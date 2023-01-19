<tr>
    <td>
         <x-bss-form.select name="package_product_unit_id[]" id="" required :select2="false">
            <option value="">Please choose</option>
            @foreach ($units as $data)
            <option value="{{ $data->id }}" {{ $data->id == @$package->product_unit_id ? 'selected' : '' }}>{{ d_obj($data, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-bss-form.select>
    </td>
    <input type="hidden" name="package_qty[]" value="1" />
    <td>
        <x-bss-form.input type="text" name='package_price[]' value="{{ @$package->price ?: 0 }}" class="text-center" />
    </td>
    <td>
        <x-bss-form.input type="text" name='package_code[]' value="{{ @$package->code ?: '' }}"/>
    </td>
    <td>
        <x-form.button color="danger" class="btn-sm" icon="bx bx-trash" onclick="$(this).parents('tr').remove();" />
    </td>
</tr>