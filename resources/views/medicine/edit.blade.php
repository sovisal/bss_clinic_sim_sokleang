<x-app-layout>
    <x-slot name="header">
        <x-form.button-back href="{{ route('setting.medicine.index') }}" />
    </x-slot>
    <x-slot name="js">
        @include('medicine.script')
    </x-slot>
    <form action="{{ route('setting.medicine.update', $row->id) }}" method="POST" autocomplete="off">
        @method('PUT')
        @csrf
        <x-card bodyClass="pb-0">
            <x-slot name="action">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>
            <x-slot name="footer">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>
            
            @include('medicine.form')
            <br />
            @include('medicine.form_unit')
        </x-card>
    </form>

    <div>
        <table id="sample_unit" class="hidden">
            @include('medicine.form_unit_sample')
        </table>
    </div>
</x-app-layout>