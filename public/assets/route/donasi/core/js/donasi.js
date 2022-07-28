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