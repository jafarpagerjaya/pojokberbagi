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

const pilihanListValue = [...document.querySelectorAll('.pilihan-donasi label')].map(label => {
    return {
        'name': label.getAttribute('for'),
        'value': price_to_number(label.innerText),
        'terbilang': terbilang(price_to_number(label.innerText)).trim()
    }
});

pilihanListValue.forEach(el => {
    document.querySelector('label[for="'+el.name+'"]').setAttribute('data-terbilang', el.terbilang);
});

const pilihanDonasi = document.querySelectorAll('[name="pilihan_donasi"]');
pilihanDonasi.forEach(radio => {
    radio.addEventListener('change', function(e) {
        const selectedDonate = pilihanListValue.find(key => {
            return key.name === e.target.id;
        })
        nominalDonasi.value = formatTSparator(selectedDonate.value, 'Rp. ');
    })
});

let textarea = document.querySelector(".textarea");

textarea.addEventListener('input', autoResize, false);

let nominalDonasi = document.getElementById('floatingInputDonasi'),
    oldValueNominalDonasi;

nominalDonasi.addEventListener('keydown', function (e) {
    if (e.key == 0 && !this.value.length) {
        e.preventDefault();
    }
    let prefix = 'Rp. ';
    if (e.code == "ArrowUp" || e.target.selectionStart == 0 && e.target.selectionStart != e.target.selectionEnd && e.code == "ArrowLeft" || e.code == "ArrowLeft" && e.target.selectionStart == prefix.length || e.code == "Home") {
        e.target.selectionStart = prefix.length;
        e.target.selectionEnd = prefix.length;
        e.preventDefault();
        return false;
    }
    if (e.code == "Delete" || e.code == "Backspace") {
        oldValueNominalDonasi = this.value;
        if (e.target.selectionStart <= prefix.length && e.target.selectionStart == e.target.selectionEnd && e.code == "Backspace") {
            e.target.selectionStart = prefix.length;
            e.target.selectionEnd = prefix.length;
            e.preventDefault();
            return false;
        }
    }

    if (price_to_number(this.value) >= maxDonasi) {
        e.preventDefault();
        return false;
    }
});

nominalDonasi.addEventListener('keypress', preventNonNumbersInInput);

nominalDonasi.addEventListener('keyup', function (e) {
    let ceret = e.target.selectionStart,
        numberTPArray = formatTSparator(this.value, 'Rp. ', e),
        value = numberTPArray[0],
        sisa = numberTPArray[1],
        ribuan = numberTPArray[2],
        prefix = numberTPArray[3];

    this.value = value;

    // Checker radio Pilihan Donasi
    const selectedRadio = pilihanListValue.find(key => {
        return key.value === price_to_number(this.value);
    });

    if (selectedRadio == undefined) {
        if (document.querySelector('[name="pilihan_donasi"]:checked')) {
            document.querySelector('[name="pilihan_donasi"]:checked').checked = false;
        }
    } else {
        document.querySelector('#'+selectedRadio.name).checked = true;
    }

    if (price_to_number(this.value) >= maxDonasi && e.key >= 0) {
        this.value = formatTSparator(maxDonasi, 'Rp. ');
        return false;
    }

    if (e.code.match('Digit')) {
        if (ribuan != null) {
            if ((sisa == 1 && ceret + sisa > value.length - 3) || (sisa == 1 && ceret != prefix.length + 1 && ceret != value.length - prefix.length)) {
                ceret++;
            }
            e.target.selectionStart = ceret;
            e.target.selectionEnd = ceret;
        }
    }

    if (e.code == "Delete") {
        if (ribuan != null) {
            if (sisa == 0 && ceret != prefix.length && ceret != this.value.length && ceret != this.value.length - 1 || sisa == 0 && ceret >= this.value.length - 3 && ceret > prefix.length) {
                ceret --;
            }
            if (oldValueNominalDonasi == this.value) {
                if (sisa == 0) {
                    ceret += 2;
                } else if (sisa == 2) {
                    ceret++;
                } else {
                    ceret++;
                }
                this.value = formatTSparator(removeByIndex(this.value, ceret), prefix);
                if (sisa == 1) {
                    ceret--;
                }
            }
            e.target.selectionStart = ceret;
            e.target.selectionEnd = ceret;
        }
    }

    if (e.code == "Backspace") {
        if (ceret <= prefix.length && ribuan == null || ribuan != null && sisa == 0 && ceret == prefix.length) {
            e.target.selectionStart = ceret;
            e.target.selectionEnd = ceret;
        }
        if (ribuan != null && ceret > prefix.length) {
            if (sisa == 0 && oldValueNominalDonasi != this.value) {
                ceret--;
            }
            if (oldValueNominalDonasi == this.value) {
                this.value = formatTSparator(removeByIndex(this.value, --ceret), prefix);
                if (sisa == 1 && ceret > prefix.length + 1) {
                    ceret--;
                }
            }
            e.target.selectionStart = ceret;
            e.target.selectionEnd = ceret;
        }
    }
});

nominalDonasi.addEventListener('click', function(e) {
    let prefix = 'Rp. ';
    if (this.value.length && e.target.selectionStart <= prefix.length) {
        e.target.selectionStart = prefix.length;
    }
});

let min = 10000,
    max = 10000000;

if (nominalDonasi.getAttribute('min') != null) {
    min = nominalDonasi.getAttribute('min');
}

if (nominalDonasi.getAttribute('max') != null) {
    max = nominalDonasi.getAttribute('max');
}

const minDonasi = min,
      maxDonasi = max;

nominalDonasi.addEventListener('change', function (e) {
    if (this.value.length < 1) {
        return false;
    }

    if (price_to_number(this.value) < minDonasi) {
        this.value = minDonasi;
    } else if (price_to_number(this.value) > maxDonasi) {
        this.value = maxDonasi;
    } else {
        this.value = price_to_number(this.value);
    }
    this.value = formatTSparator(this.value, 'Rp. ');
});

function formatChannelPayment(cp) {
    if (cp.loading) {
        return cp.text;
    }
    let $cp;
    if (cp.path_gambar == null || cp.path_gambar == undefined) {
        $cp = '<div class="font-weight-bolder">'+ cp.text +'</div>'
    } else {
        $cp = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="fw-bold">' + cp.text + '</span></div><div class="col-auto p-0 d-flex align-items-center"><img src="'+ cp.path_gambar +'" alt="'+ cp.text +'" class="img-fluid"></div></div>'
    }
    return $cp;
};

function formatSelectedChannelPayment(cp) {
    if (cp.loading) {
        return cp.text;
    }

    let $cp;
    if (cp.path_gambar == null || cp.path_gambar == undefined) {
        $cp = '<div class="font-weight-bolder">'+ cp.text +'</div>'
    } else {
        $cp = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="fw-bold">' + cp.text + '</span><span class="badge bg-secondary ms-2 fw-light">'+ cp.jenis +'</span></div><div class="col-auto p-0 d-flex align-items-center"><img src="'+ cp.path_gambar +'" alt="'+ cp.text +'" class="img-fluid"></div></div>'
    }
    return $cp;
};

function selectOtionGroupLabel(array) {
    return Object.values(array.reduce((accu, { id_cp: id, jenis: text, nama, path_gambar }) => {
        (accu[text] ??= { text: keteranganJenisChannelPayment(text), children: [] }).children.push({ id, text: nama, path_gambar, jenis: keteranganJenisChannelPayment(text) });
        return accu;
    }, {}));
}

// Select2 for Channel Payment
function modelMatcher(params, data) {
    data.parentText = data.parentText || "";

    // Always return the object if there is nothing to compare
    if ($.trim(params.term) === '') {
        return data;
    }

    // Do a recursive check for options with children
    if (data.children && data.children.length > 0) {
        // Clone the data object if there are children
        // This is required as we modify the object to remove any non-matches
        var match = $.extend(true, {}, data);

        // Check each child of the option
        for (var c = data.children.length - 1; c >= 0; c--) {
            var child = data.children[c];
            child.parentText += data.parentText + " " + data.text;

            var matches = modelMatcher(params, child);

            // If there wasn't a match, remove the object in the array
            if (matches == null) {
                match.children.splice(c, 1);
            }
        }

        // If any children matched, return the new object
        if (match.children.length > 0) {
            return match;
        }

        // If there were no matching children, check just the plain object
        return modelMatcher(params, match);
    }

    // If the typed-in term matches the text of this term, or the text from any
    // parent term, then it's a match.
    var original = (data.parentText + ' ' + data.text).toUpperCase();
    var term = params.term.toUpperCase();

    // Check if the text contains the term
    if (original.indexOf(term) > -1) {
        return data;
    }

    // If it doesn't contain the term, don't return anything
    return null;
}

fetch('/donasi/buat/get/channel-payment', {
    method: "POST",
    cache: "no-cache",
    mode: "same-origin",
    credentials: "same-origin",
    headers: {
        "Content-Type": "application/json",
    },
    referrer: "no-referrer",
    body:JSON.stringify({'token': document.querySelector('[name="token"]').value})
})
.then(response => response.json())
.then(function(result) {
    document.querySelector('[name="token"]').value = result.token;
    fetchTokenChannel.postMessage({
        token: result.token
    });

    if (result.error == false) {
        // Success
        let data = result.feedback.data;
        // console.log(selectOtionGroupLabel(data));
        if (data.length > 0) {
            $('.selectpicker').select2({
                placeholder: "Pilih Metode Pembayaran",
                escapeMarkup: function (markup) { return markup; },
                data: selectOtionGroupLabel(data),
                templateResult: formatChannelPayment,
                templateSelection: formatSelectedChannelPayment,
                matcher: modelMatcher,
                language: {
                    inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, maximumSelected: function (e) { return 'Maksimum petugas pencairan terpilih adalah ' + e.maximum + ' orang'; },
                }
            });
        }
    } else {
        // Failed
        console.log('there is some error in server side');
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast('show');
    }
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

const notifikasiModalEl = document.getElementById('notifikasi');
// Modal BS
if (notifikasiModalEl != null) {
    var myModal = new bootstrap.Modal(notifikasiModalEl, {
        backdrop: 'static', 
        keyboard: false
    });
    setTimeout(()=>{
        myModal.toggle();
    }, 500);
}