defaultOptions.placeholder = 'Isi content campaign ...';
defaultOptions.modules.toolbar.container = [
    ['image','video']
];

let qEditor = editor('#editor'),
    relatedCard,
    dataBantuan = {},
    route = window.location.pathname.split('/')[1];

qEditor.container.querySelector('.ql-editor').addEventListener('keydown', function(e) {
    e.preventDefault();
    return false;
});

let formatSelected = function(objectSelected) {
    const label = objectSelected.element.closest('select').parentElement.querySelector('label');

    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '';

    if (label != null) {
        $elSelected = label.outerHTML;
    }

    if (objectSelected.tag == null) {
        $elSelected = $elSelected + '<div class="font-weight-normal">' + objectSelected.text + '</div>';
    } else {
        $elSelected = $elSelected + '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-normal">' + objectSelected.text + '</span></div><div>#'+ objectSelected.tag +'</div></div>';
    }

    return $elSelected;
};

function formatResult(result) {
    if (result.loading) {
        return result.text;
    }
    let $result;
    if (result.tag == null) {
        $result = '<div class="font-weight-bolder">' + result.text + '</div>';
    } else {
        $result = '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-normal">' + result.text + '</span></div><div>#'+ result.tag +'</div></div>';
    }
    return $result;
};

let resetDataSelect2 = function(el, data) {
    el.html('');

    let dataAdapter = el.data('select2').dataAdapter;
    dataAdapter.addOptions(dataAdapter.convertToOptions(data));
};

$('#id-bantuan').select2({
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, },
    ajax: {
        url: '/'+ route +'/campaign/fetch/bantuan',
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
            Object.assign(params, objectCampaign);
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

            data = response.feedback.data;
            
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
}).on('select2:close', function () {
    dataBantuan = {};
});

$('#modalLandingPage').on('hidden.bs.modal', function(e) {
    $(this).find('input.form-control').val('');
    qEditor.deleteText(0, qEditor.getLength());
    if (objectCampaign.mode == 'update') {
        document.querySelector('#id-bantuan').insertAdjacentHTML('afterbegin','<option value="0" selected disabled hidden>Pilih salah satu</option>');
        $('#id-bantuan').val(0).trigger('change');
    }
    objectCampaign = {};
}).on('show.bs.modal', function(e) {
    relatedCard = e.relatedTarget.closest('.card');
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
            'id_campaign': e.relatedTarget.closest('tr').getAttribute('data-id-campaign'),
            'token': body.getAttribute('data-token')
        };

        objectCampaign.id_campaign = data.id_campaign;
        
        // fetchGetDataCampaign
        fetchData('/'+ route +'/campaign/fetch/get', data, e.target, 'get-data-campaign');
        objectCampaign.mode = 'update';
    } else {
        objectCampaign.mode = 'create';
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
    switch (objectCampaign.mode.toLowerCase()) {
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
                    message: 'TR id-campaign not found'
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
        campaign: {
            id_bantuan: id_bantuan,
            isi: Delta
        },
        token: document.querySelector('body').getAttribute('data-token')
    };

    if (objectCampaign.mode == 'update') {
        if (data.campaign.id_bantuan == objectCampaign.id_bantuan) {
            delete data.campaign.id_bantuan;
        }

        objectCampaign.isi = new DOMParser().parseFromString(objectCampaign.isi, "text/html").querySelector('body').innerText;
        if (objectCampaign.isi == JSON.stringify(data.campaign.isi)) {
            delete data.campaign.isi;
        }

        if (!Object.keys(data.campaign).length) {
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'invalid-feedback',
                feedback: {
                    message: 'Belum ada perubahan data yang berganti'
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

    if (objectCampaign.id_campaign != null) {
        data.campaign.id_campaign = objectCampaign.id_campaign;
    }

    if (data.campaign.isi != null) {
        if (data.campaign.isi.ops.slice(-1).insert === '\n') {
            data.campaign.isi.ops.pop();
        }
    }

    // fetchCreateCampaign OR fetchUpdateCampaign
    fetchData('/'+ route +'/campaign/fetch/'+ objectCampaign.mode, data, root, objectCampaign.mode+'-campaign');
    // $('#'+root.id).modal('hide');
};

document.querySelector('#buat-campaign[type="submit"]').addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction));

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
            case 'update-campaign':
                fetchUpdateCampaign(root, response);
                break;
            case 'create-campaign':
                fetchCreateCampaign(root, response);
                break;
            case 'gets-campaign':
                fetchGetsCampaign(root, response, data, url);
                break;
            case 'get-data-campaign':
                fetchGetDataCampaign(root, response);
                break;
            case 'update-aktif-campaign':
                fetchUpdateAktifCampaign(root, response);
                break;
            default:
                break;
        }
    })
};

// timeAgoRuns('table span[data-modified-value]','data-modified-value','data-modified-value-', 1000, 'tr','data-id-campaign');

let fetchUpdateAktifCampaign = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    const trEl = relatedCard.querySelector('tbody>tr[data-id-campaign="'+ response.feedback.data.id_campaign +'"]');
    if (trEl != null) {
        const badge = trEl.querySelector('td[data-status]>span');
        badge.innerText = response.feedback.data.aktif.text;
        badge.setAttribute('class','badge '+response.feedback.data.aktif.class);
        trEl.querySelector('span[data-modified-value]').setAttribute('data-modified-value',response.feedback.data.modified_at);
        timeAgoRuns(trEl.querySelectorAll('span[data-modified-value]'),'data-modified-value','data-modified-value-', 60000, 'tr','data-id-campaign');
        trEl.querySelector('span.badge[data-aktif]').setAttribute('data-aktif', response.feedback.data.aktif.value);
        trEl.querySelector('[data-type="update-aktif"]').innerText = (response.feedback.data.aktif.value == 1 ? 'Non-aktifkan Campaign':'Aktifkan Campaign');
        trEl.classList.add('highlight');
        setTimeout(()=> {
            if (trEl.classList.contains('highlight')) {
                trEl.classList.remove('highlight');
            }
        }, 3000);
    }

    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
    $('#'+root.id).modal('hide');
};

let objectCampaign = {};
let fetchGetDataCampaign = function(root, response) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }
    const data = response.feedback.data,
          defaultSelectBantuan = [{ id: data.id, text: data.text, tag: data.tag }]; 
    resetDataSelect2($('#id-bantuan'), defaultSelectBantuan);
    if (data.isi != '') {
        qEditor.setContents(JSON.parse(new DOMParser().parseFromString(data.isi, "text/html").querySelector('body').innerText));
    }

    objectCampaign.id_bantuan = data.id;
    objectCampaign.isi = data.isi;

    const select = root.querySelectorAll('select');
    select.forEach(sl => {
        if (sl.parentElement.classList.contains('is-invalid')) {
            sl.parentElement.classList.remove('is-invalid');
            sl.parentElement.querySelector('label').removeAttribute('data-label-after');
            sl.classList.remove('is-invalid');
        }
    });

    if (qEditor.root.closest('.is-invalid') != null) {
        qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
    }
};

let fetchUpdateCampaign = function(root, response) {
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
                const trEl = relatedCard.querySelector('tbody>tr[data-id-campaign="'+ response.feedback.data.id_campaign +'"]');
                if (trEl != null) {
                    trEl.classList.add('highlight');
                    if (route == 'admin') {
                        trEl.querySelector('td a').setAttribute('href','/'+ route +'/bantuan/data/'+ response.feedback.data.id_bantuan);
                    } else {
                        trEl.querySelector('td a').setAttribute('href','/'+ route +'/campaign/'+ response.feedback.data.tag);
                    }
                    if (response.feedback.data.nama_bantuan != null) {
                        trEl.querySelector('td a span').innerText = response.feedback.data.nama_bantuan;
                    }
                    if (response.feedback.data.tag != null) {
                        if (trEl.querySelector('td .tag') == null) {
                            trEl.querySelector('td>.row').insertAdjacentHTML('beforeend','<div class="col-auto"><a href="/campaign/'+ response.feedback.data.tag +'"><span class="tag">#'+ response.feedback.data.tag +'</span></a></div>');
                        } else {
                            trEl.querySelector('td .tag').parentElement.setAttribute('href','/campaign/'+response.feedback.data.tag);
                            trEl.querySelector('td .tag').innerText = '#'+response.feedback.data.tag;
                        }
                    }
                    trEl.querySelector('td span[data-modified-value]').setAttribute('data-modified-value', response.feedback.data.modified_at);
                    stopPassed('data-modified-value');
                    trEl.querySelector('td span[data-modified-value]').innerText = ' beberapa saat yang lalu';
                    trEl.querySelector('.badge').setAttribute('class','badge font-weight-bolder '+response.feedback.data.status.class);
                    trEl.querySelector('.badge').innerText = response.feedback.data.status.text;

                    setTimeout(()=> {
                        if (relatedCard.querySelector('tbody>tr.highlight') != null) {
                            relatedCard.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            }, 100);
        }, 0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
};

let fetchCreateCampaign = function(root, response) {
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

    let rD = response.feedback.data;

    if (relatedCard.querySelector('.card-footer ul.pagination .page-link.page-item.active') != null) {
        relatedCard.querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    } else {
        relatedCard.querySelector('tbody').innerHTML = '';
        const tr = '<tr data-id-campaign="'+ reverseString(btoa(rD.id_campaign))+'"><td><div class="row justify-content-between"><div class="col-auto"><a href="'+ ((rD.tag == null) ? '/bantuan/detil/'+ rD.id_bantuan : '/bantuan/'+ rD.tag)+'" target="_blank" rel="noopener noreferrer" class="font-weight-bolder"><span>'+ rD.nama_bantuan+'</span></a></div>'+ ((rD.tag != null) ? '<div class="col-auto"><a href="/campaign/' +rD.tag+ '"><span class="tag">#' +rD.tag +'</span></a></div>' : '') +'</div><div class="row justify-content-between"><div class="col-auto"><span class="badge font-weight-bolder '+ rD.status.class +'">'+ rD.status.text +'</span></div><div class="col-auto"><span><i class="far fa-clock small"></i></span><small><span data-modified-value="'+ rD.modified_at +'">'+ rD.time_ago +'</span></small></div></div></td><td data-status="'+ rD.aktif.value +'"><span class="badge '+ rD.aktif.class +'" data-aktif="'+ rD.aktif.value +'">'+ rD.aktif.text +'</span></td><td><div class="media align-items-center gap-x-3"><div class="media-body"><div class="nama_jabatan mb-0 text-black-50 font-weight-bolder"><span>'+ rD.jabatan_author +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ rD.nama_author+'</span></div></div><div class="avatar rounded bg-transparent border overflow-hidden" data-id-author="'+ reverseString(btoa(rD.id_akun_maker ?? ''))+'"><img src="'+ rD.path_author+'" alt="'+ rD.nama_author+'" class="img-fluid"></div></div></td><td><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light mr-0 d-flex align-items-center justify-content-center" href="javascript::void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Drop Down Action Record"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" data-value="'+ reverseString(btoa(rD.id_campaign))+'">'+ ((rD.tag != null) ? '<a class="dropdown-item" href="/'+ route +'/campaign/'+ rD.tag +'">Hasil Campaign</a><a class="dropdown-item" href="/campaign/'+ rD.tag +'">Menuju Campaign</a>':'') +'<a class="dropdown-item '+ ((rD.aktif.value == '1') ? 'text-danger' : 'text-warning') +' font-weight-light" href="javascript:void(0);" data-toggle="modal" data-target="#modalKonfirmasiAksiCampaign" data-type="update-aktif">'+ ((rD.aktif.value == '1') ? 'Non-akifkan Campaign' : 'Aktifkan Campaign')+'</a><a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#modalLandingPage" data-type="update">Ubah Campaign</a></div></div></td></tr>';
        relatedCard.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        controlPaginationButton(0, $(relatedCard.querySelector('.pagination')), response.feedback.pages);
    }

    if (!response.error) {
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            $('#id-bantuan').val(0).trigger('change');
            setTimeout(() => {
                if (relatedCard.querySelector('tbody>tr[data-id-campaign="'+ reverseString(btoa(rD.id_campaign)) +'"]') != null) {
                    relatedCard.querySelector('tbody>tr[data-id-campaign="'+ reverseString(btoa(rD.id_campaign)) +'"]').classList.add('highlight');
                    setTimeout(()=> {
                        if (relatedCard.querySelector('tbody>tr.highlight') != null) {
                            relatedCard.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            },100);
        },0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
    // root.querySelector('.disabled').classList.remove('disabled');
};

let fetchGetsCampaign = function(root, response, data, url) {
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

    if (response.feedback.pages > 0 && data.length == 0 && response.feedback.total_record > 0) {
        document.querySelector('body').setAttribute('data-token', response.token);

        sentData.fields.active_page = result.feedback.fields.pages;
        sentData.token = response.token;
        
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        
        fetchData(url, sentData, root, 'gets-campaign');

        jumlah_halaman = response.feedback.pages;
        controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages, jumlah_halaman);
        return false;
    }

    // Jika jumlah_halaman berganti
    if (jumlah_halaman != response.feedback.pages) {
        jumlah_halaman = response.feedback.pages;
        $(root.querySelector('.pagination')).attr('data-pages', response.feedback.pages);
        if (sentData.fields.active_page == undefined) {
            controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.pages);
        } else {
            controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages);
        }
    }

    if (data.length < 1) {
        if (response.feedback.search == null) {
            let tr = '<tr data-zero="true"><td colspan="4"><span>Belum ada bantuan untuk campange ... </span></td></tr>'
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            eraseCookie('campaign');
            root.classList.remove('load');
            return false;
        }
        let tr = '<tr data-zero="true"><td colspan="4"><span>Data pencarian tidak ditemukan ... </span></td></tr>';
        root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
    } else {
        data.forEach(element => { 
            element.status = statusBantuan(element.status);
            element.aktif = ((element.aktif == '1') ? {class: 'badge-success',text:'aktif',value:'1'}:{class:'badge-danger',text:'non-aktif',value:'0'});
            let tr = '<tr data-id-campaign="'+ reverseString(btoa(element.id_campaign))+'"><td><div class="row justify-content-between"><div class="col-auto"><a href="'+ ((element.tag == null) ? '/bantuan/detil/'+ element.id_bantuan : '/bantuan/'+ element.tag)+'" target="_blank" rel="noopener noreferrer" class="font-weight-bolder"><span>'+ element.nama_bantuan+'</span></a></div>'+ ((element.tag != null) ? '<div class="col-auto"><a href="/campaign/'+ element.tag +'"><span class="tag">#' +element.tag +'</span></a></div>' : '') +'</div><div class="row justify-content-between"><div class="col-auto"><span class="badge font-weight-bolder '+ element.status.class +'">'+ element.status.text +'</span></div><div class="col-auto"><span><i class="far fa-clock small"></i></span><small><span data-modified-value="'+ element.modified_at+'"> '+ element.time_ago+'</span></small></div></div></td><td data-status="'+ element.aktif.value +'"><span class="badge '+ element.aktif.class +'" data-aktif="'+ element.aktif.value +'">'+ element.aktif.text +'</span></td><td><div class="media align-items-center gap-x-3"><div class="media-body"><div class="nama_jabatan mb-0 text-black-50 font-weight-bolder"><span>'+ element.jabatan_author+'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ element.nama_author+'</span></div></div><div class="avatar rounded bg-transparent border overflow-hidden" data-id-author="'+ reverseString(btoa(element.id_akun_maker ?? ''))+'"><img src="'+ element.path_author+'" alt="'+ element.nama_author+'" class="img-fluid"></div></div></td><td><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light mr-0 d-flex align-items-center justify-content-center" href="javascript::void(0);" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Drop Down Action Record"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" data-value="'+ reverseString(btoa(element.id_campaign))+'">'+ ((element.tag != null) ? '<a class="dropdown-item" href="/'+ route +'/campaign/'+ element.tag +'">Hasil Campaign</a><a class="dropdown-item" href="/campaign/'+ element.tag +'">Menuju Campaign</a>':'') +'<a class="dropdown-item '+ ((element.aktif.value == '1') ? 'text-danger' : 'text-warning') +' font-weight-light" href="javascript:void(0);" data-toggle="modal" data-target="#modalKonfirmasiAksiCampaign" data-type="update-aktif">'+ ((element.aktif.value == '1') ? 'Non-akifkan Campaign' : 'Aktifkan Campaign')+'</a><a class="dropdown-item" href="javascript:void(0);" data-toggle="modal" data-target="#modalLandingPage" data-type="update">Ubah Campaign</a></div></div></td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        });
    }

    const lInfo = root.querySelector('#lebel');
    if (lInfo != null) {
        lInfo.querySelector('#jumlah-data').innerText = data.length;
        lInfo.querySelector('#total-data').innerText = response.feedback.total_record;

        if (response.feedback.search != null) {
            if (lInfo.querySelector('#hasil-penelusuran') != null) {
                lInfo.querySelector('#hasil-penelusuran span').innerText = response.feedback.total_record;
            } else {
                lInfo.insertAdjacentHTML('beforeend', '<small id="hasil-penelusuran"><span class="text-orange">'+ response.feedback.total_record +'</span> pencarian ditemukan</small>');
            }
        } else {
            if (lInfo.querySelector('#hasil-penelusuran') != null) {
                lInfo.querySelector('#hasil-penelusuran').remove();
            }
        }
    }

    cookieValue.active_page = response.feedback.active_page;
    cookieValue.pages = response.feedback.pages;
    cookieValue.limit = response.feedback.limit;
    if (response.feedback.search != null) {
        cookieValue.search = response.feedback.search;
    }
    let expiry = new Date();
    expiry.setTime(expiry.getTime() + (60 * 60 * 1000)); // 1 hour
    updateCookie('campaign', btoa(JSON.stringify(cookieValue)), window.location.pathname, expiry);

    root.classList.remove('load');
};

let fetchRead = function(selectorElment) {
    let data = {
        token: body.getAttribute('data-token'),
        fields: {
            active_page: 1
        }
    };

    if (getCookie('campaign') != null) {
        const thisCookie = atob(decodeURIComponent(getCookie('campaign')));
        data.fields = {
            active_page: thisCookie.active_page
        };
        if (thisCookie.search != null) {
            data.fields.search = thisCookie.search;
        }
    }

    // fetchGetsCampaign
    fetchData('/'+ route +'/campaign/fetch/gets', data, selectorElment, 'gets-campaign');
};

// fetchRead(document.getElementById('list-campaign'));

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
    // fetchGetsCampaign
    fetchData('/'+ route +'/campaign/fetch/gets', data, root, 'gets-campaign');
    e.preventDefault();
};

let oldPage;
let clickPagination = function(e) {
    relatedCard = e.target.closest('.card');
    relatedCard.classList.add('load');
    if (oldPage == e.target.getAttribute('data-id')) {
        if (!delayTimer) {
            clickPageLink(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
            if (relatedCard.classList.contains('load')) {
                relatedCard.classList.remove('load');
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

if (getCookie('campaign') != null) {
    cookieValue = JSON.parse(atob(decodeURIComponent(getCookie('campaign'))));
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
    
    if (oldSearchValue == sValue || oldSearchValue == undefined && sValue == '') {
        clearTimeout(delayTimer);
        e.target.closest('.card').classList.remove('load');
        return false;
    }
    
    if (oldSearchValue != undefined || e.target.value.length > 0) {
        e.target.closest('.card').classList.add('load');
        relatedCard = e.target.closest('.card');
    }

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

        if (!sValue.length && root.querySelector('#hasil-penelusuran') != null) {
            root.querySelector('#hasil-penelusuran').remove();
        }

        if (oldSearchValue != data.fields.search) {
            oldSearchValue = data.fields.search;
        } else {
            root.classList.remove('load');
            return false;
        }
        
        // console.log(data);
        // fetchGetsCampaign
        fetchData('/'+ route +'/campaign/fetch/gets', data, root, 'gets-campaign');
    }, 1000);
};

document.querySelectorAll('input[name="search"]').forEach(input => {
    input.addEventListener('keyup', searchFunction);
});

$('#modalKonfirmasiAksiCampaign').on('show.bs.modal', function(e) {
    let tr = e.relatedTarget.closest('tr');
    if (tr != null && tr.getAttribute('data-id-campaign') != null) {
        objectCampaign.id_campaign = tr.getAttribute('data-id-campaign');
    } else {
        let currentDate = new Date(),
            timestamp = currentDate.getTime(); 

        let invalid = {
            error: true,
            data_toast: 'invalid-show-modal-feedback',
            feedback: {
                message: 'TR id-campaign not found'
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

    let tag;
    if (e.relatedTarget.getAttribute('data-type') == 'update-aktif') {
        if (tr.querySelector('span.tag') != null) {
            tag = tr.querySelector('span.tag').innerText;
        }
        e.target.querySelector('#tag').innerText = tag;
        e.target.querySelector('a').setAttribute('href', '/campaign/'+tag.replace('#',''));
        document.querySelector('#modalKonfirmasiAksiCampaign p>span').innerText = e.relatedTarget.innerText;
    } else {
        document.querySelector('#modalKonfirmasiAksiCampaign p>span').innerHTML = '<b>'+e.relatedTarget.getAttribute('data-type')+'</b>';
    }

    document.querySelector('#modalKonfirmasiAksiCampaign p>span').setAttribute('data-type', e.relatedTarget.getAttribute('data-type'));
    objectCampaign.aktif = (tr.querySelector('span.badge[data-aktif]').getAttribute('data-aktif') == '1' ? '0':'1');
    if (objectCampaign.aktif == '0') {
        text = 'Ya, non-aktifkan';
    } else {
        text = 'Aktifkan';
    }
    relatedCard.querySelector('[type="submit"]').innerText = text;
}).on('hidden.bs.modal', function(e) {
    objectCampaign = {};
    relatedCard = {};
});

let submitAction = document.querySelector('#modalKonfirmasiAksiCampaign [type="submit"]');
submitAction.addEventListener('click', function(e) {
    let data = {
        'token': body.getAttribute('data-token'),
        'id_campaign': objectCampaign.id_campaign,
        'fields': {
            'aktif': objectCampaign.aktif
        }
    };

    // console.log(data);
    // fetchUpdateAktifCampaign
    fetchData('/'+ route +'/campaign/fetch/update-aktif', data, relatedCard, 'update-aktif-campaign');
});