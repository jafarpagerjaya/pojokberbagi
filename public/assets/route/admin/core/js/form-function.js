function restrictNumber() {
    var newValue = this.value.replace(new RegExp(/[^\d]/, 'ig'), "");
    this.value = newValue;
};

function preventNonNumbersInInput(event) {
    let characters = String.fromCharCode(event.which);
    if (!(/[0-9]/.test(characters))) {
        event.preventDefault();
    }
};

function numberToPrice(angka, prefix = '', e) {
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

function priceToNumber(v) {
    if (!v) { return 0; }
    v = v.split('.').join('');
    v = v.split(',').join('.');
    return Number(v.replace(/[^0-9.]/g, ""));
}

function isNumber(n) { return /^[0-9.]+$/.test(n); }

function removeByIndex(str, index) {
    return str.slice(0, index) + str.slice(index + 1);
}

String.prototype.replaceAt = function (indexStart, indexEnd, replacement) {
    return this.substring(0, indexStart) + replacement + this.substring(indexEnd);
}

function autoResize() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function escapeRegExp(str, result = ' ', pattern = /[<>]/g) {
    return str.replace(pattern, result);
}

function ucwords(string, trim = true) {
    if (string == null) {
        return '';
    }
    let str = string;
    if (trim) {
        str.trim()
    }
    return str.toLowerCase().replace(/\b[a-z]/g, function (letter) {
        return letter.toUpperCase();
    });
};

function checkEmailChar(char) {
    const pattern = /[^~`!#$%^&*()+={}:;'"<>,/?\[\]|\\\s]/; // not acceptable char

    return pattern.test(char);
}

function checkEmailPattern(str) {
    const pattern = /^([^\.\_\-\@])+([^\.\@\_\-])*((([^\d\@]){0,1})[a-z0-9]{2,}){0,1}((@([a-zA-Z]{2,})+(\.([a-z]{2,})){1,2}|@(\d{3}.){1,3})|(@([0-9]{1,3})+(\.([0-9]{1,3})){3}))$/;

    return pattern.test(str.toLowerCase());
}

const inputMaxlengthList = document.querySelectorAll('input[maxlength], textarea[maxlength]');
inputMaxlengthList.forEach(input => {
    let maxlength = input.getAttribute('maxlength');
    let tag = document.createElement('span');
    tag.classList.add('input-char-left');
    tag.innerHTML = '<span class="current-length text-orange">' + input.value.length + '</span> / <span>' + maxlength + '</span>';
    input.parentElement.appendChild(tag);

    if (input.currentStyle ? input.currentStyle.display : getComputedStyle(input, null).display == 'none') {
        input.parentElement.querySelector('.input-char-left').classList.add('d-none');
    }

    // Event
    input.addEventListener('keyup', function (e) {
        this.parentElement.querySelector('.current-length').innerText = this.value.length;
    });

    input.addEventListener('paste', function (e) {
        setTimeout(() => {
            this.parentElement.querySelector('.current-length').innerText = this.value.length;
        }, 0);
    });
});

$('select').on('change', function () {
    if (this.classList.contains('is-invalid')) {
        this.classList.remove('is-invalid');
    }
    if (this.closest('.form-group').classList.contains('error')) {
        this.closest('.form-group').classList.remove('error');
    }
    if (this.closest('.form-group').classList.contains('is-invalid')) {
        this.closest('.form-group').classList.remove('is-invalid');
    }
});

document.querySelectorAll('.form-label-group.input-group .form-control').forEach(fc => {
    fc.addEventListener('focusin', function () {
        this.closest('.input-group').classList.add('focused');
    });
    fc.addEventListener('focusout', function () {
        this.closest('.input-group').classList.remove('focused');
    });
    fc.parentElement.querySelector('.input-group-append').addEventListener('click', function () {
        fc.focus();
    });
});