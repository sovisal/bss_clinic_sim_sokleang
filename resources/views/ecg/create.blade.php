<x-app-layout>
    <x-slot name="header">
        <x-form.button-back href="{{ route('para_clinic.ecg.index') }}"/>
    </x-slot>
    <x-slot name="js">
        @include('ecg.script')
    </x-slot>

    <form action="{{ route('para_clinic.ecg.store') }}" method="POST" autocomplete="off" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="status" value="1" />
        <x-card bodyClass="pb-0">
            <x-slot name="action">
                <div>
                    <x-form.button type="submit" class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <div>
                    <x-form.button type="submit" class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>
            
            @include('ecg.form_input')
            
        </x-card>
    </form>

</x-app-layout>