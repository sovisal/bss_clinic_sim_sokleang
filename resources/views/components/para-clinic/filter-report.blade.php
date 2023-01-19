<x-filter :url="$url">
    <div class="col-sm-3 col-md-2">
        <x-form.daterangepicker
            name="ft_daterangepicker"
            value="{{ request()->ft_daterangepicker }}"
            drpStart="{{ request()->ft_daterangepicker_drp_start }}"
            drpEnd="{{ request()->ft_daterangepicker_drp_end }}"
            data-submit="#form-filter"
            label="Date"
        />
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_patient_id" :url="route('patient.index')" class="filter-input" label="patient">
            <option value="">{{ __('form.all') }}</option>
            @foreach ($patients as $patient)
            <option value="{{ $patient->id }}" {{ ((request()->ft_patient_id == $patient->id) ? 'selected' : '') }}>{{ d_obj($patient, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-form.select>
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_doctor_id" class="filter-input" label="doctor">
            <option value="">{{ __('form.all') }}</option>
            @foreach ($doctors as $doctor)
            <option value="{{ $doctor->id }}" {{ ((request()->ft_doctor_id == $doctor->id) ? 'selected' : '') }}>{{ d_obj($doctor, ['name_en', 'name_kh']) }}</option>
            @endforeach
        </x-form.select>
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_payment_status" class="filter-input" label="Payment">
            <option value="">{{ __('form.all') }}</option>
            @foreach (['unpaid' => 'Unpaid', 'paid' => 'Paid'] as $id => $value)
            <option value="{{ $id }}" {{ ((request()->ft_payment_status == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
            @endforeach
        </x-form.select>
    </div>
    <div class="col-sm-3 col-md-2">
        <x-form.select name="ft_status" class="filter-input" label="Status">
            <option value="">{{ __('form.all') }}</option>
            @foreach (['active' => 'Active', 'complete' => 'Complete'] as $id => $value)
            <option value="{{ $id }}" {{ ((request()->ft_status == $id) ? 'selected' : '') }}>{{ d_text($value) }}</option>
            @endforeach
        </x-form.select>
    </div>

    {!! $slot !!}
</x-filter>
