let ctrl = undefined;

const inputCheckAlias = document.getElementById('input-check-alias'),
inputAlias = document.getElementById('input-alias-donasi'),
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

inputCheckAlias.addEventListener('click', function () {
    if (this.checked) {
        this.parentElement.classList.add('checked');
        inputAlias.classList.remove('d-none');
        inputAlias.nextElementSibling.classList.remove('d-none');
        inputAlias.focus();
        textareaDoa.removeAttribute('style');
    } else {
        this.parentElement.classList.remove('checked');
        inputAlias.classList.add('d-none');
        inputAlias.nextElementSibling.classList.add('d-none');
        textareaDoa.style = 'min-height: 89px !important; max-height: 89px !important;';
    }
});

const inputCheckDoa = document.getElementById('input-check-doa'),
      textareaDoa = document.getElementById('textarea-doa'),
      charLeft = document.getElementById('charLeft');

if (!inputCheckAlias.checked) {
    textareaDoa.style = 'min-height: 89px !important; max-height: 89px !important;';
}

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
                data: selectOtionGroupLabel(data),
                matcher: modelMatcher,
                escapeMarkup: function (markup) { return markup; },
                templateResult: formatChannelPayment
            }).on('select2:open', function() {
                if ($(this).hasClass("select2-hidden-accessible")) {
                    if ($(this).hasClass('is-invalid')) {
                        $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').addClass('is-invalid');
                    } else {
                        $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').removeClass('is-invalid');
                    }
                }
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
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
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
    $bantuan = '<div class="d-flex justify-content-between"><div class="font-weight-bold w-80 overflow-hidden">'+ bantuan.text +'</div><div class="text-muted"><span class="'+ (bantuan.status == 'S' ? statusBantuan(bantuan.status).class+' ' : '') +'badge p-2">'+ statusBantuan(bantuan.status).text +'</span></div></div>';
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

// CATATAN JIKA SUATU delay 750 masih ada LOST AKIBAT CEK TOKEN di METHOD AJAX, FETCH ADMIN CHECK TOKEN LEBIH BAIK DI NON AKTIFKAN DAN LIHAT COMMENT MENGENAI DELAY DI BAWAH 750
let dataBantuan = {};
$('#input-bantuan-donasi').select2({
    // minimumInputLength: 1,
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function() { return "Data yang dicari tidak ditemukan"; }, searching: function() { return "Sedang melakukan pencarian..."; }, loadingMore: function() { return "Menampilkan data yang lainnya"; }, },
    placeholder: "Pilih salah satu",
    ajax: {
        url: '/admin/fetch/ajax/bantuan',
        type: 'post',
        dataType: 'json',
        delay: 750,
        contentType: "application/json",
        data: function (params) {
            let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
            // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
            // if (input.val().length) {
            //     params.search = input.val();
            // }
            // delete params.term;
            // akhir uncomment

            // JIKA delay 750 masih error saat cek token maka comment code ini
            if (input.val().length) {
                params.term = input.val();
                params.search = params.term;
            }
            // akhir comment
            
            if (dataBantuan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataBantuan.search))) {
                params.offset = parseInt(dataBantuan.offset) + parseInt(dataBantuan.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            // console.log(response);
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });
            
            if (response.error) {
                console.log(response.feedback.message);
                return false;
            }
            let data = response.feedback.data;
            data = data.map(function (elments) {
                return {
                    id: elments.id_bantuan,
                    text: elments.nama_bantuan,
                    status: elments.status,
                    min_donasi: elments.min_donasi
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
        }
    },
    escapeMarkup: function (markup) { return markup; },
    templateResult: formatDataBantuan
}).on('select2:open', function() {
    if (dataBantuan.search != undefined) {
        let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
        input.val(dataBantuan.search);
    }
    // console.log(dataBantuan);
    if (!dataBantuan.load_more) {
        dataBantuan.offset = 0;
        if ($(this).hasClass("select2-hidden-accessible")) {
            $('#select2-'+ $(this).attr('id') +'-results').scrollTop(0);
        }
    }

    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
}).on('select2:close', function(e) {
    dataBantuan.load_more = false;
}).on("select2:select", function (e) {
    if (e.params.data.status == 'S') {
        $('#modalNotifikasiTambahDonasiBantuanSelesai').find('#nama-bantuan').attr('href','/admin/bantuan/data/'+e.params.data.id);
        $('#modalNotifikasiTambahDonasiBantuanSelesai').find('#nama-bantuan').attr('data-id',e.params.data.id);
        $('#modalNotifikasiTambahDonasiBantuanSelesai').find('#nama-bantuan').text(e.params.data.text);
        $('#modalNotifikasiTambahDonasiBantuanSelesai').modal('show'); 
    }
    if (e.params.data.min_donasi) {
        dataBantuan.selected = {
            min_donasi: e.params.data.min_donasi
        }
        if (priceToNumber(inputJumlahDonasi.value) > 0) {
            if (priceToNumber(inputJumlahDonasi.value) >= parseInt(e.params.data.min_donasi)) {
                if (inputJumlahDonasi.classList.contains('is-invalid')) {
                    inputJumlahDonasi.closest('.form-group').classList.remove('is-invalid');
                    inputJumlahDonasi.classList.remove('is-invalid');
                    inputJumlahDonasi.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
                }
            } else {
                if (!inputJumlahDonasi.classList.contains('is-invalid')) {
                    iError.error = true;
                    iError.message = 'Minimal donasi on select tidak terpenuhi';
                    inputJumlahDonasi.closest('.form-group').classList.add('is-invalid');
                    inputJumlahDonasi.classList.add('is-invalid');
                }
                inputJumlahDonasi.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+numberToPrice(e.params.data.min_donasi));
            }
        }
    } else {
        if (dataBantuan.selected) {
            delete dataBantuan.selected.min_donasi;
        }
        if (inputJumlahDonasi.classList.contains('is-invalid') && inputJumlahDonasi.value.length && priceToNumber(inputJumlahDonasi.value) >= min_ketentuan_donasi) {
            inputJumlahDonasi.closest('.form-group').classList.remove('is-invalid');
            inputJumlahDonasi.classList.remove('is-invalid');
            inputJumlahDonasi.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
        } else {
            if (inputJumlahDonasi.value.length) {
                if (!inputJumlahDonasi.classList.contains('is-invalid')) {
                    inputJumlahDonasi.closest('.form-group').classList.add('is-invalid');
                    inputJumlahDonasi.classList.add('is-invalid');
                }
                inputJumlahDonasi.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+numberToPrice(min_ketentuan_donasi));
            }
        }
    }
});

// CATATAN JIKA SUATU delay 750 masih ada LOST AKIBAT CEK TOKEN di METHOD AJAX, FETCH ADMIN CHECK TOKEN LEBIH BAIK DI NON AKTIFKAN DAN LIHAT COMMENT MENGENAI DELAY DI BAWAH 750
let dataDonatur = {};
$('#input-donatur-donasi').select2({
    // minimumInputLength: 1,
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function() { return "Data yang dicari tidak ditemukan"; }, searching: function() { return "Sedang melakukan pencarian..."; }, loadingMore: function() { return "Menampilkan data yang lainnya"; }, },
    placeholder: "Pilih salah satu",
    ajax: {
        url: '/admin/fetch/ajax/donatur',
        type: 'post',
        dataType: 'json',
        delay: 750,
        contentType: "application/json",
        data: function (params) {
            let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
            // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
            // if (input.val().length) {
            //     params.search = input.val();
            // }
            // delete params.term;
            // akhir uncomment

            // JIKA delay 750 masih error saat cek token maka comment code ini
            if (input.val().length) {
                params.term = input.val();
                params.search = params.term;
            }
            // akhir comment
            
            if (dataDonatur.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataDonatur.search))) {
                params.offset = parseInt(dataDonatur.offset) + parseInt(dataDonatur.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            // console.log(response);
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });
            
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
        }
    },
    escapeMarkup: function (markup) { return markup; },
    templateResult: formatDataDonatur
}).on('select2:open', function() {
    if (dataDonatur.search != undefined) {
        let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
        input.val(dataDonatur.search);
    }
    // console.log(dataDonatur);
    if (!dataDonatur.load_more) {
        dataDonatur.offset = 0;
        if ($(this).hasClass("select2-hidden-accessible")) {
            $('#select2-'+ $(this).attr('id') +'-results').scrollTop(0);
        }
    }

    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
}).on('select2:close', function(e) {
    dataDonatur.load_more = false;
}).on("select2:select", function (e) {
    inputAlias.value = (e.params.data.samaran.toLowerCase() == 'sahabat berbagi' ? '' : e.params.data.samaran);
    dataDonatur.selected = {
        name: e.params.data.text
    };

    if (!inputCheckAlias.checked) {
        iNames.alias = e.params.data.text;
    }

    if (e.params.data.email.length) {
        dataDonatur.selected.email = e.params.data.email;
    }
}).on('change', function(e) {
    setTimeout(() => {
        if (dataDonatur.selected) {
            if (!dataDonatur.selected.email) {
                inputNotifikasi.parentElement.classList.add('d-none');
            } else {
                inputNotifikasi.parentElement.classList.remove('d-none');
            }
        }
    }, 0)
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
    if (this.classList.contains('is-invalid')) {
        this.classList.remove('is-invalid');
        this.closest('.form-group.is-invalid').removeAttribute('data-label-after');
        this.closest('.form-group.is-invalid').classList.remove('is-invalid');
    }
    iNames.waktu_bayar = $(this).datepicker( 'getDate' );
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

const inputJumlahDonasi = document.getElementById('input-jumlah-donasi'),
      min_ketentuan_donasi = 5000;
let oldValue;
inputJumlahDonasi.addEventListener('keypress', preventNonNumbersInInput);
inputJumlahDonasi.addEventListener('keydown', function(e) {
    let prefix = 'Rp. ';
    if (e.code == "ArrowUp" || e.target.selectionStart == 0 && e.target.selectionStart != e.target.selectionEnd && e.code == "ArrowLeft" || e.code == "ArrowLeft" && e.target.selectionStart == prefix.length || e.code == "Home") {
        e.target.selectionStart = prefix.length;
        e.target.selectionEnd = prefix.length;
        e.preventDefault();
        return false;
    }
    if (e.code == "Delete" || e.code == "Backspace") {
        oldValue = this.value;
        if (e.target.selectionStart <= prefix.length && e.target.selectionStart == e.target.selectionEnd && e.code == "Backspace") {
            e.target.selectionStart = prefix.length;
            e.target.selectionEnd = prefix.length;
            e.preventDefault();
            return false;
        }
    }
});
inputJumlahDonasi.addEventListener('keyup', function (e) {
    let ceret = e.target.selectionStart,
        numberTPArray = numberToPrice(this.value, 'Rp. ', e),
        value = numberTPArray[0],
        sisa = numberTPArray[1],
        ribuan = numberTPArray[2],
        prefix = numberTPArray[3];

    this.value = value;

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
            if (oldValue == this.value) {
                if (sisa == 0) {
                    ceret += 2;
                } else if (sisa == 2) {
                    ceret++;
                } else {
                    ceret++;
                }
                this.value = numberToPrice(removeByIndex(this.value, ceret), prefix);
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
            if (sisa == 0 && oldValue != this.value) {
                ceret--;
            }
            if (oldValue == this.value) {
                this.value = numberToPrice(removeByIndex(this.value, --ceret), prefix);
                if (sisa == 1 && ceret > prefix.length + 1) {
                    ceret--;
                }
            }
            e.target.selectionStart = ceret;
            e.target.selectionEnd = ceret;
        }
    }

    if (dataBantuan.selected) {
        if (dataBantuan.selected.min_donasi == undefined) {
            min_donasi = min_ketentuan_donasi;
        } else {
            min_donasi = dataBantuan.selected.min_donasi;
        }  

        if (priceToNumber(this.value) > 0 && priceToNumber(this.value) >= min_donasi || !this.value.length) {
            if (this.classList.contains('is-invalid')) {
                this.closest('.form-group').classList.remove('is-invalid');
                this.classList.remove('is-invalid');
                this.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
            }
        }
    } else {
        if (priceToNumber(this.value) > 0 && priceToNumber(this.value) >= min_ketentuan_donasi || !this.value.length) {
            if (this.classList.contains('is-invalid')) {
                this.closest('.form-group').classList.remove('is-invalid');
                this.classList.remove('is-invalid');
                this.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
            }
        }
    }
});

inputJumlahDonasi.addEventListener('change', function () {
    if (typeof this.value == "string") {
        if (priceToNumber(this.value) > 0) {
            this.value = priceToNumber(this.value);
        }
    }
    this.value = numberToPrice(this.value, 'Rp. ');
    if (dataBantuan.selected) {
        if (dataBantuan.selected.min_donasi == undefined) {
            if (priceToNumber(this.value) > 0 && priceToNumber(this.value) < min_ketentuan_donasi) {
                if (!this.classList.contains('is-invalid')) {
                    this.closest('.form-group').classList.add('is-invalid');
                    this.classList.add('is-invalid');
                }
                iError.error = true;
                iError.message = 'Minimal donasi on change tidak terpenuhi';
                this.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+ numberToPrice(min_ketentuan_donasi));
            }
        } else {
            if (priceToNumber(this.value) < parseInt(dataBantuan.selected.min_donasi)) {
                if (!this.classList.contains('is-invalid')) {
                    this.closest('.form-group').classList.add('is-invalid');
                    this.classList.add('is-invalid');
                }
                iError.error = true;
                iError.message = 'Minimal donasi on change tidak terpenuhi';
                this.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+numberToPrice(dataBantuan.selected.min_donasi));
            }
        }
    } else {
        if (priceToNumber(this.value) > 0 && priceToNumber(this.value) < min_ketentuan_donasi) {
            if (!this.classList.contains('is-invalid')) {
                this.closest('.form-group').classList.add('is-invalid');
                this.classList.add('is-invalid');
            }
            iError.error = true;
            iError.message = 'Minimal donasi on change tidak terpenuhi';
            this.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+ numberToPrice(min_ketentuan_donasi));
        }
    }
});
inputJumlahDonasi.addEventListener('click', function(e) {
    let prefix = 'Rp. ';
    if (this.value.length && e.target.selectionStart <= prefix.length) {
        e.target.selectionStart = prefix.length;
    }
});

function selectOtionGroupLabel(array) {
    return Object.values(array.reduce((accu, { id_cp: id, jenis: text, nama, path_gambar }) => {
        (accu[text] ??= { text: keteranganJenisChannelPayment(text), children: [] }).children.push({ id, text: nama, path_gambar });
        return accu;
    }, {}));
}

const inputNotifikasi = document.getElementById('notifikasi_bayar');
if (!$('#input-donatur-donasi').val().length) {
    inputNotifikasi.parentElement.classList.add('d-none');
}

const resetBtn = document.querySelector('button[type="clear"]'),
      submitBtn = document.querySelector('button[type="submit"]');

let names = submitBtn.closest('.card').closest('.row').querySelectorAll('[name]');
resetBtn.addEventListener('click', function() {
    names.forEach(element => {
        if (element.getAttribute('name') == 'waktu_bayar') {
            $('.datepicker').val("").datepicker("update");
        }
        if (element?.tagName.toLowerCase() === 'input' && element?.getAttribute('type') === 'checkbox') {
            element.checked = false;
            if (element.getAttribute('id') == 'input-check-alias') {
                element.parentElement.classList.remove('checked');
                inputAlias.classList.add('d-none');
                inputAlias.nextElementSibling.classList.add('d-none');
            }
            if (element.getAttribute('id') == 'input-check-doa') {
                element.parentElement.classList.remove('checked');
                textareaDoa.classList.add('d-none');
                charLeft.classList.add('d-none');
            }
        }
        if (element?.tagName.toLowerCase() === 'select') {
            $('#'+element.getAttribute('id')).val(null).trigger('change');
        }
        element.value = '';
        if (element?.tagName.toLowerCase() === 'textarea') {
            charLeft.querySelector('span').innerText = '0';
        }
        if (element.classList.contains('is-invalid')) {
            element.classList.remove('is-invalid');
            element.closest('.form-group').classList.remove('is-invalid');
            element.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
        }
    });
    delete dataBantuan.selected;
    delete dataDonatur.selected;
    inputNotifikasi.parentElement.classList.add('d-none');
    iNames = {};
    submitBtn.classList.remove('disabled');
});

let iNames = {},
    iError = {
        error: false
    },
    action = window.location.pathname;
submitBtn.addEventListener('click', function() {
    names.forEach(name => {
        if (name.getAttribute('data-required') == 'true') {
            if (!name.value.length) {
                let iNullWord = (name?.tagName.toLowerCase() == 'select' ? 'dipilih' : 'diisi');
                iError.error = true;
                iError.message = 'Data [name="'+ name.getAttribute('name') +'"] wajib '+ iNullWord;
                name.closest('.form-group').classList.add('is-invalid');
                name.classList.add('is-invalid');
                name.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'wajib '+ iNullWord);
                return;
            } else {
                if (name.closest('.form-group').classList.contains('is-invalid')) {
                    name.closest('.form-group').classList.remove('is-invalid');
                    name.classList.remove('is-invalid');
                    name.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
                }
            }
        }
        if (name?.tagName.toLowerCase() === 'input' && name?.getAttribute('type') === 'checkbox') {
            if (!name.checked) {
                if (name.getAttribute('name') == 'check_doa') {
                    delete iNames.doa;
                }
                if (name.getAttribute('name') == 'check_alias') {
                    if (dataDonatur.selected)
                    iNames.alias = dataDonatur.selected.name;
                }
                if (name.getAttribute('name') == 'notifikasi') {
                    delete iNames.notifikasi;
                }
                return;
            }
            if (name.getAttribute('name') == 'check_doa') {
                if (textareaDoa.value.length) {
                    iNames.doa = textareaDoa.value;
                } else {
                    delete iNames.doa;
                }
            }
            if (name.getAttribute('name') == 'check_alias') {
                if (inputAlias.value.length) {
                    iNames.alias = inputAlias.value;
                } else {
                    delete iNames.alias;
                }
            }
            if (name.getAttribute('name') == 'notifikasi') {
                iNames.notifikasi = true;
            }
        } else {
            if (name.value.length && name.getAttribute('name') == 'jumlah_donasi') {
                if (dataBantuan.selected) {
                    if (priceToNumber(dataBantuan.selected.min_donasi) > priceToNumber(name.value)) {
                        iError.error = true;
                        iError.message = 'Minimal donasi on submit tidak terpenuhi';
                        name.closest('.form-group').classList.add('is-invalid');
                        name.classList.add('is-invalid');
                        name.closest('.form-group').querySelector('label').setAttribute('data-label-after', 'minimal '+numberToPrice(dataBantuan.selected.min_donasi));
                        return;
                    } else {
                        if (name.closest('.form-group').classList.contains('is-invalid')) {
                            name.closest('.form-group').classList.remove('is-invalid');
                            name.classList.remove('is-invalid');
                            name.closest('.form-group').querySelector('label').removeAttribute('data-label-after');
                        }
                    }
                }
            }
            if (name.value.length && name?.tagName.toLowerCase() !== 'textarea' && name.getAttribute('name') != 'alias' && name.getAttribute('name') != 'waktu_bayar') {
                iNames[name.getAttribute('name')] = name.value;
            }
        }
    });
    const invalid = document.querySelectorAll('[data-required="true"].is-invalid');
    if (invalid.length) {
        this.classList.add('disabled')
        return false;
    } else {
        this.classList.remove('disabled')
    }
    iError.error = false;
    delete iError.message;
    // console.log(dataDonatur.selected)
    // console.log(iNames)
    // fetch create Donasi
    let input = {
        data: iNames,
        token: body.getAttribute('data-token'),
        error: iError.error
    };
    fetch('/admin/fetch/create/donasi', {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body:JSON.stringify(input)
    })
    .then(response => response.json())
    .then(function(result) {
        // console.log(result);
        if (result.error == false) {
            // Success
            // $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            // $('.toast[data-toast="feedback"] .toast-header strong').text('Informasi');
        } else {
            // Failed
            $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
            $('.toast[data-toast="feedback"] .toast-header strong').text('Peringatan!');
            console.log('there is some error in server side');
        }

        $('.toast[data-toast="feedback"] .toast-body').html(result.feedback.message);
        $('.toast[data-toast="feedback"] .time-passed').text('Baru Saja');
        $('.toast').toast('show');
    
        document.querySelector('body').setAttribute('data-token', result.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });

        if (result.error == false) {
            deleteProperties(input);
            let redirectTo = '/admin/donasi';
            window.location.href = redirectTo;
        }
    });
});

submitBtn.addEventListener('mouseenter', function(e) {
    const invalid = document.querySelectorAll('[data-required="true"].is-invalid');
    if (invalid.length) {
        this.classList.add('disabled')
    } else {
        this.classList.remove('disabled')
    }
});