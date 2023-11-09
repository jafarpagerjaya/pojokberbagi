$('#input-jenis-kelamin-donatur').select2({
    'placeholder': 'Pilih salah satu'
});

document.querySelector('[type="reset"]').addEventListener('click', function(e) {
    const lastParams = window.location.href.split('/').at(-1);
    $("select").each(function(){
        if (lastParams == 'formulir') {
            if ($(this).data('selected-value') == null) {
                $(this).val('').trigger('change');
            }
        } else {
            $(this).val($(this).find('option[selected]').val()).trigger('change');
        }
    });
});