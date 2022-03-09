const formControl = document.querySelectorAll('.form-control');
formControl.forEach(el => {
    el.addEventListener('keypress', function(e) {
        let ceret = e.target.selectionStart;
        if (ceret == 0 && e.keyCode == 32) {
            e.preventDefault();
        }
        if (e.keyCode == 32 && e.target.selectionStart != 0 && e.target.value.indexOf(' ') >= 0 && this.value.substring(e.target.selectionStart, e.target.selectionStart-1) == ' ' && e.target.value.charCodeAt(e.target.selectionStart-1) == 32) {
            e.preventDefault();
        }
    });

    el.addEventListener('paste', function(e) {
        setTimeout(()=>{
            if (el.classList.contains('no-space')) {
                if (e.target.value.indexOf(' ') >= 0) {
                    e.target.value = e.target.value.trim().replace(/\s+/g, "");
                }
            }
            if (e.target.value.indexOf('  ') >= 0) {
                let ceret = e.target.selectionStart;
                e.target.value = e.target.value.trim().replace(/\s+/g, " ");
                e.target.selectionEnd = ceret-1;
            }
        }, 0);
    });
});

const noSpace = function(event) {
    if (event.keyCode === 32) {
        event.preventDefault();
    }
};

let inputNoSpace = document.querySelectorAll('.no-space');
inputNoSpace.forEach(el => {
    el.addEventListener('keypress', function(e) {
        noSpace(e);
    });
});

function preventNonNumbersInInput(event){
    let characters = String.fromCharCode(event.which);
    if(!(/[0-9]/.test(characters))){
        event.preventDefault();
    }
};

function restrictNumber () {  
    var newValue = this.value.replace(new RegExp(/[^\d]/,'ig'), "");
    this.value = newValue;
};

let textarea = document.querySelector(".textarea");

textarea.addEventListener('input', autoResize, false);

let nominalDonasi = document.getElementById('floatingInputDonasi');

nominalDonasi.addEventListener('keydown', function (e) {
    if (e.key == 0 && !this.value.length) {
        e.preventDefault();
    }
});

nominalDonasi.addEventListener('keypress', preventNonNumbersInInput);

nominalDonasi.addEventListener('keyup', function (e) {
    this.value = formatTSparator(this.value, 'Rp. ');
});

let min = 2000;

if (nominalDonasi.getAttribute('min').length) {
    min = nominalDonasi.getAttribute('min');
}

const minDonasi = min;

nominalDonasi.addEventListener('change', function (e) {
    if (this.value.length < 1) {
        return false;
    }

    if (price_to_number(this.value) < minDonasi) {
        this.value = minDonasi;
    } else {
        this.value = price_to_number(this.value);
    }
    this.value = formatTSparator(this.value, 'Rp. ');
});

$('.selectpicker').select2({
    placeholder: "Pilih Metode Pembayaran"
});

let fields = {},
    fieldsGlobal = {},
    margedFields = {};
const secret = "Bismilah";

if (getCookie('donasi-pojokberbagi')) {
    let encryptedJSON = CryptoJS.AES.decrypt(getCookie('donasi-pojokberbagi'), secret).toString(CryptoJS.enc.Utf8);
    fields = JSON.parse(encryptedJSON);
}

if (getCookie('donasi-global-pojokberbagi')) {
    let encryptedJSONGlobal = CryptoJS.AES.decrypt(getCookie('donasi-global-pojokberbagi'), secret).toString(CryptoJS.enc.Utf8);
    fieldsGlobal = JSON.parse(encryptedJSONGlobal);
}

if (Object.keys(fields).length || Object.keys(fieldsGlobal).length) {
    Object.assign(margedFields, fields, fieldsGlobal);
    // console.log(margedFields);
}

if (Object.keys(margedFields).length) {
    Object.keys(margedFields).forEach(key => {
        let el = document.querySelector('[name="' + key + '"]');
        if (el.value.length && el.type != 'checkbox') {
            return;
        }
        el.value = margedFields[key];
        if (el.classList.contains('select2-hidden-accessible')) {
            $('[name="' + key + '"]').select2("val", margedFields[key]);
        }
        if (el.type && el.type === 'checkbox') {
            el.checked = el.value;
        }
        fields[key] = margedFields[key];
        if (key == 'email') {
            $.post('/donasi/buat/ajax', {
                email: fields[key] ,
            }, function(response, success) {
                if (success) {
                    aliasDonasi.nextElementSibling.children[0].innerText = ((response.length) ? response : 'Sahabat Berbagi');
                }
            });
        }
    });
}

$('form').on('change', '[name]', function () {
    if ($(this).attr('type') == 'checkbox') {
        if (!$(this).is(':checked')) {
            delete fields[this.name];
            delete fieldsGlobal[this.name];
        }
        $(this).val($(this).is(':checked'));
    }
    if (!$(this).val().length) {
        delete fields[this.name];
    }
    if ($(this).val() != '' && ($(this).attr('type') != 'checkbox') || ($(this).attr('type') == 'checkbox') && $(this).is(':checked')) {
        fields[this.name] = this.value;
        if ($(this).attr('type') == 'checkbox') {
            fieldsGlobal[this.name] = this.value;
        }
    }
    if (this.name == 'metode_pembayaran') {
        fieldsGlobal[this.name] = this.value;
        if (this.closest('.form-floating').classList.contains('text-danger')) {
            this.closest('.form-floating').classList.remove('text-danger');
        }
    }

    let json = JSON.stringify(fields),
        encryptedJSON = CryptoJS.AES.encrypt(json, secret),
        jsonGlobal = JSON.stringify(fieldsGlobal),
        encryptedJSONGlobal = CryptoJS.AES.encrypt(jsonGlobal, secret);

    if (Object.keys(fields).length > 0) {
        setCookie('donasi-pojokberbagi', encryptedJSON, 1, window.location.pathname);
    } else {
        eraseCookie('donasi-pojokberbagi', window.location.pathname);
    }

    if (Object.keys(fieldsGlobal).length > 0) {
        setCookie('donasi-global-pojokberbagi', encryptedJSONGlobal, 1, '/');
    } else {
        eraseCookie('donasi-global-pojokberbagi', '/');
    }
    // console.log(fields, fieldsGlobal);
}).on('mouseenter', 'a', function () {
    // console.log(fields);
});

let emailDonatur = document.getElementById('floatingInputEmail'),
    aliasDonasi = document.getElementById('flexSwitchCheckChecked');
emailDonatur.addEventListener('change', function(e) {
    $.post('/donasi/buat/ajax', {
        email: e.target.value,
    }, function(response, success) {
        if (success) {
            aliasDonasi.nextElementSibling.children[0].innerText = ((response.length) ? response : 'Sahabat Berbagi');
        }
    });
});

let kontakDonatur = document.getElementById('floatingInputKontak');
kontakDonatur.addEventListener('keypress',preventNonNumbersInInput);
// paste also restricted
kontakDonatur.addEventListener('input', restrictNumber);

let charLeft = document.getElementById('charLeft');
const maxChar = textarea.getAttribute('data-max');

charLeft.innerHTML = ('<span class="text-orange">' + textarea.value.length + '</span>/' + maxChar);

textarea.addEventListener("keydown", function (e) {
    if (e.target.value.length >= maxChar) {
        if ((e.key != "Backspace") && (e.key != "Delete") && (e.key != "F5") && (e.key != "ArrowLeft") && (e.key != "ArrowRight") && (e.key != "ArrowUp") && (e.key != "ArrowDown")) {
            e.preventDefault()
        }
    }
});

textarea.addEventListener("keyup", function (e) {
    charLeft.innerHTML = '<span class="text-orange">' + e.target.value.length + '</span>/' + maxChar;
});

textarea.style.height = textarea.scrollHeight + 'px';

let client = undefined;

if (jsonClientAuth != 'null') {
    client = JSON.parse(atob(decodeURIComponent(getCookie('client-pojokberbagi'))));
}

if (client) {
    if (client.auth) {
        authChannelSignin.auth = client.auth;
        authChannelSignin['sender'] = window.location.pathname;
        authChannel.postMessage({
            action: "signin",
            rule: authChannelSignin
        });
    }
}

// const submit = document.querySelector('[type="submit"]:not(.disabled)');

// submit.addEventListener("click", function(e) {
//     if (getCookie('donasi-pojokberbagi')) {
//         eraseCookie('donasi-pojokberbagi', window.location.pathname);
//     }
// });

function isTouchDevice() {
    return (('ontouchstart' in window) || (navigator.maxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0));
}

const myFlag = isTouchDevice();
const textareaInput = document.querySelectorAll('.input_textarea2');

textareaInput.forEach(el => {
    if (myFlag) { 
        el.classList.add('touch');
    } else {
        el.classList.remove('touch');
    }
});

let resizeTimeout = undefined;
window.addEventListener('resize', function onResize() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(()=>{
        let touchS = isTouchDevice();
        textareaInput.forEach(el => {
            if (touchS) { 
                el.classList.add('touch');
            } else {
                el.classList.remove('touch');
            }
        });
    }, 50);
});







