const elMSInterval = document.getElementById('msInterval');

if (elMSInterval != null) {
    let msInterval = elMSInterval.getAttribute('data-interval');
    
    if (msInterval < 3600) {
        let timer = new CountDownTimer(msInterval);
        
        timer.onTick(format).start();

        setTimeout(()=> {
            timer.start();
        }, 1000);

        function format(minutes, seconds) {
            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;
            elMSInterval.textContent = '(' + minutes + ':' + seconds + ')';

            if (minutes == 0 && seconds == 0) {
                timer.duration = 0;
            }

            if (minutes < 5) {
                if (!elMSInterval.classList.contains('text-danger')) {
                    elMSInterval.classList.remove('text-warning');
                    elMSInterval.classList.add('text-danger');
                }
            } else if (minutes <= 30 && minutes > 4) {
                if (!elMSInterval.classList.contains('text-warning')) {
                    elMSInterval.classList.remove('text-success');
                    elMSInterval.classList.add('text-warning');
                }
            } else {
                if (!elMSInterval.classList.contains('text-secondary')) {
                    elMSInterval.classList.add('text-secondary');
                }
            }
        }
    }
}