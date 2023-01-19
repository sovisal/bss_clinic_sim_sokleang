<tr>
    <td colspan="2" class="text-left tw-bg-gray-100">Product Unit Information</td>
</tr>

<x-bss-form.input-row name="name_kh" :value="old('name_kh', @$row->name_kh)" required autofocus label="Name in Khmer" />
<x-bss-form.input-row name="name_en" :value="old('name_en', @$row->name_en)" label="Name in English" />
<x-bss-form.textarea-row name="description" label="Description">{!! old('description', @$row->description) !!}</x-bss-form.textarea-row>