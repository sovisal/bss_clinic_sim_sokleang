@props([
'row',
'title',
])
<?php $_POST['setting'] = Auth::user()->setting(); ?>
<header>
    <table class="table-header" width="100%">
        <tr>
            <td width="13%">
                <img src="{{ asset('images/site/logo.png') }}" alt="">
            </td>
            <td class="text-center">
                <h2 class="KHMOULLIGHT text-blue">{{ $_POST['setting']->clinic_name_kh }}</h2>
                <h1 class="text-bold text-blue">{{ $_POST['setting']->clinic_name_en }}</h1>
                <div>{!! $_POST['setting']->description !!}</div>
            </td>
            <td width="13%">
                <img src="{{ asset('images/site/logo-2.png') }}" alt="">
            </td>
        </tr>
    </table>
    <table class="table-info" width="100%">
        <tr>
            <td colspan="6" class="text-center">
                <h2>{{ $title }}</h2>
            </td>
        </tr>
        <tr>
            <td width="15%"><b>កាលបរិច្ឆេទ/Date</b></td>
            <td width="25%">: {{ date('d/m/Y', strtotime($row->requested_at)) }}</td>
            <td width="10%"><b>PatientID</b></td>
            <td width="17%">: PT-{{ str_pad($row->patient_id, 6, '0', STR_PAD_LEFT) }}</td>
            <td width="13%"><b>លេខកូដ/Code</b></td>
            <td width="20%">: {{ $row->code }}</td>
        </tr>
        <tr>
            <td width="15%"><b>ឈ្មោះ/Name</b></td>
            <td width="25%">: {{ d_obj($row, 'patient', ['name_en', 'name_kh']) }}</td>
            <td width="10%"><b>អាយុ/Age</b></td>
            <td width="17%">: {{ $row->age }}</td>
            <td width="13%"><b>ភេទ/Sex</b></td>
            <td width="20%">: {{ d_obj($row, 'gender', ['title_en', 'title_kh']) }}</td>
        </tr>
        {!! $slot !!}
    </table>
</header>