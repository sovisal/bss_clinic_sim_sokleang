
<table class="table-form striped">
    {{-- Vaccination --}}
    <tr>
        <td rowspan="3" class="text-right">Vaccination</td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_bgc_hepb' label="BCG/HepB" :checked="$consultation->pmr_bgc_hepb" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_opv_dpt_depb_hib1' label="OPV+DPT+HepB-Hib1"
                    :checked="$consultation->pmr_opv_dpt_depb_hib1" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_opv_dpt_depb_hib2' label="OPV+DPT+HepB-Hib2"
                    :checked="$consultation->pmr_opv_dpt_depb_hib2" />
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_opv_dpt_depb_hib3' label="OPV+DPT+HepB-Hib3"
                    :checked="$consultation->pmr_opv_dpt_depb_hib3" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_measles_jdtofrech' label="Measles+JDToFrench(juliandaycount)"
                    :checked="$consultation->pmr_measles_jdtofrech" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_tetanus' label="Tetanus" :checked="$consultation->pmr_tetanus" />
            </div>
        </td>
    </tr>
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_none' label="None" :checked="$consultation->pmr_none" />
            </div>
        </td>
        <td colspan="2"></td>
    </tr>

    {{-- Over Blood Pressure --}}
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_over_blood_pressure' label="Over blood pressure"
                    :checked="$consultation->pmr_over_blood_pressure" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_diabet' label="Diabet" :checked="$consultation->pmr_diabet" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_tuberculosis' label="Tuberculosis" :checked="$consultation->pmr_tuberculosis" />
            </div>
        </td>
        <td></td>
    </tr>
    {{-- Cardio Vascular --}}
    <tr>
        <td class="text-right">
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_cardio_vascular' class="data_parent" label="Cardio Vascular"
                    :checked="$consultation->pmr_cardio_vascular" />
            </div>
        </td>
        <td>
            <div class="tw-mb-2">
                <x-form.checkbox name='pmr_cardio_vascular_coronary_disease' data-parent="pmr_cardio_vascular"
                    label="Coronary Disease" :checked="$consultation->pmr_cardio_vascular_coronary_disease" />
            </div>
            <div class="tw-mb-2">
                <x-form.checkbox name='pmr_cardio_vascular_myocardio_disease' data-parent="pmr_cardio_vascular"
                    label="Myocardio Disease" :checked="$consultation->pmr_cardio_vascular_myocardio_disease" />
            </div>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_cardio_vascular_valvulopathies' data-parent="pmr_cardio_vascular"
                    label="Valvulopathies" :checked="$consultation->pmr_cardio_vascular_valvulopathies" />
            </div>
        </td>
        <td class="text-right">
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_drugs' class="data_parent" label="Drugs" :checked="$consultation->pmr_drugs" />
            </div>
        </td>
        <td>
            <div class="tw-mb-2">
                <x-form.checkbox name='pmr_drug_amphetamin' data-parent="pmr_drugs" label="Amphetamin"
                    :checked="$consultation->pmr_drug_amphetamin" />
            </div>
            <div class="tw-mb-2">
                <x-form.checkbox name='pmr_drug_methamphetamine' data-parent="pmr_drugs" label="Methamphetamine"
                    :checked="$consultation->pmr_drug_methamphetamine" />
            </div>
            <div class="tw-mb-2">
                <x-form.checkbox name='pmr_drug_morphin' data-parent="pmr_drugs" label="Morphin"
                    :checked="$consultation->pmr_drug_morphin" />
            </div>
            <div class="tw-mb-1">
                <x-form.checkbox name='pmr_drug_other' data-parent="pmr_drugs" label="Other"
                    :checked="$consultation->pmr_drug_other" />
            </div>
        </td>
    </tr>

    {{-- Drink --}}
    <tr>
        <td rowspan="3">
            <x-form.checkbox name='pmr_drinking' class="data_parent" label="Drinking" :checked="$consultation->pmr_drinking" />
        </td>
        <td class="text-right">How long?</td>
        <td>
            <x-bss-form.input name='pmr_drinking_how_long' data-parent="pmr_drinking"
                value="{{ $consultation->pmr_drinking_how_long }}" />
        </td>
        <td></td>
    </tr>
    <tr>
        <td class="text-right">What kind?</td>
        <td>
            <x-bss-form.input name='pmr_drinking_what_kind' data-parent="pmr_drinking"
                value="{{ $consultation->pmr_drinking_what_kind }}" />
        </td>
        <td></td>
    </tr>
    <tr>
        <td class="text-right">How many?</td>
        <td>
            <x-bss-form.input name="pmr_drinking_how_many" data-parent="pmr_drinking"
                value="{{ $consultation->pmr_drinking_how_many }}" />
        </td>
        <td></td>
    </tr>

    {{-- Operation --}}
    <tr>
        <td rowspan="2">
            <x-form.checkbox name="pmr_operation" class="data_parent" label="Operation" :checked="$consultation->pmr_operation" />
        </td>
        <td class="text-right">At age</td>
        <td>
            <x-bss-form.input name="pmr_operation_at_age" data-parent="pmr_operation"
                value="{{ $consultation->pmr_operation_at_age }}" />
        </td>
        <td></td>
    </tr>
    <tr>
        <td class="text-right">What kind?</td>
        <td>
            <x-bss-form.input name="pmr_operation_what_kind" data-parent="pmr_operation"
                value="{{ $consultation->pmr_operation_what_kind }}" />
        </td>
        <td></td>
    </tr>

    {{-- Smoking --}}
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name="pmr_smoking" class="data_parent" label="Smoking" :checked="$consultation->pmr_smoking" />
            </div>
        </td>
        <td class="text-right">How many?</td>
        <td>
            <x-bss-form.input name="pmr_smoking_how_many" data-parent="pmr_smoking"
                value="{{ $consultation->pmr_smoking_how_many }}" />
        </td>
        <td></td>
    </tr>

    {{-- Other --}}
    <tr>
        <td>
            <x-form.checkbox name="pmr_other" class="data_parent" label="Other" :checked="$consultation->pmr_other" />
        </td>
        <td>
            <x-bss-form.textarea name="pmr_other_input" placeholder="If others, please tell more." data-parent="pmr_other">{{
                $consultation->pmr_other_input }}</x-bss-form.textarea>
        </td>
        <td>
            <x-form.checkbox name="pmr_medication" class="data_parent" label="Medication"
                checked="{{ $consultation->pmr_medication }}" />
        </td>
        <td>
            <x-bss-form.textarea name="pmr_medication_input" placeholder="Please list the medicals." data-parent="pmr_medication">
                {{ $consultation->pmr_medication_input }}</x-bss-form.textarea>
        </td>
    </tr>

    {{-- Childhood & Development History --}}
    <tr>
        <td class="text-right">Childhood & Development History</td>
        <td>
            <x-bss-form.textarea name="pmr_childhood_development_history">{{ $consultation->pmr_childhood_development_history }}
            </x-bss-form.textarea>
        </td>
        <td class="text-right">Mental Illness History</td>
        <td>
            <x-bss-form.textarea name="pmr_mental_illess_history">{{ $consultation->pmr_mental_illess_history }}
            </x-bss-form.textarea>
        </td>
    </tr>

    {{-- Family History --}}
    <tr>
        <td class="text-right">Family History</td>
        <td>
            <x-bss-form.textarea name="pmr_childhood_development_history">{{ $consultation->pmr_childhood_development_history }}
            </x-bss-form.textarea>
        </td>
        <td></td>
        <td></td>
    </tr>
</table>