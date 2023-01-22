<x-app-layout>
    <x-slot name="header">
        <x-form.button-back href="{{ route(($module ?? 'inventory.stock_out') . '.index') }}" />
    </x-slot>
    <x-slot name="js">
        @include('stock_out.script')
    </x-slot>
    <form action="{{ route(($module ?? 'inventory.stock_out') . '.update', $row->id) }}" method="POST" autocomplete="off">
        @method('PUT')
        @csrf
        <x-card bodyClass="pb-0">
            <x-slot name="action">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>
            <x-slot name="footer">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>

            <table class="table-stock-out-item table-form striped">
                <tr>
                    <th colspan="6" class="tw-bg-gray-100">{{ $title ?? 'Stock Out' }} Information</th>
                </tr>
                <x-bss-form.input-row name="date" id="" labelWidth="15%" class="date-picker" value="{{ old('date', $row->date) }}" required hasIcon="right" icon="bx bx-calendar" label="Date" />
                <x-bss-form.input-row name="reciept_no" id="" labelWidth="15%" value="{{ old('reciept_no', $row->document_no) }}" disabled label="Reciept Number" />
                <x-bss-form.input-row name="price" id="" labelWidth="15%" class="is_number price" value="{{ old('price', $row->price) }}" required label="Price" />
                <x-bss-form.textarea-row name="note" id="" labelWidth="15%" required label="Note" >{!! old('note', $row->note) !!}</x-bss-form.textarea-row>
            </table>
        </x-card>
    </form>

    <x-modal-image-crop />
</x-app-layout>