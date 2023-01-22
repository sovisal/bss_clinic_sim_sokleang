<script>
    function initialize_select2_ajx () {
        $('.table-medicine select[name="medicine_id[]"]').each((_i, e) => {
            let $_this = $(e);
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

    $(document).ready(function() {
        <?php if (!$is_edit) { ?>
            $('.table-medicine').append($('#sample_prescription').html());
        <?php } ?>

        $('.table-medicine select').each((_i, e) => {
            $(e).select2({
                dropdownAutoWidth: !0,
                width: "100%",
                dropdownParent: $(e).parent()
            });
        });

        initialize_select2_ajx();
        
        $(document).on('click', '.btn-add-medicine', function() {   
            $('.table-medicine').append($('#sample_prescription').html());
            initialize_select2_ajx();
        });
        $(document).on('change', '[name="qty[]"], [name="upd[]"], [name="nod[]"]', function() {
            $this_row = $(this).parents('tr');
            $total = bss_number($this_row.find('[name="qty[]"]').val()) *
                bss_number($this_row.find('[name="upd[]"]').val()) *
                bss_number($this_row.find('[name="nod[]"]').val());

            $this_row.find('[name="total[]"]').val(bss_number($total));
        });
    });

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

    $(document).on('submit', '#form_prescription', function(evt) {
        $('[name^="time_usage_"]').each(function(i, e) {
            if (!$(e).prop('checked')) {
                $(e).val('OFF').prop('checked', true);
            }
        });
    });
</script>