
<table class="table-form striped">
    <tr>
        <td>Systolic (mmHg)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_systolic" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_systolic }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        mmHg
                    </span>
                </div>
            </div>
        </td>
        <td>Diastolic (mmHg)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_diastolic" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_diastolic }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        mmHg
                    </span>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>Pulse (/mn)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_pulse" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_pulse }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        /mn
                    </span>
                </div>
            </div>
        </td>
        <td>Breath (/mn)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_breath" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_breath }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        /mn
                    </span>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>Temperature (&deg;C)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_temperature" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_temperature }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        &deg;C
                    </span>
                </div>
            </div>
        </td>
        <td>O2sat (%)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_o2sat" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_o2sat }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        %
                    </span>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>Height (cm)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_height" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_height }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        cm
                    </span>
                </div>
            </div>
        </td>
        <td>Weight (kg)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_weight" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_weight }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        %
                    </span>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td>Glucose (mg/dl)</td>
        <td>
            <div class="input-group">
                <input type="text" name="vital_sign_glucose" class="form-control tw-border-r-0"
                    value="{{ $consultation->vital_sign_glucose }}" />
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white tw-border-l-0">
                        mg/dl
                    </span>
                </div>
            </div>
        </td>
        <td>Chief Complain</td>
        <td>
            <input type="text" name="vital_sign_chief_complain" class="form-control"
                value="{{ $consultation->vital_sign_chief_complain }}" />
        </td>
    </tr>
    <tr>
        <td>History of present illness</td>
        <td>
            <input type="text" name="vital_sign_history_of_illness" class="form-control"
                value="{{ $consultation->vital_sign_history_of_illness }}" />
        </td>
        <td>Current Medication</td>
        <td>
            <input type="text" name="vital_sign_current_medication" class="form-control"
                value="{{ $consultation->vital_sign_current_medication }}" />
        </td>
    </tr>
</table>