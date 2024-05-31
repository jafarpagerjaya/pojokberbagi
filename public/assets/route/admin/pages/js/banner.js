let objectBanner = {},
    dataBantuan = {},
    relatedModal;

let formatSelected = function(objectSelected) {
    const label = objectSelected.element.closest('select').parentElement.querySelector('label');

    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '';

    if (label != null) {
        $elSelected = label.outerHTML;
    }

    if (objectSelected.status == null) {
        $elSelected = $elSelected + '<div class="font-weight-normal">' + objectSelected.text + '</div>';
    } else {
        $elSelected = $elSelected + '<div class="d-flex justify-content-between align-items-center"><span class="font-weight-normal">' + objectSelected.text + '</span><span class="badge '+ objectSelected.status.class +'">'+ objectSelected.status.text +'</span></div>';
    }

    return $elSelected;
};

function formatResult(result) {
    if (result.loading) {
        return result.text;
    }
    let $result;
    if (result.children != null) {
        $result = '<div class="font-weight-bolder">' + result.text + '</div>';
    } else {
        $result = '<div class="d-flex justify-content-between align-items-center"><span class="font-weight-normal">' + result.text + '</span><span class="text-right badge '+ result.status.class +'">'+ result.status.text +'</span></div>';
    }
    return $result;
};

$('#id-bantuan').select2({
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, },
    ajax: {
        url: '/admin/fetch/ajax/bantuan/banner',
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
            if (input.length != 0) {
                if (input.val().length) {
                    params.term = input.val();
                    params.search = params.term;
                }
            } else {
                params.search = params.term;
            }
            // akhir comment
            
            if (dataBantuan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataBantuan.search))) {
                params.offset = parseInt(dataBantuan.offset) + parseInt(dataBantuan.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            Object.assign(params, objectBanner);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (response.error) {
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': false
                }).toast('show');
                $('#id-bantuan').select2('close');
                return false;
            }

            let data = response.feedback.data;
            
            data.map(element => {
                if (element.status == 'aktif') {
                    element.status = {
                        text: element.status,
                        class: 'badge-success'
                    }
                } else if (element.status == 'belum aktif') {
                    element.status = {
                        'text': element.status,
                        'class': 'badge-warning'
                    }
                } else if (element.status == 'kadaluarsa') {
                    element.status = {
                        'text': element.status,
                        'class': 'badge-danger'
                    }
                } else if (element.status == 'kadaluarsa') {
                    element.status = {
                        'text': element.status,
                        'class': 'badge-secondary'
                    }
                } else if (element.status == 'sudah ditakedown') {
                    element.status = {
                        'text': element.status,
                        'class': 'badge-natural'
                    }
                } else {
                    element.status = {
                        'text': '',
                        'class': 'badge-primary'
                    }
                }
                return element;
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
        },
        cache: true
    },
    data: [],
    placeholder: "Pilih salah satu",
    templateSelection: formatSelected,
    templateResult: formatResult,
    escapeMarkup: function (markup) { return markup; }
}).on('select2:select', function (e) {
    if (this.value != '0') {
        objectBanner.id_bantuan = this.value;
        objectBanner.nama_bantuan = e.params.data.text;
        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectBanner.id_bantuan;
        delete objectBanner.nama_bantuan;
    }
    $(this).find('option[value!="'+ this.value +'"]:not([value="0"])').remove();
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

let clickFunction = function(e) {
    let c_error = 0,
        nameList = e.target.closest('.modal').querySelectorAll('[name]'),
        root = e.target.closest('.modal');

    if (nameList != undefined) {
        nameList.forEach(name => {
            if (name.getAttribute('unrequired')) {
                return;
            }
            let error = false,
                errorText = 'wajib diisi';

            if (name.tagName.toLowerCase() == 'select') {
                if (name.value == '0' || !name.value.length) {
                    error = true;
                    errorText = 'wajib dipilih';
                }
            } else {
                if (name.tagName.toLowerCase() == 'input') {
                    if (!name.value.length && (name.type != 'file' && name.type != 'checkbox')) {
                        error = true;
                    }
                }
            }

            if (error) {
                c_error++;
                if (!name.parentElement.classList.contains('is-invalid')) {
                    name.parentElement.classList.add('is-invalid');
                    name.classList.add('is-invalid');
                }
                if (name.classList.contains('custom-select')) {
                    if (!name.hasAttribute('multiple')) {
                        name.nextElementSibling.querySelector('label').setAttribute('data-label-after', errorText);
                    } else {
                        name.parentElement.querySelector('label').setAttribute('data-label-after', errorText);
                    }
                } else {
                    name.parentElement.querySelector('label').setAttribute('data-label-after', errorText);
                }
            } else {
                if (name.parentElement.classList.contains('is-invalid')) {
                    name.parentElement.classList.remove('is-invalid');
                    name.classList.remove('is-invalid');
                    if (name.classList.contains('custom-select')) {
                        name.nextElementSibling.querySelector('label').removeAttribute('data-label-after');
                        if (!name.hasAttribute('multiple')) {
                            name.nextElementSibling.querySelector('label').removeAttribute('data-label-after');
                        } else {
                            name.parentElement.querySelector('label').removeAttribute('data-label-after');
                        }
                    } else {
                        name.parentElement.querySelector('label').removeAttribute('data-label-after');
                    }
                }
            }
        });
    }

    if (c_error > 0) {
        return false;
    }
   
    let id_bantuan = root.querySelector('#id-bantuan').value;

    let data = {
        fields: {
            id_bantuan: id_bantuan
        },
        token: document.querySelector('body').getAttribute('data-token')
    };

    switch (objectBanner.mode.toLowerCase()) {
        case 'create':
            data.fields.nama_bantuan = objectBanner.nama_bantuan;
        break;

        case 'update':
        break;

        default:
            let currentDate = new Date(),
                timestamp = currentDate.getTime(),
                invalid = {
                error: true,
                data_toast: 'invalid-show-modal-feedback',
                feedback: {
                    message: 'TR id-banner not found'
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                'delay': 10000
            }).toast('show');

            e.preventDefault();
            return false;
        break;
    }

    if (objectBanner.mode == 'update') {
        if (data.id_banner == objectBanner.id_banner) {
            delete data.id_banner;
        }

        if (!Object.keys(data.fields.id_bantuan).length) {
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'invalid-feedback',
                feedback: {
                    message: 'Belum ada perubahan data yang berarti'
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
                        
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                'autohide': false
            }).toast('show');
            return false;
        }

        if (document.querySelectorAll('.toast[data-toast="invalid-feedback"]').length) {
            $('.toast[data-toast="invalid-feedback"]').toast('hide');
        }
    }

    if (objectBanner.id_banner != null) {
        data.id_banner = objectBanner.id_banner;
    }

    console.log(data);
    // fetchCreateBanner OR fetchUpdateBanner
    fetchData('/admin/fetch/'+ objectBanner.mode +'/banner', data, root, objectBanner.mode +'-banner');
    // $('#'+root.id).modal('hide');
};

document.querySelector('#buat-banner[type="submit"]').addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction));

let submitAction = document.querySelector('#modalKonfirmasiAksiBanner [type="submit"]');
submitAction.addEventListener('click', function(e) {
    let data = {
        'token': body.getAttribute('data-token'),
        'id_banner': objectBanner.id_banner,
        'slot': objectBanner.slot
    };

    console.log(data);
    // fetchResetBanner
    fetchData('/admin/fetch/'+ e.target.closest('.modal').querySelector('p>span').getAttribute('data-type') +'/banner', data, relatedModal, 'reset-banner');
});

document.querySelector('#modalKonfirmasiAksiBanner a').addEventListener('click', function() {
    localStorage.setItem("banner", objectBanner.id_banner);
});

$('#modalFormBanner').on('hidden.bs.modal', function(e) {
    $(this).find('input.form-control').val('');
    if (objectBanner.mode == 'update') {
        document.querySelector('#id-bantuan').insertAdjacentHTML('afterbegin','<option value="0" selected disabled hidden>Pilih salah satu</option>');
        $('#id-bantuan').val(0).trigger('change');
    }
    objectBanner = {};
}).on('show.bs.modal', function(e) {
    relatedModal = e.relatedTarget.closest('.card');
    if (e.relatedTarget.getAttribute('data-type') == 'update') {
        if (e.relatedTarget.closest('tr').getAttribute('data-id-banner') == null) {
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'invalid-show-modal-feedback',
                feedback: {
                    message: 'TR not found'
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                'delay': 10000
            }).toast('show');

            e.preventDefault();
            return false;
        }

        e.target.querySelector('#mode').innerText = 'Ubah Slot';

        let data = {
            'id_banner': e.relatedTarget.closest('tr').getAttribute('data-id-banner'),
            'token': body.getAttribute('data-token')
        };

        objectBanner.id_banner = data.id_banner;
        
        // fetchGetBanner
        fetchData('/admin/fetch/get/banner', data, e.target, 'get-banner');
        objectBanner.mode = 'update';
    } else {
        e.target.querySelector('#mode').innerText = 'Tambah';
        objectBanner.mode = 'create';
    }
});

$('#modalKonfirmasiAksiBanner').on('show.bs.modal', function(e) {
    let tr = e.relatedTarget.closest('tr');
    if (tr != null && tr.getAttribute('data-id-banner') != null) {
        objectBanner.id_banner = tr.getAttribute('data-id-banner'),
        objectBanner.slot = tr.querySelector('td:first-child>b').innerText;
    } else {
        let currentDate = new Date(),
            timestamp = currentDate.getTime(); 

        let invalid = {
            error: true,
            data_toast: 'invalid-show-modal-feedback',
            feedback: {
                message: 'TR id-banner not found'
            }
        };

        invalid.id = invalid.data_toast +'-'+ timestamp;
        
        createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
        $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');

        e.preventDefault();
        return false;
    }

    relatedModal = e.target.closest('.modal');
    relatedCard = e.relatedTarget.closest('.card');

    let nama_bantuan, slot;
    if (e.relatedTarget.getAttribute('data-type') == 'reset') {
        slot = tr.querySelector('td:first-child>b').innerText;
        if (tr.querySelector('a>span') != null) {
            nama_bantuan = tr.querySelector('a>span').innerText;
        }
        e.target.querySelector('a').innerText = nama_bantuan;
        if (document.querySelector('#modalKonfirmasiAksiBanner p>span+b') == null) {
            const b = document.createElement('b'),
                  bText = document.createTextNode(slot);
                  b.classList.add('ml-2')
            b.appendChild(bText);

            document.querySelector('#modalKonfirmasiAksiBanner p:first-of-type').appendChild(b);
        }
        document.querySelector('#modalKonfirmasiAksiBanner p>span').innerText = e.relatedTarget.getAttribute('data-type');
    } else {
        document.querySelector('#modalKonfirmasiAksiBanner p>span').innerText = 'ng' + e.relatedTarget.getAttribute('data-type');
    }

    document.querySelector('#modalKonfirmasiAksiBanner p>span').setAttribute('data-type', e.relatedTarget.getAttribute('data-type'));
}).on('hidden.bs.modal', function(e) {
    objectBanner = {};
    relatedModal = {};
});

let fetchData = function (url, data, root, f) {
    
    // Fetch with token
    fetch(url, {
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
    .then(function (response) {
        document.querySelector('body').setAttribute('data-token', response.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });

        switch (f) {
            case 'update-banner':
                fetchUpdateBanner(root, response);
                break;
            case 'create-banner':
                fetchCreateBanner(root, response);
                break;
            case 'read-banner':
                fetchReadBanner(root, response, data);
                break;
            case 'get-banner':
                fetchGetBanner(root, response);
                break;
            case 'reset-banner':
                fetchResetBanner(root, response);
                break;
            default:
                break;
        }

    })
};

let resetDataSelect2 = function(el, data) {
    el.html('');

    let dataAdapter = el.data('select2').dataAdapter;
    dataAdapter.addOptions(dataAdapter.convertToOptions(data));
};

let fetchGetBanner = function(root, response) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    if (response.feedback != null) {
        const data = response.feedback.data,
              defaultSelectBantuan = [{ id: data.id, text: data.text, status: data.status }]; 
        
        resetDataSelect2($('#id-bantuan'), defaultSelectBantuan);

        objectBanner.id_bantuan = data.id;
        objectBanner.nama_bantuan = data.text;
        objectBanner.status = data.status;
    }

    root.querySelectorAll('select').forEach(sl => {
        if (sl.parentElement.classList.contains('is-invalid')) {
            sl.parentElement.classList.remove('is-invalid');
            sl.parentElement.querySelector('label').removeAttribute('data-label-after');
            sl.classList.remove('is-invalid');
        }
    });
};

let fetchCreateBanner = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        if (response.toast != null) {
            if (response.toast.feedback.message == "Id Bantuan tidak ditemukan") {
                let target = root.querySelector('#input-id-bantuan');
                target.parentElement.classList.add('is-invalid');
                target.classList.add('is-invalid');
                root.querySelector('label[for="input-id-bantuan"').setAttribute('data-label-after', 'tidak valid');
            }
        }
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    const tr = '<tr data-id-banner="'+ response.feedback.data.id_banner +'"><td class="pr-0 text-center"><b>'+ response.feedback.data.slot +'</b></td><th scope="row"><div class="d-flex flex-column"><a href="'+ ((response.feedback.data.id_bantuan == null) ? 'javascript::void(0);' : '/admin/bantuan/data/' + response.feedback.data.id_bantuan ) +'" class="font-weight-bolder"><span>'+ response.feedback.data.nama_bantuan +'</span></a><span class="mb-0">'+ response.feedback.data.create_at +'</span></div></th><td><div class="d-block"><div><span>Campaign</span></div><span class="small badge '+ response.feedback.data.status.class +' text-black-50 text-capitalize">'+ response.feedback.data.status.text +'</div></td><td class="text-right"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="javascript::void(0);" role="button" data-toggle="dropdown"aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a href="/" class="dropdown-item font-weight-bold banner-peek">Lihat Banner</a><a class="dropdown-item font-weight-bold text-warning" href="javascript::void(0);" data-toggle="modal" data-target="#modalFormBanner" data-type="update">Ubah Banner</a><a class="dropdown-item font-weight-bold text-danger" href="javascript::void(0);" data-toggle="modal" data-target="#modalKonfirmasiAksiBanner" data-type="reset">Reset</a></div></div></td></tr>';
    relatedModal.querySelector('tbody').insertAdjacentHTML('beforeend', tr);

    if (!response.error) {
        if (response.feedback.data.slot == 8) {
            document.querySelector('.btn[data-target="#modalFormBanner"]').remove();
        }
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            $('#id-bantuan').val(0).trigger('change');
            setTimeout(() => {
                if (relatedModal.querySelector('tbody>tr[data-id-banner="'+ response.feedback.data.id_banner +'"]') != null) {
                    relatedModal.querySelector('tbody>tr[data-id-banner="'+ response.feedback.data.id_banner +'"]').classList.add('highlight');
                    setTimeout(()=> {
                        if (relatedModal.querySelector('tbody>tr.highlight') != null) {
                            relatedModal.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            },100);
        },0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
    // root.querySelector('.disabled').classList.remove('disabled');
};

let fetchResetBanner = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
    const trEl = relatedCard.querySelector('tbody>tr[data-id-banner="'+ response.feedback.data.id_banner +'"]');
    if (trEl != null) {
        const th = trEl.querySelector('th');
        th.innerHTML = '';
        if (trEl.querySelector('a[data-type="reset"]') != null) {
            trEl.querySelector('a[data-type="reset"]').remove();
        }
        trEl.querySelector('td:nth-of-type(2)').innerHTML = '';
        trEl.querySelector('td:nth-of-type(2)').insertAdjacentHTML('beforeend','<span class="small badge badge-primary text-black-50 text-capitalize">Kosong</span>');
        trEl.classList.add('highlight');
        setTimeout(()=> {
            relatedCard.querySelector('tbody>tr.highlight').classList.remove('highlight');
        }, 3000);
    }
    $(root).modal('hide');
};

let fetchUpdateBanner = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    } else {
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            setTimeout(() => {
                const trEl = relatedModal.querySelector('tbody>tr[data-id-banner="'+ response.feedback.data.id_banner +'"]');
                if (trEl != null) {
                    trEl.classList.add('highlight');
                    if (trEl.querySelector('th a') == null) {
                        const divA = '<div class="d-flex flex-column"><a href="/admin/bantuan/data/'+ response.feedback.data.id_bantuan +'" class="font-weight-bolder"><span>'+ response.feedback.data.nama_bantuan +'</span></a><span class="mb-0">'+ response.feedback.data.modified_at +'</span></div>';
                        trEl.querySelector('th').insertAdjacentHTML('beforeend', divA);
                    } else {
                        trEl.querySelector('th a').setAttribute('href','/admin/bantuan/data/'+ response.feedback.data.id_bantuan);
                        trEl.querySelector('th a span').innerText = response.feedback.data.nama_bantuan;
                    }
                    let tdStatus = trEl.querySelector('td:nth-of-type(2)');
                    if (tdStatus.querySelector('span.badge') != null) {
                        const badgeSpan = '<div class="d-block"><div><span>Campaign</span></div><span class="small badge '+ response.feedback.data.status.class +' text-black-50 text-capitalize">'+ response.feedback.data.status.text +'</span></div>';
                        tdStatus.insertAdjacentHTML('beforeend', badgeSpan);
                        tdStatus.querySelector('div.d-block:first-of-type').remove();
                    }
                    if (trEl.querySelector('a[data-type="reset"]') == null) {
                        aUpdate = '<a class="dropdown-item font-weight-bold text-danger" href="#" data-toggle="modal" data-target="#modalKonfirmasiAksiBanner" data-type="reset">Reset</a>';
                        trEl.querySelector('a[data-type="update"]').insertAdjacentHTML('afterend', aUpdate);
                    }
                    setTimeout(()=> {
                        if (relatedModal.querySelector('tbody>tr.highlight') != null) {
                            relatedModal.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            }, 100);
        }, 0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
};

const peekList = document.querySelector('table tbody');
peekList.addEventListener('click', function(e) {
    if (e.target.classList.contains('banner-peek')) {
        if (e.target.closest('td').previousElementSibling.querySelector('span.badge').innerText.toLowerCase() == 'aktif') {
            let id_banner = e.target.closest('tr').getAttribute('data-id-banner');
            localStorage.setItem("banner", id_banner);
        }
    }
});