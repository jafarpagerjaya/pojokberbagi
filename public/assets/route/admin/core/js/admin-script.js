const aDisabledList = document.querySelectorAll('a.disabled');
aDisabledList.forEach(aDisabled => {
    aDisabled.addEventListener('click', function(e) {
        e.preventDefault();
    });
});

const counterTarget = document.querySelectorAll('.counter-card'),
      counterSpeed = 2000;

counterUpSup(counterTarget, counterSpeed);

let toastRun = document.querySelector('.toast[data-toast="feedback"][data-toast-run="true"]');
if (toastRun != null) {
    $('.toast[data-toast="feedback"]').toast('show');
    let elTarget = $('.toast[data-toast="feedback"] .toast-body .font-weight-bold'),
        id_bantuan = elTarget.data('id-bantuan');
    
    $('table tbody>tr>th a[data-id="'+id_bantuan+'"').parents('tr').addClass('highlight');

    setTimeout(() => {
        $('table tbody>tr.highlight').removeClass('highlight');
    }, 3100);
}

let tableWidthSetter = function() {
    if ($('table thead').width() > $('table').parent().width()) {
        $('table').addClass('table-responsive');
    } else {
        if ($('table').hasClass('table-responsive')) {
            $('table').removeClass('table-responsive');
        }
    }
}

let resizeTimeoutTable;
$(window).resize(function () {
    clearTimeout(resizeTimeoutTable)
    resizeTimeoutTable = setTimeout(function () {
        if ($('table').length) {
            tableWidthSetter();
        }
    }, 50);
});