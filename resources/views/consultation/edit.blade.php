<x-app-layout>
    <x-slot name="css">
        <style>
        </style>
    </x-slot>
    <x-slot name="js">
        <script>
            function initialize_select2_ajx () {
                $('.table-medicine select[name="medicine_id[]"]').each((_i, e) => {
                    $_this = $(e);
                    $(e).select2({
                        ajax: {
                            url: $_this.data('url'),
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                var query = {
                                    _type : 'query',
                                    term: params.term,
                                    qty_remain: true
                                }
                                return query;
                            },
                            processResults: function (data) {
                                if (!window.stock_inventory && data.results.length == 0 && $('.select2-search__field').val() != '') {
                                    $('.select2-search__field').keyup(function(e){
                                        if (e.keyCode === 13) {
                                            let select_search = $(this);
                                            if (select_search.val()) {
                                                $.ajax({
                                                    url: window.route_medicine,
                                                    type: "POST",
                                                    data: {
                                                        name: select_search.val(),
                                                        price: "1",
                                                        usage_id: "1",
                                                    },
                                                    dataType: "JSON",
                                                    success: function (data) {
                                                        if (data.id) {
                                                            let newOption = new Option(
                                                                select_search.val(),
                                                                data.id,
                                                                false,
                                                                false
                                                            );
                                                            $('select[name="medicine_id[]"').append(
                                                                newOption
                                                            );
                                                            $_this.val(data.id).trigger("change");
                                                        }
                                                    },
                                                });
                                            }
                                        }
                                    });
                                }
                                return data;
                            },
                        },
                        width: "100%",
                    });
                });
            }

            // Prescription Request
            $('.table-medicine').append($('#sample_prescription').html());
            initialize_select2_ajx();

            $(document).on('click', '.btn-add-medicine', function () {
                $('.table-medicine').append($('#sample_prescription').html());
                initialize_select2_ajx();
            });

            $(document).on('change', '[name="qty[]"], [name="upd[]"], [name="nod[]"]', function () {
                $this_row = $(this).parents('tr');
                $total = 	bss_number($this_row.find('[name="qty[]"]').val()) * 
                            bss_number($this_row.find('[name="upd[]"]').val()) * 
                            bss_number($this_row.find('[name="nod[]"]').val());

                $this_row.find('[name="total[]"]').val(bss_number($total));
            });

            // Labor
            $('.labor_row').hide();
            $(document).on('change', '.btnCheckRow', function () {
                $this_row = $(this).parents('tr.labor_row');
                $this_row.find('input[type="checkbox"]').prop('checked', $(this).prop('checked'));
            });
            $(document).on('change', '#btnShowRow', function () {
                $('.labor_row_' + $(this).val()).show();
                $('.labor_rows_of_' + $(this).val()).show();
            });
            $(document).on('click', '.btnHideRow', function () {
                $this_row = $(this).parents('tr.labor_row');
                $this_row.find('input[type="checkbox"]').prop('checked', false);
                $this_row.hide();
            });

            $(document).ready(function () {
                $(".data_parent").each(function () {
                    if ($(this).is(':checked')) {
                        $('[data-parent="'+ $(this).attr('id') +'"]').removeAttr('disabled');
                    } else {
                        $('[data-parent="'+ $(this).attr('id') +'"]').attr('disabled', 'disabled');
                    }
                });
                $('.data_parent').change(function (){
                    if ($(this).is(':checked')) {
                        $('[data-parent="'+ $(this).attr('id') +'"]').removeAttr('disabled');
                    } else {
                        $('[data-parent="'+ $(this).attr('id') +'"]').attr('disabled', 'disabled');
                    }
                });

            });

            const swalWithBootstrapBtns = Swal.mixin({
                customClass: {
                    cancelButton: "btn btn-danger tw-mr-1",
                    confirmButton: "btn btn-primary tw-ml-1",
                },
                buttonsStyling: false,
            });

            function formValidate(target = 'form') {
                var rs = true;
                $(target +" input,"+ target +" textarea,"+ target +" checkbox,"+ target +" radio,"+ target +" select").each(function () {
                    var attr = $(this).attr('required');
                    if ((typeof attr !== 'undefined' && attr !== false) && $(this).val() == '') {
                        var id = $(this).closest('.tab-pane').attr('aria-labelledby');
                        $('#'+ id +' i').remove();
                        $('#'+ id).click().append('<i class="bx bx-error-circle text-danger animate__animated animate__rubberBand"></i>');
                        rs = false;
                    }
                });
                return rs;
            }

            $(document).on('change', '#evaluation_category', function () {
                $('#evaluation_indication').html('');
                $id_category = $(this).val();

                $.ajax({
                    url: "/patient/consultation/get_indication/" + $id_category,
                    type: 'get',
                    success: function(rs){
                        let obj = JSON.parse(rs);
                        for (i in obj) {
                            let newOption = new Option(name = obj[i], i, true, true);
                            $('#evaluation_indication').append(newOption);
                        }
                    },
                    error: function (rs) {
                        // pageLoading('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            confirmButtonText: 'Confirm',
                            timer: 1500
                        });
                    }
                });
            });

            function refresh_treament_plan_label (patient_id) {
                $.ajax({
                    url: "/patient/consultation/treament_plan_label/" + patient_id,
                    type: 'get',
                    success: function(rs){
                        let obj = JSON.parse(rs);
                        $('#link_prescription').html(obj['list_prescription']);
                        $('#link_labor').html(obj['list_labor']);
                        $('#link_xray').html(obj['list_xray']);
                        $('#link_echo').html(obj['list_echo']);
                        $('#link_ecg').html(obj['list_ecg']);
                    },
                    error: function (rs) {
                        // pageLoading('hide');
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                            confirmButtonText: 'Confirm',
                            timer: 1500
                        });
                    }
                });
            }
            refresh_treament_plan_label("{{ $consultation->patient_id }}");

            $(document).on('submit', '#form_prescription', function (evt) {
                $('[name^="time_usage_"]').each(function (i, e) {
                    if (!$(e).prop('checked')) {
                        $(e).val('OFF').prop('checked', true);
                    }
                });
            })

            $('.btn-submit').click(function (){
                var value = $(this).val();
                $('[name="submit_option"]').val(value);
                if (value=="cancel"){
                    swalWithBootstrapBtns.fire({
                        title: "Your data is not saved yet!",
                        text: "Are you sure you want to leave this page?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: "Leave",
                        cancelButtonText: "Stay",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#consultation-form').submit();
                        }
                    })
                }else{
                    if (formValidate('#consultation-form')) {
                        $('#consultation-form').submit();
                    }
                }
            });

            // Remember tab of treament plan
            $(document).ready(function () {
                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("href");
                    localStorage.setItem("treament_plan_tab", target);
                });

                var target = localStorage.getItem("treament_plan_tab");
                if (target) {
                    $('[href="' + target + '"]').tab('show');
                }
            });

            // Treatment plan, when medicine changed, ajax to get unit list
            $(document).on('change', '[name="medicine_id[]"]', function () {
                const $this_row = $(this).closest('tr');
                $this_row.find('[name="unit_id[]"]').html('<option value="">---- None ----</option>');
                if ($(this).val() != '') {
                    $.ajax({
                        url: "{{ route('inventory.product.getUnit') }}",
                        type: "post",
                        data: {
                            id: bss_number($(this).val()),
                        },
                        success: function (rs) {
                            if (rs.success) {
                                $this_row.find('[name="unit_id[]"]').html(rs.options);
                            }
                        },
                        error: function (rs) {
                            flashMsg("danger", 'Error', rs.message)
                        },
                    })
                }
            });

            $("#consultation-form").trackChanges();
            $("#treatment_modal_xray, #treatment_modal_ecg, #treatment_modal_echo, #treatment_modal_labor, #treatment_modal_prescriotion").on('shown.bs.modal', function(){
                $('[name="temp_save"]').val('');
                if ($("#consultation-form").isChanged()) {
                    $(this).modal('hide');
                    swalWithBootstrapBtns.fire({
                        title: "Your data is not saved yet!",
                        text: "Please, save your data before create treatment plan.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: "Save",
                        cancelButtonText: "Cancel",
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            if (formValidate('#consultation-form')) {
                                $('[name="temp_save"]').val('save');
                                $('#consultation-form').submit();
                            }
                        }
                    })
                }
            });
        </script>
    </x-slot>
    <form id="consultation-form" action="{{ route('patient.consultation.update', $consultation->id) }}" method="post">
        @csrf
        @method('PUT')
        <input type="hidden" name="temp_save" value="" />
        <input type="hidden" name="submit_option" value="1" />
        <x-card headerClass="" footerClass="" bodyClass="">
            <x-slot name="header">
                <h4>Consultation</h4>
            </x-slot>
            <x-slot name="action">
                <div>
                    <x-form.button class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>
            <x-slot name="footer">
                <div>
                    <x-form.button class="btn-submit" value="1" icon="bx bx-save" label="Save" />
                </div>
            </x-slot>
            <table class="table-form">
                <tr>
                    <td width="20%" class="text-right">Patient <small class='required'>*</small></td>
                    <td width="30%">
                        <x-bss-form.select name="patient_id" :select2="false" readonly required>
                            <option value="{{ $consultation->patient_id }}" selected>{{ render_synonyms_name($consultation->patient->name_en, $consultation->patient->name_kh) }}</option>
                            </x-form.select2>
                    </td>
                    <td width="20%" class="text-right">Payment Type</td>
                    <td>
                        <x-bss-form.select name="payment_type" data-no_search="true">
                            @foreach ($payment_types as $id => $payment_type)
                            <option value="{{ $id }}" {{ ($consultation->payment_type == $id)? 'selected' : '' }}>{{ $payment_type }}</option>
                            @endforeach
                        </x-bss-form.select>
                    </td>
                </tr>
                <tr>
                    <td class="text-right">Doctor <small class='required'>*</small></td>
                    <td>
                        <x-bss-form.select name="doctor_id">
                            @foreach ($doctors as $doctor)
                            <option value="{{ $doctor->id }}" {{ ($consultation->doctor_id == $doctor->id)? 'selected' : '' }}>{{
                                render_synonyms_name($doctor->name_en, $doctor->name_kh) }}</option>
                            @endforeach
                        </x-bss-form.select>
                    </td>
                    <td class="text-right">Evaluate at <small class='required'>*</small></td>
                    <td>
                        <x-bss-form.input name='evaluated_at' class="date-time-picker" hasIcon="right" icon="bx bx-calendar"
                            value="{{ $consultation->evaluated_at }}" />
                    </td>
                </tr>
            </table>

            <ul class="nav nav-tabs mt-2 mb-0" role="tablist">
                <li class="nav-item">
                    <a class="nav-link btn-sm active" id="vital-sign-tab" data-toggle="tab" href="#vital-sign" aria-controls="vital-sign" role="tab"
                        aria-selected="true">
                        <span class="align-middle">Vital Sign</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-sm" id="past-medical-record-tab" data-toggle="tab" href="#past-medical-record"
                        aria-controls="past-medical-record" role="tab" aria-selected="false">
                        <span class="align-middle">Past medical Record</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-sm" id="examination-tab" data-toggle="tab" href="#examination" aria-controls="examination" role="tab"
                        aria-selected="false">
                        <span class="align-middle">Examination</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-sm" id="evaluation-tab" data-toggle="tab" href="#evaluation" aria-controls="evaluation" role="tab"
                        aria-selected="false">
                        <span class="align-middle">Evaluation</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn-sm" id="treatment-plan-tab" data-toggle="tab" href="#treatment-plan" aria-controls="treatment-plan"
                        role="tab" aria-selected="false">
                        <span class="align-middle">Treament Plan</span>
                    </a>
                </li>
            </ul>
            <div class="tab-content pl-0">
                <div class="tab-pane active" id="vital-sign" aria-labelledby="vital-sign-tab" role="tabpanel">
                    @include('consultation.tabs.vital_sign')
                </div>
                <div class="tab-pane" id="past-medical-record" aria-labelledby="past-medical-record-tab" role="tabpanel">
                    @include('consultation.tabs.past_medical_record')
                </div>
                <div class="tab-pane" id="examination" aria-labelledby="examination-tab" role="tabpanel">
                    @include('consultation.tabs.examination')
                </div>
                <div class="tab-pane" id="evaluation" aria-labelledby="evaluation-tab" role="tabpanel">
                    @include('consultation.tabs.evaluation')
                </div>
                <div class="tab-pane" id="treatment-plan" aria-labelledby="treatment-plan-tab" role="tabpanel">
                    @include('consultation.tabs.treament_plan')
                </div>
            </div>
        </x-card>
    </form>

    @include('consultation.sub_form.echo')
    @include('consultation.sub_form.ecg')
    @include('consultation.sub_form.xray')
    @include('consultation.sub_form.prescription')
    @include('consultation.sub_form.labor')

    <x-para-clinic.modal-detail title="Detail" :foot="false" />
</x-app-layout>