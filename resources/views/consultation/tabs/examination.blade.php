
<table class="table-form striped">
    <tr>
        <th colspan="4" class="tw-bg-gray-100">General Appear</th>
    </tr>
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_good' label="Good" :checked="$consultation->examination_good" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_not_good' label="Not Good" :checked="$consultation->examination_not_good" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_serious' label="Serious" :checked="$consultation->examination_serious" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_too_serious' label="Too Serious"
                    :checked="$consultation->examination_too_serious" />
            </div>
        </td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Neurological System</th>
    </tr>
    <tr>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_consciousness' label="Consciousness"
                    :checked="$consultation->examination_consciousness" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_fantasy' label="Fantasy" :checked="$consultation->examination_fantasy" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_unconscious' label="Unconscious"
                    :checked="$consultation->examination_unconscious" />
            </div>
        </td>
        <td>
            <div class="tw-mb-1">
                <x-form.checkbox name='examination_seizures' label="Seizures" :checked="$consultation->examination_seizures" />
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="4" class="text-center">Mental Status</td>
    </tr>
    <tr>
        <td class="text-right">Speech</td>
        <td>
            <x-bss-form.textarea name="examination_speech">{{ $consultation->examination_speech }}</x-bss-form.textarea>
        </td>
        <td class="text-right">Mood and effect</td>
        <td>
            <x-bss-form.textarea name="examination_mood_and_effect">{{ $consultation->examination_mood_and_effect }}
            </x-bss-form.textarea>
        </td>
    </tr>
    <tr>
        <td class="text-right">Thought</td>
        <td>
            <x-bss-form.textarea name="examination_thought">{{ $consultation->examination_thought }}</x-bss-form.textarea>
        </td>
        <td class="text-right">Perception</td>
        <td>
            <x-bss-form.textarea name="examination_perception">{{ $consultation->examination_perception }}</x-bss-form.textarea>
        </td>
    </tr>
    <tr>
        <td class="text-right">Insight and Judgment</td>
        <td>
            <x-bss-form.textarea name="examination_insight_and_judgment">{{ $consultation->examination_insight_and_judgment }}
            </x-bss-form.textarea>
        </td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Score de Glasgow</th>
    </tr>
    <tr>
        <td class="text-right">Eyes</td>
        <td>
            <x-bss-form.input name='examination_score_de_glasgow_eyes'
                value="{{ $consultation->examination_score_de_glasgow_eyes }}" />
        </td>
        <td class="text-right">Verbal</td>
        <td>
            <x-bss-form.input name='examination_score_de_glasgow_verbal'
                value="{{ $consultation->examination_score_de_glasgow_verbal }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Motion</td>
        <td>
            <x-bss-form.input name='examination_score_de_glasgow_motion'
                value="{{ $consultation->examination_score_de_glasgow_motion }}" />
        </td>
        <td class="text-right">Percussion</td>
        <td>
            <x-bss-form.input name='examination_score_de_glasgow_percussion'
                value="{{ $consultation->examination_score_de_glasgow_percussion }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Auscultation</td>
        <td>
            <x-bss-form.input name='examination_score_de_glasgow_auscultation'
                value="{{ $consultation->examination_score_de_glasgow_auscultation }}" />
        </td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Cardiovascular System</th>
    </tr>
    <tr>
        <td class="text-right">Inspection</td>
        <td>
            <x-bss-form.input name='examination_cardiovascular_inspection'
                value="{{ $consultation->examination_cardiovascular_inspection }}" />
        </td>
        <td class="text-right">Palpation</td>
        <td>
            <x-bss-form.input name='examination_cardiovascular_palpation'
                value="{{ $consultation->examination_cardiovascular_palpation }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Percussion</td>
        <td>
            <x-bss-form.input name='examination_cardiovascular_percussion'
                value="{{ $consultation->examination_cardiovascular_percussion }}" />
        </td>
        <td class="text-right">Auscultation</td>
        <td>
            <x-bss-form.input name='examination_cardiovascular_auscultation'
                value="{{ $consultation->examination_cardiovascular_auscultation }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_cardiovascular_other">{{ $consultation->examination_cardiovascular_other }}
            </x-bss-form.textarea>
        </td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Eyes</th>
    </tr>
    <tr>
        <td class="text-right">Left</td>
        <td>
            <x-bss-form.input name='examination_eye_left' value="{{ $consultation->examination_eye_left }}" />
        </td>
        <td class="text-right">Right</td>
        <td>
            <x-bss-form.input name='examination_eye_right' value="{{ $consultation->examination_eye_right }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Fondus</td>
        <td>
            <x-bss-form.input name='examination_eye_fondus' value="{{ $consultation->examination_eye_fondus }}" />
        </td>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_eye_other">{{ $consultation->examination_eye_other }}</x-bss-form.textarea>
        </td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Ears</th>
    </tr>
    <tr>
        <td class="text-right">Left</td>
        <td>
            <x-bss-form.input name='examination_ear_left' value="{{ $consultation->examination_ear_left }}" />
        </td>
        <td class="text-right">Right</td>
        <td>
            <x-bss-form.input name='examination_ear_right' value="{{ $consultation->examination_ear_right }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Head</td>
        <td>
            <x-bss-form.input name='examination_ear_head' value="{{ $consultation->examination_ear_head }}" />
        </td>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_ear_other">{{ $consultation->examination_ear_other }}</x-bss-form.textarea>
        </td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Hand</th>
    </tr>
    <tr>
        <td class="text-right">Left</td>
        <td>
            <x-bss-form.input name='examination_hand_left' value="{{ $consultation->examination_hand_left }}" />
        </td>
        <td class="text-right">Right</td>
        <td>
            <x-bss-form.input name='examination_hand_right' value="{{ $consultation->examination_hand_right }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Body</td>
        <td>
            <x-bss-form.input name='examination_body' value="{{ $consultation->examination_body }}" />
        </td>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_hand_other">{{ $consultation->examination_hand_other }}</x-bss-form.textarea>
        </td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Foot</th>
    </tr>
    <tr>
        <td class="text-right">Left</td>
        <td>
            <x-bss-form.input name='examination_foot_left' value="{{ $consultation->examination_foot_left }}" />
        </td>
        <td class="text-right">Right</td>
        <td>
            <x-bss-form.input name='examination_foot_right' value="{{ $consultation->examination_foot_right }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_foot_other">{{ $consultation->examination_foot_other }}</x-bss-form.textarea>
        </td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <th colspan="4" class="tw-bg-gray-100">Other body parts</th>
    </tr>
    <tr>
        <td class="text-right">Nose</td>
        <td>
            <x-bss-form.input name='examination_nose' value="{{ $consultation->examination_nose }}" />
        </td>
        <td class="text-right">pharynxl</td>
        <td>
            <x-bss-form.input name='examination_pharynxl' value="{{ $consultation->examination_pharynxl }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Neck</td>
        <td>
            <x-bss-form.input name='examination_nech' value="{{ $consultation->examination_nech }}" />
        </td>
        <td class="text-right">Lymphadenopathy</td>
        <td>
            <x-bss-form.input name='examination_lymphadenopathy' value="{{ $consultation->examination_lymphadenopathy }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Geneto-urinary</td>
        <td>
            <x-bss-form.input name='examination_geneto_urinary' value="{{ $consultation->examination_geneto_urinary }}" />
        </td>
        <td class="text-right">Extremities</td>
        <td>
            <x-bss-form.input name='examination_extremities' value="{{ $consultation->examination_extremities }}" />
        </td>
    </tr>
    <tr>
        <td class="text-right">Musculosqueletal</td>
        <td>
            <x-bss-form.input name='examination_musculosqueletal' value="{{ $consultation->examination_musculosqueletal }}" />
        </td>
        <td class="text-right">Other</td>
        <td>
            <x-bss-form.textarea name="examination_other_body_part_other">{{ $consultation->examination_other_body_part_other }}
            </x-bss-form.textarea>
        </td>
    </tr>
</table>