const elMSInterval = document.getElementById('msInterval');

if (elMSInterval != null) {
    let msInterval = elMSInterval.getAttribute('data-interval'),
    timer = new CountDownTimer(msInterval);

    timer.onTick(format).start();

    setTimeout(function () {
        timer.start();
    }, 1000);

    function format(minutes, seconds) {
        minutes = minutes < 10 ? "0" + minutes : minutes;
        seconds = seconds < 10 ? "0" + seconds : seconds;
        elMSInterval.textContent = '(' + minutes + ':' + seconds + ')';

        if (minutes < 1) {
            if (!elMSInterval.classList.contains('text-danger')) {
                elMSInterval.classList.remove('text-warning');
                elMSInterval.classList.add('text-danger');
            }
        } else if (minutes <= 2 && minutes > 0) {
            if (!elMSInterval.classList.contains('text-warning')) {
                elMSInterval.classList.remove('text-success');
                elMSInterval.classList.add('text-warning');
            }
        } else {
            if (!elMSInterval.classList.contains('text-success')) {
                elMSInterval.classList.add('text-success');
            }
        }
    }

    $('#notif-modal').on('hide.bs.modal', function () {
        timer.duration = 0;
    });    
}