let qEditor = editor('#editor'),
    relatedModal,
    relatedCard,
    dataBantuan = {};

let formatSelected = function(objectSelected) {
    const label = objectSelected.element.closest('select').parentElement.querySelector('label');

    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '';

    if (label != null) {
        $elSelected = label.outerHTML;
    }

    $elSelected = $elSelected + '<div class="font-weight-normal">' + objectSelected.text + '</div>';

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
        $result = '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-normal">' + result.text + '</span></div></div>';
    }
    return $result;
};

let resetDataSelect2 = function(el, data) {
    el.html('');

    let dataAdapter = el.data('select2').dataAdapter;
    dataAdapter.addOptions(dataAdapter.convertToOptions(data));
};

function selectLabelBantuanDeskripsi(array) {
    return Object.values(array.reduce((accu, { id: id, group_by: text, nama, additional_text }) => {
        (accu[text] ??= { text, children: [] }).children.push({ id, text: nama, additional_text });
        return accu;
    }, {}));
};

$('#id-bantuan').select2({
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, },
    ajax: {
        url: '/admin/fetch/ajax/bantuan/deskripsi-selengkapnya',
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
            Object.assign(params, objectDeskripsi);
            // console.log(params);
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

            let data = selectLabelBantuanDeskripsi(response.feedback.data);
            
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
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

$('#modalFormDeskripsiSelengkapnya').on('hidden.bs.modal', function(e) {
    $(this).find('input.form-control').val('');
    qEditor.deleteText(0, qEditor.getLength());
    if (objectDeskripsi.mode == 'update') {
        document.querySelector('#id-bantuan').insertAdjacentHTML('afterbegin','<option value="0" selected disabled hidden>Pilih salah satu</option>');
        $('#id-bantuan').val(0).trigger('change');
    }
    objectDeskripsi = {};
}).on('show.bs.modal', function(e) {
    relatedModal = e.relatedTarget.closest('.card');
    if (e.relatedTarget.getAttribute('data-type') == 'update') {
        if (e.relatedTarget.closest('tr') == null) {
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
        let data = {
            'id_deskripsi': e.relatedTarget.closest('tr').getAttribute('data-id-deskripsi'),
            'token': body.getAttribute('data-token')
        };

        objectDeskripsi.id_deskripsi = data.id_deskripsi;
        
        // fetchGetDeskripsiSelengkapnya
        fetchData('/admin/fetch/get/deskripsi-selengkapnya', data, e.target, 'get-deskripsi');
        objectDeskripsi.mode = 'update';
    } else {
        objectDeskripsi.mode = 'create';
    }
});

let qEList = document.querySelectorAll('.ql-editor');
qEList.forEach(qe => {
    qe.addEventListener('keydown', function(e) {
        if (this.classList.contains('ql-blank') && qe.closest('.ql.is-invalid') != null) {
            qe.closest('.ql.is-invalid').classList.remove('is-invalid');
        }
    });
});

let clickFunction = function(e) {
    let c_error = 0,
        nameList = e.target.closest('.modal').querySelectorAll('[name]'),
        Delta = qEditor.getContents(),
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

    if (Delta.ops.length === 1) {
        if (Delta.ops[0].insert == '\n') {
            qEditor.container.parentElement.classList.add('is-invalid');
            return false;
        }
    }

    if (c_error > 0) {
        return false;
    }
   
    // e.target.classList.add('disabled');
    let id_bantuan = root.querySelector('#id-bantuan').value;
    switch (objectDeskripsi.mode.toLowerCase()) {
        case 'create':
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
                    message: 'TR id-deskripsi not found'
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

    let data = {
        deskripsi: {
            id_bantuan: id_bantuan,
            judul: root.querySelector('[name="judul"]').value,
            isi: Delta
        },
        token: document.querySelector('body').getAttribute('data-token')
    };

    if (objectDeskripsi.mode == 'update') {
        if (data.deskripsi.id_bantuan == objectDeskripsi.id_bantuan) {
            delete data.deskripsi.id_bantuan;
        }

        if (data.deskripsi.judul == objectDeskripsi.judul) {
            delete data.deskripsi.judul;
        }

        if (JSON.stringify(Delta) == objectDeskripsi.isi) {
            delete data.deskripsi.isi;
        }

        if (!Object.keys(data.deskripsi).length) {
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

    if (objectDeskripsi.id_deskripsi != null) {
        data.deskripsi.id_deskripsi = objectDeskripsi.id_deskripsi;
    }

    if (data.deskripsi.isi.ops.slice(-1).insert === '\n') {
        data.deskripsi.isi.ops.pop();
    }

    // console.log(data);
    // fetchCreateDeskripsiSelengkapnya OR fetchUpdateDeskripsiSelengkapnya
    fetchData('/admin/fetch/'+ objectDeskripsi.mode +'/bantuan/deskripsi-selengkapnya', data, root, objectDeskripsi.mode +'-deskripsi-selengkapnya');
    // $('#'+root.id).modal('hide');
};

document.querySelector('#buat-deskripsi-selengkapnya[type="submit"]').addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction));

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
            case 'update-deskripsi-selengkapnya':
                fetchUpdateDeskripsiSelengkapnya(root, response);
                break;
            case 'create-deskripsi-selengkapnya':
                fetchCreateDeskripsiSelengkapnya(root, response);
                break;
            case 'read-deskripsi-selengkapnya':
                fetchReadDeskripsiSelengkapnya(root, response, data);
                break;
            case 'get-deskripsi':
                fetchGetDeskripsiSelengkapnya(root, response);
                break;
            case 'reset-deskripsi':
                fetchResetDeskripsi(root, response);
                break;
            default:
                break;
        }

    })
};

let fetchResetDeskripsi = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
    const trEl = relatedCard.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]');
    if (trEl != null) {
        const title = trEl.querySelector('td[data-title="true"]>div');
        if (title.querySelector('span.badge') == null) {
            title.insertAdjacentHTML('afterend','<span class="small badge badge-warning text-black-50 text-capitalize">Kosong</span>');
        }
        trEl.classList.add('highlight');
        setTimeout(()=> {
            relatedCard.querySelector('tbody>tr.highlight').classList.remove('highlight');
        }, 3000);
    }
    $(root).modal('hide');
};

let objectDeskripsi = {};
let fetchGetDeskripsiSelengkapnya = function(root, response) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }
    const data = response.feedback.data,
          defaultSelectBantuan = [{ id: data.id_bantuan, text: data.nama_bantuan }]; 
    resetDataSelect2($('#id-bantuan'), defaultSelectBantuan);
    root.querySelector('input#input-judul').value = data.judul;
    root.querySelector('.current-length').innerText = data.judul.length;
    if (data.isi != '') {
        qEditor.setContents(JSON.parse(data.isi));
    }

    objectDeskripsi.id_bantuan = data.id_bantuan;
    objectDeskripsi.judul = data.judul;
    objectDeskripsi.isi = data.isi;

    const select = root.querySelectorAll('select');
    select.forEach(sl => {
        if (sl.parentElement.classList.contains('is-invalid')) {
            sl.parentElement.classList.remove('is-invalid');
            sl.parentElement.querySelector('label').removeAttribute('data-label-after');
            sl.classList.remove('is-invalid');
        }
    });

    const input = root.querySelector('input#input-judul');

    if (input.parentElement.classList.contains('is-invalid') && input.value.length) {
        input.parentElement.classList.remove('is-invalid');
        input.classList.remove('is-invalid');
        input.parentElement.querySelector('label').removeAttribute('data-label-after');
    }

    if (qEditor.root.closest('.is-invalid') != null) {
        qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
    }
};

let fetchUpdateDeskripsiSelengkapnya = function(root, response) {
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
                const trEl = relatedModal.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]');
                if (trEl != null) {
                    trEl.classList.add('highlight');
                    trEl.querySelector('th a').setAttribute('href','/admin/bantuan/data/'+ response.feedback.data.id_bantuan);
                    trEl.querySelector('th a span').innerText = response.feedback.data.nama_bantuan;
                    if (trEl.querySelector('td[data-title="true"] span.badge') != null) {
                        trEl.querySelector('td[data-title="true"] span.badge').remove();
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

let fetchCreateDeskripsiSelengkapnya = function(root, response) {
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

    if (relatedModal.querySelector('.card-footer ul.pagination .page-link.page-item.active') != null) {
        relatedModal.querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    } else {
        relatedModal.querySelector('thead tr').insertAdjacentHTML('beforeend', '<th scope="col" class="sort" data-sort="judul">Judul</th><th scope="col" class="fit"></th>');
        relatedModal.querySelector('tfoot tr').insertAdjacentHTML('beforeend', '<th scope="col" class="sort" data-sort="judul">Judul</th><th scope="col" class="fit"></th>');
        relatedModal.querySelector('tbody').innerHTML = '';
        const tr = '<tr data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"><th scope="row" class="fit"><div class="d-flex flex-column"><a href="'+ (response.feedback.data.id_bantuan == null ? '#' : '/admin/bantuan/data/' + response.feedback.data.id_bantuan) +'" class="font-weight-bolder"><span>'+ response.feedback.data.nama_bantuan +'</span></a><span class="mb-0">'+ response.feedback.data.create_at +'</span></div></th><td><div><span>'+ response.feedback.data.judul +'</span></div>'+ (response.feedback.data.isi_length != null ? '':'<span class="small badge badge-warning text-black-50 text-capitalize">Kosong</span>') +'</td><td class="text-right"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a class="dropdown-item font-weight-bold" href="/bantuan/detil/'+ response.feedback.data.id_bantuan +'/#deskripsi-selengkapnya-area">Lihat Isi</a><a class="dropdown-item font-weight-bold text-warning" href="#" data-toggle="modal" data-target="#modalFormDeskripsiSelengkapnya" data-type="update">Ubah Isi</a>'+ (response.feedback.data.id_bantuan == null ? '':'<a class="dropdown-item font-weight-bold text-danger" href="#" data-toggle="modal" data-target="#modalKonfirmasiAksiDeskripsi" data-type="reset">Reset</a>') +'</div></div></td></tr>';
        relatedModal.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        controlPaginationButton(0, $(relatedModal.querySelector('.pagination')), response.feedback.fields.pages);
    }

    if (!response.error) {
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            $('#id-bantuan').val(0).trigger('change');
            setTimeout(() => {
                if (relatedModal.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]') != null) {
                    relatedModal.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]').classList.add('highlight');
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

let fetchReadDeskripsiSelengkapnya = function(root, response, data) {
    if (response.error) {
        return false;
    }

    let sentData = data;

    root.querySelectorAll('tbody tr').forEach(el => {
        el.remove();
    });
    
    data = response.feedback.data;
    let cookieValue = {},
        jumlah_halaman = root.querySelector('.pagination').getAttribute('data-pages');

    if (response.feedback.fields.pages > 0 && data.length == 0 && response.feedback.total_record > 0) {
        document.querySelector('body').setAttribute('data-token', response.token);

        sentData.fields.active_page = result.feedback.fields.pages;
        sentData.token = response.token;
        
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        
        fetchData(url, sentData, root, 'read-deskripsi-selengkapnya');

        jumlah_halaman = response.feedback.fields.pages;
        controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.fields.pages, jumlah_halaman);
        return false;
    }

    // Jika jumlah_halaman berganti
    if (jumlah_halaman != response.feedback.fields.pages) {
        jumlah_halaman = response.feedback.fields.pages;
        $(root.querySelector('.pagination')).attr('data-pages', response.feedback.fields.pages);
        if (sentData.fields.active_page == undefined) {
            controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.fields.pages);
        } else {
            controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.fields.pages);
        }
    }

    if (data.length < 1) {
        if (response.feedback.fields.search == null) {
            let tr = '<tr data-zero="true"><td colspan="2"><span>Belum ada deskripsi selengkapnya yang ditulis untuk campange ... </span></td></tr>'
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            eraseCookie('deskripsi-selengkapnya');
            root.querySelector('table').classList.remove('load');
            return false;
        }
        let tr = '<tr data-zero="true"><td colspan="2"><span>Data pencarian tidak ditemukan ... </span></td></tr>';
        root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
    } else {
        data.forEach(elment => { 
            let tr = '<tr data-id-deskripsi="'+ elment.id_deskripsi +'"><th scope="row" class="fit"><div class="d-flex flex-column"><a href="'+ (elment.id_bantuan == null ? '#' : '/admin/bantuan/data/' + elment.id_bantuan) +'" class="font-weight-bolder"><span>'+ elment.nama_bantuan +'</span></a><span class="mb-0">'+ elment.create_at +'</span></div></th><td><div><span>'+ elment.judul +'</span></div>'+ (elment.isi_length != null ? '':'<span class="small badge badge-warning text-black-50 text-capitalize">Kosong</span>') +'</td><td class="text-right"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a class="dropdown-item font-weight-bold" href="/bantuan/detil/'+ elment.id_bantuan +'/#deskripsi-selengkapnya-area">Lihat Isi</a><a class="dropdown-item font-weight-bold text-warning" href="#" data-toggle="modal" data-target="#modalFormDeskripsiSelengkapnya" data-type="update">Ubah Isi</a>'+ (elment.id_bantuan == null ? '':'<a class="dropdown-item font-weight-bold text-danger" href="#" data-toggle="modal" data-target="#modalKonfirmasiAksiDeskripsi" data-type="reset">Reset</a>') +'</div></div></td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        });
    }

    cookieValue.active_page = response.feedback.fields.active_page;
    cookieValue.pages = response.feedback.fields.pages;
    cookieValue.limit = response.feedback.fields.limit;
    if (response.feedback.fields.search != null) {
        cookieValue.search = response.feedback.fields.search;
    }
    let expiry = new Date();
    expiry.setTime(expiry.getTime() + (60 * 60 * 1000)); // 1 hour
    updateCookie('deskripsi-selengkapnya', btoa(JSON.stringify(cookieValue)), window.location.pathname, expiry);

    root.querySelector('table').classList.remove('load');
};

let fetchRead = function(selectorElment) {
    let data = {
        token: body.getAttribute('data-token'),
        fields: {
            active_page: 1
        }
    };

    if (getCookie('deskripsi-selengkapnya') != null) {
        const thisCookie = atob(decodeURIComponent(getCookie('deskripsi-selengkapnya')));
        data.fields = {
            active_page: thisCookie.active_page
        };
        if (thisCookie.search != null) {
            data.fields.search = thisCookie.search;
        }
    }

    // fetchReadDeskripsiSelengkapnya
    fetchData('/admin/fetch/read/deskripsi/list', data, selectorElment, 'read-deskripsi-selengkapnya');
};

// fetchRead(document.getElementById('list-deskripsi-selengkapnya'));

let clickPageLink = function(e) {
    const root = e.target.closest('.card');
    let active_page = e.target.getAttribute('data-id'),
        limit = root.querySelector('tbody').getAttribute('data-limit'),
        token = document.querySelector('body').getAttribute('data-token');
            
    let data = {
        'token': token,
        'fields': {
            'active_page': active_page,
            'limit': limit
        }
    };

    const search = root.querySelector('input[name="search"]');

    if (search.value.length > 0) {
        data.fields.search = search.value;
    }

    // console.log(data);
    // fetchReadDeskripsiSelengkapnya
    fetchData('/admin/fetch/read/deskripsi/list', data, root, 'read-deskripsi-selengkapnya');
    e.preventDefault();
};

let oldPage;
let clickPagination = function(e) {
    relatedCard = e.target.closest('.card');
    relatedCard.querySelector('table').classList.add('load');
    if (oldPage == e.target.getAttribute('data-id')) {
        if (!delayTimer) {
            clickPageLink(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
            if (relatedCard.querySelector('table.load')) {
                relatedCard.querySelector('table').classList.remove('load');
            }
        }, 1000);
    } else {
        clickPageLink(e);
    }
    oldPage = e.target.getAttribute('data-id');
};

$('.pagination').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', clickPagination);

let delayTimer,
oldSearchValue;

if (getCookie('deskripsi-selengkapnya') != null) {
    cookieValue = JSON.parse(atob(decodeURIComponent(getCookie('deskripsi-selengkapnya'))));
    if (cookieValue.search != null) {
        oldSearchValue = cookieValue.search;
    }
    oldPage = cookieValue.active_page;
}

let searchFunction = function(e) {
    let sValue = '';
    if (e.target.value.length > 0) {
        sValue = e.target.value;
        if (isNumber(sValue)) {
            e.target.value = numberToPrice(e.target.value);
            sValue = e.target.value;
        }
    }
    e.target.closest('.card').querySelector('table').classList.add('load');
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = e.target.closest('.card');

        let limit = root.querySelector('tbody').getAttribute('data-limit'),
            token = document.querySelector('body').getAttribute('data-token');

        let data = {
            'token': token,
            'fields': {
                'limit': limit
            }
        };

        if (root.querySelector('.pagination .page-link.active') != null) {
            data.fields.active_page = root.querySelector('.pagination .page-link.active').getAttribute('data-id');
        }        

        if (e.target.value.length > 0) {
            data.fields.search = sValue;
        }

        if (oldSearchValue != data.fields.search) {
            oldSearchValue = data.fields.search;
        } else {
            return false;
        }
        
        // console.log(data);
        // fetchReadDeskripsiSelengkapnya
        fetchData('/admin/fetch/read/deskripsi/list', data, root, 'read-deskripsi-selengkapnya');
    }, 1000);
};

document.querySelectorAll('input[name="search"]').forEach(input => {
    input.addEventListener('keyup', searchFunction);
});

$('#modalKonfirmasiAksiDeskripsi').on('show.bs.modal', function(e) {
    let tr = e.relatedTarget.closest('tr');
    if (tr != null && tr.getAttribute('data-id-deskripsi') != null) {
        objectDeskripsi.id_deskripsi = tr.getAttribute('data-id-deskripsi');
    } else {
        let currentDate = new Date(),
            timestamp = currentDate.getTime(); 

        let invalid = {
            error: true,
            data_toast: 'invalid-show-modal-feedback',
            feedback: {
                message: 'TR id-deskripsi not found'
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

    let nama_bantuan, id_bantuan;
    if (e.relatedTarget.getAttribute('data-type') == 'reset') {
        if (tr.querySelector('a>span') != null) {
            nama_bantuan = tr.querySelector('a>span').innerText;
            id_bantuan = tr.querySelector('a').getAttribute('href').split('/').at(-1);
        }
        e.target.querySelector('a').innerText = nama_bantuan;
        e.target.querySelector('a').setAttribute('href', '/bantuan/detil/'+id_bantuan+'/#deskripsi-selengkapnya-area');
        document.querySelector('#modalKonfirmasiAksiDeskripsi p>span').innerText = e.relatedTarget.getAttribute('data-type');
    } else {
        document.querySelector('#modalKonfirmasiAksiDeskripsi p>span').innerText = 'ng' + e.relatedTarget.getAttribute('data-type');
    }

    document.querySelector('#modalKonfirmasiAksiDeskripsi p>span').setAttribute('data-type', e.relatedTarget.getAttribute('data-type'));
}).on('hidden.bs.modal', function(e) {
    objectDeskripsi = {};
    relatedModal = {};
});

let submitAction = document.querySelector('#modalKonfirmasiAksiDeskripsi [type="submit"]');
submitAction.addEventListener('click', function(e) {
    let data = {
        'token': body.getAttribute('data-token'),
        'id_deskripsi': objectDeskripsi.id_deskripsi
    };

    // fetchResetDeskripsi
    fetchData('/admin/fetch/'+ e.target.closest('.modal').querySelector('p>span').getAttribute('data-type') +'/deskripsi', data, relatedModal, 'reset-deskripsi');
});