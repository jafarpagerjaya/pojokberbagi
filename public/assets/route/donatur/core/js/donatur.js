function responseUpdateCp(elements, response) {
    let elTr = elements.closest('tr'),
        elCp = elTr.querySelector('.channel-payment'),
        feedback;

    elCp.querySelector('[data-jenis-cp').setAttribute('data-jenis-cp', response.type);
    elCp.querySelector('[data-jenis-cp').innerText = response.typeDesc;
    elCp.querySelector('img').setAttribute('src', response.src);
    elCp.querySelector('img').setAttribute('alt', response.alt);
    
    if (response.data.error) {
        feedback = response.data.feedback+' :(';
        $('.toast .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
        $('.toast .toast-header strong').text('Peringatan!');
    } else {
        feedback = response.data.feedback+' ke <span class="font-weight-bolder">'+response.typeDesc+' <span class="text-primary">'+response.alt+'</span></span> :)';
        $('.toast .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
        $('.toast .toast-header strong').text('Pemberitahuan');
    }

    $('.toast .time-passed').text('Baru Saja');
    $('.toast .toast-body').html(feedback);

    $('.toast').toast('show');
};

// BroadcastChannel for data-token
const body = document.querySelector('body');
let fetchTokenChannel;
if (body.getAttribute('data-token') != null) {
    fetchTokenChannel = new BroadcastChannel('fetch-token');
    // If recive message
    fetchTokenChannel.onmessage = event => { 
        // set data token
        body.setAttribute('data-token', event.data.token);
        // cek data respose
        if (event.data.response != undefined) {
            const response = event.data.response,
            elDataDonasi = body.querySelector('[data-id-donasi="'+response.id_donasi+'"]');

            if (elDataDonasi == null) {
                return false;
            }

            responseUpdateCp(elDataDonasi, response);
        }
    };

    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
}

function jenis_cp(params) {
    switch (params.toUpperCase()) {
        case 'TB':
            params = 'Transfer Bank';
            break;
        case 'QR':
            params = 'Qris';
            break;
        case 'GM':
            params = 'Gerai Mart';
            break;
        case 'EW':
            params = 'E-Wallet';
            break;
        case 'VA':
            params = 'Virtual Account';
            break;
        case 'GI':
            params = 'Giro';
            break;
        case 'TN':
            params = 'Tunai';
            break;
        default:
            break;
    }
    return params;
};

$('.modal [type="button"]').on('click', function(e) {
    if ($(this).hasClass('disabled')) {
        e.stopPropagation();
    }
});

let dataImgCP = {};
$('#modalGantiMetodePembayaran').on('hidden.bs.modal', function () {
    if ($(this).find('.radio-box .item:not(.selected) input[type="radio"]:checked').length) {
        $(this).find('.radio-box .item:not(.selected) input[type="radio"]').removeAttr('checked').prop('checked', false);
    }

    if (!$(this).find('[type="button"][data-target]').hasClass('disabled')) {
        $(this).find('[type="button"][data-target]').addClass('disabled');
    }

    if ($(this).find('[type="button"][data-target]:not([data-dismiss])[data-id-donasi]').length) {
        $(this).find('[type="button"][data-target]:not([data-dismiss])[data-id-donasi]').removeAttr('data-id-donasi');
    }

    if (dataImgCP.elements != undefined) {
        dataImgCP.elements = undefined;
    }
}).on('show.bs.modal', function(e) {
    const radioBox = this.querySelector('.radio-box');

    let id_cp = e.relatedTarget.getAttribute('data-cp'),
        data_idonasi = e.relatedTarget.closest('tr').querySelector('[data-id-donasi]').getAttribute('data-id-donasi'),
        data_nbantuan = e.relatedTarget.closest('tr').querySelector('a[data-nama-bantuan]').getAttribute('data-nama-bantuan'),
        data_hbantuan = e.relatedTarget.closest('tr').querySelector('a[data-nama-bantuan]').getAttribute('href'),
        elCP = e.relatedTarget.closest('tr').querySelector('.channel-payment'),
        cp_type = elCP.querySelector('[data-jenis-cp]'),
        cp_name = elCP.querySelector('img[alt]');

    dataImgCP.elements = elCP;
    this.querySelector('#modalGantiMetodePembayaranLabel a').setAttribute('data-nama-bantuan', data_nbantuan);
    this.querySelector('#modalGantiMetodePembayaranLabel a').setAttribute('href', data_hbantuan);
    this.querySelector('#modalGantiMetodePembayaranLabel a').innerText = data_nbantuan;

    let cp_type_value = undefined,
        cp_name_value = '';

    if (cp_type == null) {
        console.log('[data-jenis-cp] tidak ditemukan', elCP.children);
    } else {
        cp_type_value = jenis_cp(cp_type.getAttribute('data-jenis-cp'));
    }

    if (cp_name == null) {
        console.log('img[alt] tidak ditemukan didalam elment', elCP.children);
    } else {
        cp_name_value = cp_name.getAttribute('alt');
    }

    if (cp_type_value == undefined || cp_type_value != undefined && cp_type_value.length == 0) {
        console.log('nilai [data-jenis-cp] tidak ditemukan', cp_type);
        cp_type_value = 'Jenis Payment (Unrecognize)';
    }

    if (cp_name_value == undefined) {
        console.log('nilai [alt] tidak ditemukan', cp_name);
        cp_name_value = 'Partner Payment (Unrecognize)';
    }

    const elPS = this.querySelector('.modal-body .position-sticky');

    elPS.querySelector('span.jenis-cp').innerText = cp_type_value;
    elPS.querySelector('span.nama-cp').innerText = cp_name_value;

    if (id_cp == null) {
        return;
    }

    elPS.querySelector('[type="button"][data-target]:not([data-dismiss])').setAttribute('data-id-donasi', data_idonasi);

    let inputCP = this.querySelector('.radio-box .item input[value="'+id_cp+'"]'),
        itemSelected = radioBox.querySelector('.item.selected');
    
        if (itemSelected == null) {
            const input = inputCP;
            input.checked = true;
            input.setAttribute('name', input.getAttribute('name')+'_selected');
            input.closest('.item').classList.add('selected');
            return;
        }
        
        if (typeof itemSelected == 'object' && itemSelected != null) {
            let itemSelectedInput = itemSelected.querySelector('.inputGroup input[type="radio"]'),
                itemSelectedInputName = itemSelectedInput.getAttribute('name'),
                itemSelectedInputValue = itemSelectedInput.value;

            if (itemSelectedInputValue == id_cp) {
                return;
            }
            
            itemSelectedInput.setAttribute('name', inputCP.getAttribute('name'));
            inputCP.setAttribute('name', itemSelectedInputName);

            itemSelectedInput.checked = false;
            inputCP.checked = true;
            
            itemSelected.classList.remove('selected');
            inputCP.closest('.item').classList.add('selected');
        }
});

const modalGMP = document.getElementById('modalGantiMetodePembayaran');

modalGMP.querySelectorAll('.radio-box .item:not(.selected)').forEach(item => {
    item.addEventListener('click', function(e) {
        e.stopPropagation();
        e.preventDefault();

        if (this.querySelector('input[type="radio"]').checked) {
            return false;
        }
        
        this.querySelector('input[type="radio"]').checked = true;

        let buttonToConfirm = undefined;

        // Harus hati2 jika data-dismiss punya data-target juga
        buttonToConfirm = modalGMP.querySelector('[type="button"][data-target]');
        if (buttonToConfirm.classList.contains('disabled')) {
            buttonToConfirm.classList.remove('disabled');
            buttonToConfirm.getAttribute('data-target');
        }
    });
});

modalGMP.querySelector('[type="button"][data-target]:not([data-dismiss])').addEventListener('click', function(e) {
    if (modalGMP.querySelectorAll('.item:not(.selected) input[type="radio"]:checked').length != 1) {
        e.stopPropagation();
        e.preventDefault();
    }
});

$('#modalKonfirmasiGantiMetodePembayaran').on('show.bs.modal', function (e) {
    const modalGanti = e.relatedTarget.closest('.modal'),
          newIdCp = modalGanti.querySelector('.radio-box .item:not(.selected) input[type="radio"]:checked');

    let newIdValue = undefined,
        nama_bantuan = modalGanti.querySelector('a[data-nama-bantuan]').getAttribute('data-nama-bantuan'),
        href_bantuan = modalGanti.querySelector('a[data-nama-bantuan]').getAttribute('href'),
        id_donasi = e.relatedTarget.getAttribute('data-id-donasi'),
        cp_type,
        cp_name;

    data = {};
    data.id_cp = newIdCp.value;
    data.id_donasi = id_donasi;
    data.token = document.querySelector('body').getAttribute('data-token');

    if (newIdCp != null) {
        newIdValue = newIdCp.value;
    }
    
    if (newIdValue == undefined) {
        cp_type = 'Jenis Payment (Unrecognize)';
        cp_name = 'Partner Payment (Unrecognize)';
    } else {
        const item = newIdCp.closest('.item');
        cp_name = item.querySelector('img').getAttribute('alt');
        cp_type = jenis_cp(item.querySelector('img').getAttribute('data-jenis-cp'));
    }
    this.querySelector('.modal-body a#nama-bantuan').innerText = nama_bantuan;
    this.querySelector('.modal-body a#nama-bantuan').setAttribute('href', href_bantuan);
    this.querySelector('.modal-body span.jenis-cp').innerText = cp_type;
    this.querySelector('.modal-body span.nama-cp').innerText = cp_name;

    dataImgCP.src = newIdCp.closest('.item').querySelector('img').getAttribute('src');
    dataImgCP.type = newIdCp.closest('.item').querySelector('img').getAttribute('data-jenis-cp').toUpperCase();
    dataImgCP.typeDesc = cp_type;
    dataImgCP.alt = cp_name;
});

let data = {};
$('#modalKonfirmasiGantiMetodePembayaran [type="submit"]').on('click', function(e) {
    let message = undefined;
    if (data.token == null) {
        message = 'Fetch being abort -> fetch token is null';
        console.log(message);
        alert(message);
        return false;
    }

    const id_donasi = data.id_donasi,
          id_cp = data.id_cp;
    // Fetch with token
    fetch('/donatur/fetch/update/payment-method', {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(function(data) {
        if (data.error == false) {
            // Success
            message = data.feedback+' ke <span class="font-weight-bolder">'+dataImgCP.typeDesc+' <span class="text-primary">'+dataImgCP.alt+'</span></span> :)';

            let elCP = dataImgCP.elements;

            elCP.querySelector('[data-jenis-cp]').setAttribute('data-jenis-cp', dataImgCP.type);
            elCP.querySelector('[data-jenis-cp]').innerText = dataImgCP.typeDesc;
            elCP.querySelector('img').setAttribute('src', dataImgCP.src);
            elCP.querySelector('img').setAttribute('alt', dataImgCP.alt);
            elCP.parentElement.parentElement.querySelector('a~.dropdown-menu>a').setAttribute('data-cp', id_cp);
            elCP.closest('tr').classList.add('highlight');

            setTimeout(() => {
                elCP.closest('tr').classList.remove('highlight');
            }, 3100);
        } else {
            message = data.feedback+' :(';
            console.log('there is some error in server side');
        }

        if (data.error) {
            $('.toast .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
            $('.toast .toast-header strong').text('Peringatan!');
        } else {
            $('.toast .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            $('.toast .toast-header strong').text('Pemberitahuan');
        }

        $('.toast .time-passed').text('Baru Saja');
        $('.toast .toast-body').html(message);

        delete dataImgCP.elements;

        dataImgCP.data = data;
        dataImgCP.id_donasi = id_donasi;

        document.querySelector('body').setAttribute('data-token', data.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token'),
            response: dataImgCP
        });

        dataImgCP = {};

        $('.toast').toast('show');
        // Close all modal
        $('.modal').modal('hide');
    });
});

$('.toast').on('hidden.bs.toast', function (e) {
    $(this).find('.toast-header .small-box').removeClass('bg-danger bg-success');
    $(this).find('.toast-header strong').text('Apaan');
    $(this).find('.toast-body').text('Belum ada isi');
    $(this).find('.time-passed').text('Kapan terjadi');
});