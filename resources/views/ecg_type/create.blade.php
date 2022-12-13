<x-app-layout>
    <x-slot name="header">
        <x-form.button href="{{ route('setting.ecg-type.index') }}" color="danger" icon="bx bx-left-arrow-alt" label="Back" />
    </x-slot>
    <form action="{{ route('setting.ecg-type.store') }}" method="POST" autocomplete="off" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <x-card bodyClass="pb-0">
            <x-slot name="action">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>
            <x-slot name="footer">
                <x-form.button type="submit" icon="bx bx-save" label="Save" />
            </x-slot>

            <table class="table-form striped">

                @include('shared.setting_service.form')

                @include('ecg_type.extra_form.0')
            </table>
        </x-card>
    </form>

</x-app-layout>