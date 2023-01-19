<script>
    function init_select2 () {
        $('.table-unit select').each((_i, e) => {
            $(e).select2({
                dropdownAutoWidth: !0,
                width: "100%",
                dropdownParent: $(e).parent()
            });
        });
    }

    $(document).ready(function() {
        init_select2();
        $(document).on('click', '.btn-add-package', function() {
            $('.table-unit').append($('#sample_unit').html());
            init_select2();
        });
    });
</script>