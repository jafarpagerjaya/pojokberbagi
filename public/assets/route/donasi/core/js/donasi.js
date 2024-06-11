function formatTSparator(angka, prefix = '', e) {
    var number_string = angka.toString().replace(/[^,\d]/g, ''),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        formed = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        formed += separator + ribuan.join('.');
    }

    formed = split[1] != undefined ? formed + ',' + split[1] : formed;
    return e == undefined ? prefix == undefined ? formed : (formed ? prefix + formed : '') : [prefix == undefined ? formed : (formed ? prefix + formed : ''), sisa, ribuan, prefix];
}

function removeByIndex(str,index) {
    return str.slice(0,index) + str.slice(index+1);
}

function price_to_number(v) {
    if (!v) {
        return 0;
    }
    v = v.split('.').join('');
    v = v.split(',').join('.');
    return parseInt(Number(v.replace(/[^0-9.]/g, "")));
}

window.onload = function () {
    window.setTimeout(fadeout, 500);
}

function fadeout() {
    document.querySelector('.preloader').style.opacity = '0';
    document.querySelector('.preloader').style.display = 'none';
}

function autoResize() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
}

function setCookie(key, value, expiry, path = null) {
    let expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + (path != null ? ';path=' + path : '') + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function eraseCookie(key, path = null) {
    var keyValue = getCookie(key);
    if (path == null) {
        setCookie(key, keyValue, '0');
    } else {
        setCookie(key, keyValue, '0', path);
    }
}

// Toast Maker
let createNewToast = function(toastParentEl, toastId = null, dataToast = 'feedback', toast = null) {
    if (toastParentEl == null) {
        console.log('Object Toast Parent Cannot be Found');
        return false;
    }

    let bgBox = 'bg-default',
        titleBox = 'Pemberitahuan',
        message = 'data.feedback.message';
        
    if (toast != null) {
        if (toast.error) {
            bgBox = 'bg-danger';
            titleBox = 'Peringatan!';
        } else {
            bgBox = 'bg-success';
            titleBox = 'Informasi';
        }

        message = toast.feedback.message;
    };

    const toastAtributes = {
        'role':'alert',
        'aria-live':'assertive',
        'aria-atomic':'true',
        'data-toast':dataToast,
        'data-delay':5000
    };

    if (toastId != null) {
        toastAtributes.id = toastId;
    }

    const toastContainer = document.createElement('div');
    toastContainer.classList.add('toast', 'w-100', 'fade', 'hide' , 'bg-white');
    setMultipleAttributesonElement(toastContainer, toastAtributes);

    const toastHeader = document.createElement('div');
    toastHeader.classList.add('toast-header', 'd-flex', 'gap-2', 'justify-content-between', 'align-items-center');
    toastContainer.appendChild(toastHeader);

    const toastBox = document.createElement('div');
    toastBox.classList.add('small-box', 'rounded', 'p-2', 'ailgn-items-center', bgBox);
    toastHeader.appendChild(toastBox);

    const toastTitle = document.createElement('strong');
    toastTitle.classList.add('me-auto');
    const toastTitleText = document.createTextNode(titleBox);
    toastTitle.appendChild(toastTitleText);
    toastHeader.appendChild(toastTitle);

    const toastTimePassed = document.createElement('small');
    toastTimePassed.classList.add('text-muted', 'time-passed');
    const toastTimePassedText = document.createTextNode('Baru saja');
    toastTimePassed.appendChild(toastTimePassedText);
    toastHeader.appendChild(toastTimePassed);

    const toastDismissAtributes = {
        'type':'button',
        'class':'btn-close',
        'data-bs-dismiss':'toast',
        'aria-label':'Close'
    };

    const toastDismiss = document.createElement('button');
    setMultipleAttributesonElement(toastDismiss, toastDismissAtributes);
    toastHeader.appendChild(toastDismiss);
    
    const toastBody = document.createElement('div');
    toastBody.classList.add('toast-body');
    toastBody.innerHTML = message;
    toastContainer.appendChild(toastBody);

    toastParentEl.appendChild(toastContainer);

    setTimeout(() => {
        $((toast.id == null ? '':'#'+ toast.id) +'.toast[data-toast="'+ toast.data_toast +'"]').on('hidden.bs.toast', function () {
            let dataToast;
            if (this.getAttribute('id') == null) {
                dataToast = toast.data_toast;
            } else {
                dataToast = toast.id;
            }

            stopPassed(dataToast);
            $(this).remove();
        }).on('shown.bs.toast', function() {
            setTimeout(()=> {
                let toastEl = document.getElementById(toast.id);
                toastPassed(toastEl.querySelector('.time-passed'));
            }, 0);
        });
    },0);
};