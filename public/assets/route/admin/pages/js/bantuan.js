let qEditor = editor('#editor'),
    relatedModal,
    relatedTarget,
    objectDeskripsi = {},
    dataDeskripsi = {};

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
            
            if (dataDeskripsi.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataDeskripsi.search))) {
                params.offset = parseInt(dataDeskripsi.offset) + parseInt(dataDeskripsi.limit);
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
                dataDeskripsi.search = response.feedback.search;
            } else {
                delete dataDeskripsi.search;
            }
            dataDeskripsi.offset = response.feedback.offset;
            dataDeskripsi.record = response.feedback.record;
            dataDeskripsi.limit = response.feedback.limit;
            dataDeskripsi.load_more = response.feedback.load_more;
            let pagination = {
                more: dataDeskripsi.load_more
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

const modalKelolaSelengkapnya = document.getElementById('modalKelolaSelengkapnya');
const myKelolaSelengkapnya = new bootstrap.Modal(modalKelolaSelengkapnya);

$('#modalKelolaSelengkapnya').on('show.bs.modal', function (e) {
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

    const id_bantuan = e.relatedTarget.closest('tr').querySelector('[data-id]').getAttribute('data-id');

    let data = {
        'id_bantuan': id_bantuan,
        'token': body.getAttribute('data-token')
    };

    relatedTarget = e.relatedTarget.closest('tr');

    // fetchGetDeskripsiSelengkapnyaByBantuan
    fetchData('/admin/fetch/get/deskripsi-selengkapnya/by-bantuan', data, e.target, 'get-selengkapnya-by-bantuan')
}).on('hide.bs.modal', function (e) {
    e.target.querySelector('#id-bantuan').removeAttribute('disabled');
    const input = document.querySelectorAll('input');
    input.forEach(el => {
        el.value = '';
        if (el.parentElement.classList.contains('is-invalid')) {
            el.parentElement.classList.remove('is-invalid');
            el.classList.remove('is-invalid');
            el.parentElement.querySelector('label').removeAttribute('data-label-after');
        }
    });

    qEditor.deleteText(0, qEditor.getLength());

    if (qEditor.root.closest('.is-invalid') != null) {
        qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
    }

    objectDeskripsi = {};
});

// myKelolaSelengkapnya.show();

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
            case 'get-selengkapnya-by-bantuan':
                fetchGetDeskripsiSelengkapnyaByBantuan(root, response);
                break;
            case 'update-deskripsi-selengkapnya':
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                if (response.error == false) {
                    $('#modalKelolaSelengkapnya').modal('hide');
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
                    setTimeout(() => {
                        let trTerubah = relatedTarget.parentElement.querySelector('a[data-id="'+ response.feedback.data.id_bantuan +'"]');
                        console.log(trTerubah, response.feedback.data.id_bantuan);
                        if (trTerubah != null && relatedTarget.querySelector('a[data-id').getAttribute('data-id') != response.feedback.data.id_bantuan) {
                            trTerubah.closest('tr').classList.add('highlight');
                        }
                        relatedTarget.classList.add('highlight');
                        setTimeout(()=> {
                            relatedTarget.parentElement.querySelectorAll('tr.highlight').forEach(tr => {
                                tr.classList.remove('highlight');
                            });
                        }, 3000);
                    }, 100);
                } else {
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                        'delay': 10000
                    }).toast('show');
                    return false;
                }
                break;
            case 'create-deskripsi-selengkapnya':
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                if (response.error) {
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                        'delay': 10000
                    }).toast('show');
                    return false;
                } else {
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
                }
                break;
            default:
                break;
        }
    })
};

let fetchGetDeskripsiSelengkapnyaByBantuan = function(root, response) {
    const data = response.feedback.data,
          defaultSelectBantuan = [{ id: data.id_bantuan, text: data.nama_bantuan }]; 
    resetDataSelect2($('#id-bantuan'), defaultSelectBantuan);
    
    if (data.judul == '') {
        objectDeskripsi.mode = 'create';
        document.querySelector('#id-bantuan').setAttribute('disabled', 'true');
    } else {
        objectDeskripsi.mode = 'update';
        objectDeskripsi.id_deskripsi = data.id_deskripsi;
        objectDeskripsi.id_bantuan = data.id_bantuan;
        objectDeskripsi.judul = data.judul;
        objectDeskripsi.isi = data.isi;
        document.querySelector('#id-bantuan').insertAdjacentHTML('afterbegin','<option value="0" disabled hidden>Pilih salah satu</option>');
    }

    root.querySelector('#input-judul').value = data.judul;
    root.querySelector('#input-judul~.input-char-left .current-length').innerText = data.judul.length;

    if (data.isi != '') {
        qEditor.setContents(JSON.parse(data.isi));
    }
};

let clickFunction = function (e) {
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
                if (name.id == 'id-bantuan') {
                    return;
                }
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

    let data = {
        deskripsi: {
            id_bantuan: root.querySelector('#id-bantuan').value,
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

    console.log(objectDeskripsi.mode);
    console.log(data);
    // fetchCreateDeskripsiSelengkapnya OR fetchUpdateDeskripsiSelengkapnya
    fetchData('/admin/fetch/'+ objectDeskripsi.mode +'/bantuan/deskripsi-selengkapnya', data, root, objectDeskripsi.mode +'-deskripsi-selengkapnya');
};

const submitBtn = document.querySelectorAll('[type="submit"]').forEach(btn => {
    btn.addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction))
});