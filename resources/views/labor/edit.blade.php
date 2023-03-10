<x-app-layout>
    <x-slot name="header">
        <x-form.button-back href="{{ route('para_clinic.labor.index') }}"/>
    </x-slot>
    <x-slot name="js">
        @include('labor.script')
        <script>
            $('.btn-submit, .btn-success').click(function (){
                $('[name="status"]').val($(this).val());
            });
        </script>
    </x-slot>
    <form id="labor-form" action="{{ route('para_clinic.labor.update', $row) }}" method="POST" autocomplete="off" enctype="multipart/form-data">
        @method('PUT')
        @csrf
        <input type="hidden" name="status" value="{{ $row->status ?: 1 }}" />
        <x-card bodyClass="pb-0">
            <x-slot name="action">
                <div>
                    <x-form.button type="submit" class="btn-success" value="2" icon="bx bx-check" label="Complete" />
                    <x-form.button type="submit" class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <div>
                    <x-form.button type="submit" class="btn-success" value="2" icon="bx bx-check" label="Complete" />
                    <x-form.button type="submit" class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>

            @include('labor.form_input')
            <br/>

            @include('labor.form_result')
        </x-card>
    </form>

</x-app-layout>