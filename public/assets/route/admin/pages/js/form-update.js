if (jumlahTarget.value.length > 0) {
    inputSatuan.closest('.d-none').classList.remove('d-none');
}

jumlahTarget.addEventListener('change', function(e) {
    let min = this.getAttribute('data-min');
    if (min != null) {
        let value = priceToNumber(this.value);
        if (value < min) {
            value = min;
            this.value = numberToPrice(value);
        }
    }
});

$('select').each(function() {
    if ($(this).data('selected-value') != null) {
        $(this).val($(this).data('selected-value')).trigger('change');
        delete nameNew[$(this).attr('name')];
    }
});

charLeft.innerHTML = ('<span class="text-orange">'+ textarea.value.length +'</span>' + '/' +255);

const id_bantuan = submit.getAttribute('data-id-bantuan');
nameNew.id_bantuan = id_bantuan;

$('[type="reset"]').click(function (e) {
    setTimeout(() =>{
        $('select').each(function() {
            if ($(this).data('selected-value') != null) {
                $(this).val($(this).data('selected-value')).trigger('change');
                delete nameNew[$(this).attr('name')];
            }
        });
    }, 0);
});

fetchUrl = '/admin/fetch/update/bantuan';