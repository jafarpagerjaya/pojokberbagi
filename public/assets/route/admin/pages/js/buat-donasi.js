let ctrl = undefined;

const inputAlias = document.getElementById('input-alias-donasi'),
inputAliasMaxChar = inputAlias.getAttribute('maxlength');
inputAlias.addEventListener('keydown', function(e) {
    let bannedCharFound = /[^a-zA-Z0-9\s]/;
    if (bannedCharFound.test(e.key) === true) {
        e.preventDefault();
        return false;
    }
    if (e.target.value.length >= inputAliasMaxChar) {
        if (e.key == "Control") {
            ctrl = "down";
        }
        if ((e.key != "Backspace") && (e.key != "Delete") && (e.key != "F5") && (e.key != "ArrowLeft") && (e.key != "ArrowRight") && (e.key != "ArrowUp") && (e.key != "ArrowDown") && (ctrl != 'down')) {
            e.preventDefault()
        }
    }
});

inputAlias.addEventListener('keyup', function (e) {
    if (e.key == "Control") {
        ctrl = "up";
    }
});

const inputCheckDoa = document.getElementById('input-check-doa'),
      textareaDoa = document.getElementById('textarea-doa'),
      charLeft = document.getElementById('charLeft');
inputCheckDoa.addEventListener('click', function () {
    if (this.checked) {
        this.parentElement.classList.add('checked');
        textareaDoa.classList.remove('d-none');
        charLeft.classList.remove('d-none');
        textareaDoa.focus();
    } else {
        this.parentElement.classList.remove('checked');
        textareaDoa.classList.add('d-none');
        charLeft.classList.add('d-none');
    }
});

const maxChar = textareaDoa.getAttribute('maxlength');

textareaDoa.addEventListener("keydown", function (e) {
    let bannedCharFound = /[^a-zA-Z0-9\s\/.-]/;
    if (bannedCharFound.test(e.key) === true) {
        e.preventDefault();
        return false;
    }
    let ceretS = e.target.selectionStart;
    if ((((e.code == 'Period' && e.key == '.') || (e.code == 'Slash' && e.key == '/') || (e.code == 'Minus' && e.key == '-')) && (
            this.value.substring(ceretS, ceretS+1) == '.' || 
            this.value.substring(ceretS, ceretS-1) == '.' || 
            this.value.substring(ceretS, ceretS+1) == '/' || 
            this.value.substring(ceretS, ceretS-1) == '/' || 
            this.value.substring(ceretS, ceretS+1) == '-' || 
            this.value.substring(ceretS, ceretS-1) == '-' ||
            (this.value.substring(ceretS, ceretS+1) == ' ' && (this.value.substring(ceretS+1, ceretS+2) == '.' || this.value.substring(ceretS+1, ceretS+2) == '/' || this.value.substring(ceretS+1, ceretS+2) == '-')) ||
            (this.value.substring(ceretS, ceretS-1) == ' ' && (this.value.substring(ceretS-1, ceretS-2) == '.' || this.value.substring(ceretS-1, ceretS-2) == '/' || this.value.substring(ceretS-1, ceretS-2) == '-'))
        ))) {
        e.preventDefault();
        return false;
    }

    if (e.target.value.length >= maxChar) {
        if (e.key == "Control") {
            ctrl = "down";
        }
        if ((e.key != "Backspace") && (e.key != "Delete") && (e.key != "F5") && (e.key != "ArrowLeft") && (e.key != "ArrowRight") && (e.key != "ArrowUp") && (e.key != "ArrowDown") && (ctrl != 'down')) {
            e.preventDefault();
        }
    }
});

textareaDoa.addEventListener('keyup', function (e) {
    charLeft.querySelector('span').innerText = this.value.length;
    if (e.key == "Control") {
        ctrl = "up";
    }
});

textareaDoa.addEventListener('paste', function(e) {
    setTimeout(() => {
        this.value = escapeRegExp(this.value.trim(),'',/[^a-zA-Z0-9\s\/.-]/g);
    }, 0);
});

$('select').select2({
    placeholder: "Pilih salah satu"
});

let fetchData = function(url, data = null, root = null) {
    
};

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

// butuh perbaikan nama url ganti dengan yang cocok
let data = {
    token: document.querySelector('body').getAttribute('data-token')
}

fetch('/admin/fetch/read/channel-payment', {
    method: "POST",
    cache: "no-cache",
    mode: "same-origin",
    credentials: "same-origin",
    headers: {
        "Content-Type": "application/json",
    },
    referrer: "no-referrer",
    body:JSON.stringify(data)
})
.then(response => response.json())
.then(function(result) {
    if (result.error == false) {
        // Success
        let data = result.feedback.data;
        if (data.length > 0) {
            $('#input-cp-donasi').select2({
                placeholder: "Pilih salah satu",
                data: selectLabel(data),
                matcher: modelMatcher,
                escapeMarkup: function (markup) { return markup; },
                templateResult: formatChannelPayment
            });
        }
    } else {
        // Failed
        $('.toast[data-toast="feedback"] .time-passed').text('Baru Saja');
        $('.toast[data-toast="feedback"] .toast-body').html(result.feedback.message);
        $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
        $('.toast[data-toast="feedback"] .toast-header strong').text('Peringatan!');
        console.log('there is some error in server side');
        $('.toast').toast('show');
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    // fetchTokenChannel.postMessage({
    //     token: body.getAttribute('data-token')
    // });
});

function formatChannelPayment(cp) {
    if (cp.loading) {
        return cp.text;
    }
    let $cp;
    if (cp.path_gambar == null || cp.path_gambar == undefined) {
        $cp = '<div class="font-weight-bolder">'+ cp.text +'</div>'
    } else {
        $cp = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bold">' + cp.text + '</span></div><div class="col-1 p-0 d-flex align-items-center"><img src="'+ cp.path_gambar +'" alt="'+ cp.text +'" class="img-fluid"></div></div>'
    }
    return $cp;
};

function formatDataBantuan(bantuan) {
    if (bantuan.loading) {
        return bantuan.text;
    }
    let $bantuan;
    $bantuan = '<div class="d-flex justify-content-between"><div class="font-weight-bold w-80 overflow-hidden">'+ bantuan.text +'</div><div class="text-muted"><span class="badge p-2">'+ statusBantuan(bantuan.status).text +'</span></div></div>';
    return $bantuan;
}

function formatDataDonatur(donatur) {
    if (donatur.loading) {
        return donatur.text;
    }
    let $donatur;
    $donatur = '<div class="d-flex justify-content-between"><div class="font-weight-bold w-80 overflow-hidden">'+ donatur.text +'<p class="m-0">'+ donatur.email +'</p></div><div class="text-muted text-right"><span class="py-2">'+ donatur.samaran +'</span><p class="m-0 text-right">'+ donatur.kontak +'</p></div></div>';
    return $donatur;
}

let dataBantuan = {};
$('#input-bantuan-donasi').select2({
    // minimumInputLength: 1,
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function() { return "Data yang dicari tidak ditemukan"; }, searching: function() { return "Sedang melakukan pencarian..."; }, loadingMore: function() { return "Menampilkan data yang lainnya"; }, },
    placeholder: "Pilih salah satu",
    ajax: {
        url: '/admin/fetch/ajax/bantuan',
        type: 'post',
        dataType: 'json',
        contentType: "application/json",
        data: function (params) {
            if ($('input.select2-search__field').val().length) {
                params.search = $('input.select2-search__field').val();
            }
            delete params.term;
            if (dataBantuan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataBantuan.search))) {
                params.offset = parseInt(dataBantuan.offset) + parseInt(dataBantuan.limit);
            }
            params.offset = params.offset || 0;
            console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            console.log(response);
            if (response.error) {
                console.log(response.feedback.message);
                return false;
            }
            let data = response.feedback.data;
            data = data.map(function (elments) {
                return {
                    id: elments.id_bantuan,
                    text: elments.nama_bantuan,
                    status: elments.status
                };
            });
            if (response.feedback.search != undefined) {
                dataBantuan.search = response.feedback.search;
            } else {
                delete dataBantuan.search;
            }
            dataBantuan.offset = response.feedback.offset;
            dataBantuan.record = response.feedback.record;
            dataBantuan.limit = response.feedback.limit;
            dataBantuan.load_more = response.feedback.load_more;
            let pagination = {
                more: dataBantuan.load_more
            };
            return {results: data, pagination};
            // return {results: data};
        }
    },
    escapeMarkup: function (markup) { return markup; },
    templateResult: formatDataBantuan
}).on('select2:open', function() {
    if (dataBantuan.search != undefined) {
        $('input.select2-search__field').val(dataBantuan.search);
    }
    // console.log(dataBantuan);
    if (!dataBantuan.load_more) {
        dataBantuan.offset = 0;
        if ($(this).hasClass("select2-hidden-accessible")) {
            $('#select2-'+ $(this).attr('id') +'-results').scrollTop(0);
        }
    }
}).on('select2:close', function(e) {
    dataBantuan.load_more = false;
});

let dataDonatur = {};
$('#input-donatur-donasi').select2({
    // minimumInputLength: 1,
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function() { return "Data yang dicari tidak ditemukan"; }, searching: function() { return "Sedang melakukan pencarian..."; }, loadingMore: function() { return "Menampilkan data yang lainnya"; }, },
    placeholder: "Pilih salah satu",
    ajax: {
        url: '/admin/fetch/ajax/donatur',
        type: 'post',
        dataType: 'json',
        contentType: "application/json",
        data: function (params) {
            if ($('input.select2-search__field').val().length) {
                params.search = $('input.select2-search__field').val();
            }
            delete params.term;
            if (dataDonatur.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataDonatur.search))) {
                params.offset = parseInt(dataDonatur.offset) + parseInt(dataDonatur.limit);
            }
            params.offset = params.offset || 0;
            console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            console.log(response);
            if (response.error) {
                console.log(response.feedback.message);
                return false;
            }
            let data = response.feedback.data;
            data = data.map(function (elments) {
                return {
                    id: elments.id_donatur,
                    text: elments.nama_donatur,
                    email: elments.email,
                    kontak: elments.kontak,
                    samaran: elments.samaran
                };
            });
            if (response.feedback.search != undefined) {
                dataDonatur.search = response.feedback.search;
            } else {
                delete dataDonatur.search;
            }
            dataDonatur.offset = response.feedback.offset;
            dataDonatur.record = response.feedback.record;
            dataDonatur.limit = response.feedback.limit;
            dataDonatur.load_more = response.feedback.load_more;
            let pagination = {
                more: dataDonatur.load_more
            };
            return {results: data, pagination};
            // return {results: data};
        }
    },
    escapeMarkup: function (markup) { return markup; },
    templateResult: formatDataDonatur
}).on('select2:open', function() {
    if (dataDonatur.search != undefined) {
        $('input.select2-search__field').val(dataDonatur.search);
    }
    // console.log(dataDonatur);
    if (!dataDonatur.load_more) {
        dataDonatur.offset = 0;
        if ($(this).hasClass("select2-hidden-accessible")) {
            $('#select2-'+ $(this).attr('id') +'-results').scrollTop(0);
        }
    }
}).on('select2:close', function(e) {
    dataDonatur.load_more = false;
});

const waktu_bayar = document.getElementById('waktu-bayar');
waktu_bayar.querySelector('.input-group-append').addEventListener('click', function() {
    waktu_bayar.querySelector('#input-waktu-bayar').focus();
});

// let d = new Date(); d.setDate(d.getDate());

let d = new Date('08/11/2021');

// extended function for datepicker
const dateRanges = (date = new Date(), rule = 10, sum = 0) => Math.floor(date.getFullYear() / rule) * rule + sum;

let changeDate = false;

$('.datepicker').datepicker({
    todayBtn: "linked",
    language: 'id',
    format: 'd MM yyyy',
    autoclose: true,
    enableOnReadonly: false // readonly input will not show datepicker . The default value true
}).change(function(e) {
    // submitControl(e.target);
}).on('show', function(e) {
    let allowedPicker = [],
        untilPicker = undefined,
        year = undefined,
        timeEpoc;
    if (e.viewMode == 0) {
        timeEpoc = 'day';
    } else if (e.viewMode == 1) {
        timeEpoc = 'month';
    } else if (e.viewMode == 2) {
        timeEpoc = 'year';
    } else if (e.viewMode == 3) {
        // Start From This(d) Decade
        allowedPicker = [dateRanges(d)];
        // Until This(default dateRanges) Decade
        untilPicker = dateRanges();
        year = 10;
        timeEpoc = 'decade';
    } else if (e.viewMode == 4) {
        // daterange second params set to 100 a century
        // Start From This(d) Century
        allowedPicker = [dateRanges(d, 100)];
        // Until This(now) Century
        untilPicker = dateRanges(new Date(), 100);
        year = 100;
        timeEpoc = 'century';
    }

    $('.datepicker.datepicker-dropdown table tbody tr [class!="'+ timeEpoc +'"].focused').removeClass('focused');

    if (allowedPicker.length > 0) {
        if (allowedPicker.find(element => element == untilPicker) == undefined) {
            allowedPicker.push(untilPicker)
        }
        if (allowedPicker.length > 1 && allowedPicker[1] - allowedPicker[0] > year) {
            let loop = 1;
            do {
                if (allowedPicker.find(element => element == (loop*year)) == undefined) {
                    allowedPicker.push(allowedPicker[loop-1]+year);
                }
                loop++;
            } while ((allowedPicker[1] - allowedPicker[0]) / year > loop);
            allowedPicker.sort();
        }
        allowedPicker.forEach(pickElement => {
            $('.datepicker.datepicker-dropdown table tbody td .disabled:contains('+ pickElement +')').removeClass('disabled');
        });
    }
}).datepicker('setStartDate', d);

const inputJumlahDonasi = document.getElementById('input-jumlah-donasi');
inputJumlahDonasi.addEventListener('keypress', preventNonNumbersInInput);
inputJumlahDonasi.addEventListener('keyup', function () {
    this.value = numberToPrice(this.value, 'Rp. ');
});

function selectLabel(array) {
    return Object.values(array.reduce((accu, { id_cp: id, jenis: text, nama, path_gambar }) => {
        (accu[text] ??= { text: keteranganJenisChannelPayment(text), children: [] }).children.push({ id, text: nama, path_gambar });
        return accu;
    }, {}));
}