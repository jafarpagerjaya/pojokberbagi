const countVisitor = document.querySelectorAll('table td span[data-count-up-value]');
counterUp(countVisitor, counterSpeed);

let cbl = document.querySelectorAll('table tbody td[data-checkbox] input[type="checkbox"]');
let bml = document.querySelectorAll('button[data-value].multi');
let bsl = document.querySelectorAll('button[data-value].singgle');

checkboxForBtn(cbl, bml, bsl, 'data-id-artikel','tr', selectedId);

defaultOptions.placeholder = 'Isi content artikel ...';
let qEditor = editor('#editor');

let objectArtikel = {};
let clickFunction = function(e) {
    let c_error = 0,
        nameList = e.target.closest('.modal').querySelectorAll('[name]'),
        Delta,
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

    if (objectArtikel.mode.toLowerCase() == 'create' || objectArtikel.mode.toLowerCase() == 'update') {
        Delta = qEditor.getContents();

        if (Delta.ops.length === 1) {
            if (Delta.ops[0].insert == '\n') {
                qEditor.container.parentElement.classList.add('is-invalid');
                return false;
            }
        }
    }

    if (c_error > 0) {
        return false;
    }
   
    // e.target.classList.add('disabled');
    switch (objectArtikel.mode.toLowerCase()) {
        case 'create':
        break;

        case 'update':
        break;

        case 'reset':
        break;

        default:
            let currentDate = new Date(),
                timestamp = currentDate.getTime(),
                invalid = {
                error: true,
                data_toast: 'invalid-show-modal-feedback',
                feedback: {
                    message: 'object Mode "'+ objectArtikel.mode +'" Unrecognize'
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
        token: document.querySelector('body').getAttribute('data-token'),
        artikel: {}
    };

    if (objectArtikel.mode == 'update') {
        objectArtikel.isi = new DOMParser().parseFromString(objectArtikel.isi, "text/html").querySelector('body').innerText;
        if (objectArtikel.isi == JSON.stringify(Delta)) {
            delete data.artikel.isi;
        } else {
            data.artikel.isi = Delta;
        }

        if (objectArtikel.judul == root.querySelector('[name="judul"]').value) {
            delete data.artikel.judul;
        } else {
            data.artikel.judul = root.querySelector('[name="judul"]').value;
        }

        if (!Object.keys(data.artikel).length) {
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'feedback',
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

        if (document.querySelectorAll('.toast[data-toast="feedback"]').length) {
            $('.toast[data-toast="feedback"]').toast('hide');
        }
    } else if (objectArtikel.mode == 'reset') {
        delete data.artikel.isi;
        delete data.artikel.judul;

        if (objectArtikel.id_artikel.length == 1) {
            if (objectArtikel.reset_status != root.querySelector('input#input-reset').checked) {
                data.artikel.reset = root.querySelector('input#input-reset').checked;
            }
    
            if (objectArtikel.aktif != root.querySelector('input#input-aktif').checked) {
                data.artikel.aktif = root.querySelector('input#input-aktif').checked;
            }
            
            if (!Object.keys(data.artikel).length) {
                let currentDate = new Date(),
                    timestamp = currentDate.getTime(); 
    
                let invalid = {
                    error: true,
                    data_toast: 'feedback',
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
        } else {
            data.artikel.reset = root.querySelector('input#input-reset').checked;
            data.artikel.aktif = root.querySelector('input#input-aktif').checked;
        }
    } else if (objectArtikel.mode == 'create') {
        data.artikel = {
            judul: root.querySelector('[name="judul"]').value,
            isi: Delta
        };
    }

    if (data.artikel.isi != null) {
        if (data.artikel.isi.ops.slice(-1).insert === '\n') {
            data.artikel.isi.ops.pop();
        }
    }

    if (objectArtikel.id_artikel != null) {
        data.artikel.id_artikel= objectArtikel.id_artikel;
    }

    // console.log(data);
    // fetchCreateArtikel OR fetchUpdateArtikel Or fetchResetArtikel
    fetchData('/admin/publikasi/fetch/'+ objectArtikel.mode+'/artikel', data, root, objectArtikel.mode+'-artikel');
    // $('#'+root.id).modal('hide');
};

document.querySelectorAll('button[type="submit"]').forEach(btn => {
    btn.addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction));
});

document.querySelector('#modalResetArtikel .modal-body').addEventListener('click', function(e) {
    if (e.target.tagName == 'INPUT' && e.target.id == 'input-reset' && e.target.checked == true && e.currentTarget.querySelector('#input-aktif').checked == true) {
        // console.log(e.relatedTarget);
        // console.log(e.target);
        // console.log(e.currentTarget);
        e.currentTarget.querySelector('#input-aktif').checked = false;
    } else if (e.target.tagName == 'INPUT' && e.target.id == 'input-aktif' && e.target.checked == true && e.currentTarget.querySelector('#input-reset').checked == true) {
        e.currentTarget.querySelector('#input-reset').checked = false;
    }
});

let relatedGreatGrandParent = {};
// Modal
$('#modalFormArtikel').on('hidden.bs.modal', function(e) {
    $(this).find('input.form-control').val('');
    qEditor.deleteText(0, qEditor.getLength());
    objectArtikel = {};
}).on('show.bs.modal', function(e) {
    relatedGreatGrandParent = e.relatedTarget.closest('.card');
    const btnDataValue = e.relatedTarget.getAttribute('data-value');
    if (btnDataValue == 'update') {
        if (!selectedId.length) {
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
            'id_artikel': selectedId[0].id,
            'token': body.getAttribute('data-token')
        };

        objectArtikel.id_artikel = data.id_artikel;
        
        // console.log(data);
        // fetchGetDataArtikel
        fetchData('/admin/publikasi/fetch/get/artikel', data, e.target, 'get-data-artikel');
        objectArtikel.mode = 'update';
    } else {
        objectArtikel.mode = 'create';
    }
});

$('#modalResetArtikel').on('hidden.bs.modal', function(e) {
    const elementID = e.target.querySelector('.modal-body>*:first-child').id;
    if (elementID != null && (elementID == 'multi' || elementID == 'singgle')) {
        e.target.querySelector('#'+elementID).remove();
    }
    objectArtikel = {};
}).on('show.bs.modal', function(e) {
    if (selectedId.length) {
        let frag;
        objectArtikel.id_artikel = selectedId.map( (item) => item.id);
        if (selectedId.length > 1) {
            // Multi
            const multi = '<div class="row my-4" id="multi"><div class="col d-flex justify-content-between align-items-center"><small id="selected-count" data-value="'+selectedId.length+'">artikel dipilih</small><button data-toggle="modal" data-target="#modalSelectedResetList" class="btn btn-sm btn-outline-orange">Detil</button></div></div>';
            frag = new DOMParser().parseFromString(multi, "text/html").getRootNode();
            e.target.querySelector('.modal-body').prepend( frag.body.children[0] );

            e.target.querySelector('input#input-reset').checked = false;
            e.target.querySelector('input#input-aktif').checked = true;
        } else {
            // Singgle
            let data = {
                'id_artikel': selectedId[0].id,
                'token': body.getAttribute('data-token')
            };
    
            // fetchGetResumeArtikel
            fetchData('/admin/publikasi/fetch/get/artikel/resume', data, e.target, 'get-resume-artikel');
        }
        objectArtikel.mode = 'reset';
        relatedGreatGrandParent = e.relatedTarget.closest('.card');
    }
});

$('#modalSelectedResetList').on('hidden.bs.modal', function(e) {
    e.target.querySelector('table>tbody').innerHTML = '<tr><td data-zero="true">Belum ada data yang terpilih ...</td></tr>';
    if (document.getElementById('modalResetArtikel').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
}).on('show.bs.modal', function(e) {
    relatedGreatGrandParent = e.relatedTarget.closest('.card');
    
    if (selectedId.length > 1) {
        let ids = objectArtikel.id_artikel;

        let data = {
            'list_id_artikel': ids,
            'token': body.getAttribute('data-token')
        };
        // fetchGetsPilihanArtikel
        fetchData('/admin/publikasi/fetch/gets/artikel/pilihan', data, e.target, 'gets-pilihan-artikel');
    }
});

let delayTimer,
oldSearchValue,
oldPage;
// Event
let clickPageLink = function(e) {
    const root = relatedGreatGrandParent;
    let active_page = e.target.getAttribute('data-id'),
        token = document.querySelector('body').getAttribute('data-token'),
        url = '/admin/publikasi/fetch/gets/artikel',
        fSwitch = 'gets-artikel';
            
    let data = {
        'token': token,
        'fields': {
            'active_page': active_page
        }
    };

    const search = root.querySelector('input[name="search"]');

    if (search != null) {
        if (search.value.length > 0) {
            data.fields.search = search.value;
        }
    }

    // fetchGetsArtikel Or fetchGetsPilihanArtikel
    if (root.classList.contains('modal')) {
        url += '/pilihan';
        fSwitch = 'gets-pilihan-artikel';
        data.fields.list_id_artikel = selectedId.map( (item) => item.id);
    }
    // console.log(data);
    fetchData(url, data, root, fSwitch);
    e.preventDefault();
};

let clickPagination = function(e) {
    if (e.target.closest('.pagination')) {
        relatedGreatGrandParent = (e.target.closest('.card') != null ? e.target.closest('.card') : e.target.closest('.modal'));
        relatedGreatGrandParent.classList.add('load');
        if (oldPage == e.target.getAttribute('data-id')) {
            if (!delayTimer) {
                clickPageLink(e);
            }
            clearTimeout(delayTimer);
            delayTimer = setTimeout(() => {
                delayTimer = undefined;
                if (relatedGreatGrandParent.classList.contains('load')) {
                    relatedGreatGrandParent.classList.remove('load');
                }
            }, 1000);
        } else {
            clickPageLink(e);
        }
        oldPage = e.target.getAttribute('data-id');
    }
};

$('body').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', clickPagination);

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
        relatedGreatGrandParent = e.target.closest('.card');
    }

    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = e.target.closest('.card');

        let token = document.querySelector('body').getAttribute('data-token');

        let data = {
            'token': token
        };

        if (root.querySelector('.pagination .page-link.active') != null) {
            data['fields'] = {
                'active_page': root.querySelector('.pagination .page-link.active').getAttribute('data-id')
            };
        }

        if (e.target.value.length > 0) {
            if (Object.hasOwn(data, 'fields')) {
                data.fields.search = sValue;
            } else {
                data['fields'] = {
                    'search': sValue
                };
            }
        }
        

        if (!sValue.length && root.querySelector('#hasil-penelusuran') != null) {
            root.querySelector('#hasil-penelusuran').remove();
        }

        if (oldSearchValue != sValue) {
            oldSearchValue = sValue;
        } else {
            root.classList.remove('load');
            return false;
        }
        
        // console.log(data);
        // fetchGetsArtikel
        fetchData('/admin/publikasi/fetch/gets/artikel', data, root, 'gets-artikel');
    }, 1000);
};

document.querySelectorAll('input[name="search"]').forEach(input => {
    input.addEventListener('keyup', searchFunction);
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
            case 'update-artikel':
                fetchUpdateArtikel(root, response);
                break;
            case 'create-artikel':
                fetchCreateArtikel(root, response);
                break;
            case 'gets-artikel':
                fetchGetsArtikel(root, response, data, url);
                break;
            case 'gets-pilihan-artikel':
                fetchGetsPilihanArtikel(root, response, data, url);
                break;
            case 'get-data-artikel':
                fetchGetDataArtikel(root, response);
                break;
            case 'reset-artikel':
                fetchResetArtikel(root, response);
                break;
            case 'get-resume-artikel':
                fetchGetResumeArtikel(root, response);
                break;
            default:
                let currentDate = new Date(),
                    timestamp = currentDate.getTime(),
                    invalid = {
                        error: true,
                        data_toast: 'invalid-show-modal-feedback',
                        feedback: {
                            message: 'Unrecognize fetchData switch name'
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
    })
};

let fetchCreateArtikel = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    let rD = response.feedback.data;

    if (relatedGreatGrandParent.querySelector('.card-footer ul.pagination .page-link.page-item.active') != null) {
        relatedGreatGrandParent.querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    } else {
        relatedGreatGrandParent.querySelector('tbody').innerHTML = '';
        const tr = '<tr data-id-artikel="'+ rD.id_artikel +'"><td class="fit" data-checkbox="row"><span class="inputGroup"><input type="checkbox" id="checkbox-'+ rD.id_artikel +'"><label for="checkbox-'+ rD.id_artikel +'"></label></span></td><td><div class="row"><div class="col"><div class="d-flex flex-column"><a class="title font-weight-bold" href="/artikel/'+ rD.judul.replace(/\s+/g,'-') +'"><span>'+ rD.judul +'</span></a><span>'+ rD.publish_at +'</span></div></div><div class="col col-lg-auto viewer-badge"><span data-count-up-value="'+ rD.jumlah_kunjungan +'" class="text-black-50 view small"><span>'+ rD.jumlah_kunjungan +'</span></span><span data-status="'+ rD.aktif.value +'" class="badge '+ rD.aktif.class +' small px-2 py-1">'+ rD.aktif.text +'</span></div></div></td><td data-author="'+ rD.id_author +'" class="fit"><div class="d-flex gap-x-3 justify-content-end"><div class="media gap-3 align-items-center justify-content-end" data-id-author="'+rD.id_author+'"><div class="font-weight-bold text-right">Author<span class="d-block">'+ rD.nama_author +'</span></div><div class="avatar"><img src="'+ rD.path_gambar_author +'" alt="'+ rD.nama_gambar_author +'"></div></div></div></td></tr>';
        relatedGreatGrandParent.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        const cardFooter = '<div class="card-footer"><div class="row"><div class="col-auto d-flex flex-column text-black-50" id="lebel"></div><div class="col"><ul class="pagination justify-content-end mb-0" data-pages="'+ response.feedback.pages +'"></ul></div></div></div>';
        relatedGreatGrandParent.insertAdjacentHTML('beforeend', cardFooter);
        controlPaginationButton(0, $(relatedGreatGrandParent.querySelector('.pagination')), response.feedback.pages);
    }

    if (!response.error) {
        if (document.querySelectorAll('.toast[data-toast="'+response.toast.data_toast+'"]').length) {
            $('.toast[data-toast="'+response.toast.data_toast+'"]').toast('hide');
        }
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            setTimeout(() => {
                if (relatedGreatGrandParent.querySelector('tbody>tr[data-id-artikel="'+ rD.id_artikel +'"]') != null) {
                    relatedGreatGrandParent.querySelector('tbody>tr[data-id-artikel="'+ rD.id_artikel +'"]').classList.add('highlight');
                    setTimeout(()=> {
                        if (relatedGreatGrandParent.querySelector('tbody>tr.highlight') != null) {
                            relatedGreatGrandParent.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            },100);
        },0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
};

let fetchUpdateArtikel = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    } else {
        let rD = response.feedback.data;
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            setTimeout(() => {
                const trEl = relatedGreatGrandParent.querySelector('tbody>tr[data-id-artikel="'+ rD.id_artikel +'"]');
                if (trEl != null) {
                    trEl.classList.add('highlight');
                    
                    if (rD.judul != null) {
                        trEl.querySelector('td a').setAttribute('href','/artikel/'+ rD.judul.replace(/\s+/g,'-'));
                        trEl.querySelector('td a>span').innerText = rD.judul;
                    }

                    if (rD.id_editor != null) {
                        if (trEl.querySelector('td[data-author][data-editor="'+ rD.id_editor +'"] .media[data-id-editor]') == null) {
                            const media = '<div class="media gap-3 align-items-center" data-id-editor="'+ rD.id_editor +'"><div class="avatar"><img src="'+ rD.path_gambar_editor +'" alt="'+ rD.nama_editor +'"></div><div class="font-weight-bold small">Editor <span class="d-block">'+ rD.nama_editor +'</span><span data-modified-value="'+ rD.modified_at +'" class="small">'+ rD.time_ago +'</span></div></div>';
                            trEl.querySelector('td[data-author]').setAttribute('data-editor', rD.id_editor);
                            trEl.querySelector('td[data-editor]>div').insertAdjacentHTML('afterbegin', media);
                        } else {
                            trEl.querySelector('td[data-editor]').setAttribute('data-editor', rD.id_editor);
                            trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor]').setAttribute('data-id-editor', rD.id_editor);
                            trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] img').setAttribute('src', rD.path_editor);
                            trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] img').setAttribute('alt', rD.nama_editor);
                            trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] .avatar+div>span:first-child').innerText = rD.nama_editor;
                        }

                        trEl.querySelector('td[data-editor] span[data-modified-value]').setAttribute('data-modified-value', rD.modified_at);
                        stopPassed('data-modified-value');
                        trEl.querySelector('td[data-editor] span[data-modified-value]').innerText = ' beberapa saat yang lalu';
                    }

                    trEl.querySelector('.badge').setAttribute('data-status', rD.aktif.value);
                    trEl.querySelector('.badge').setAttribute('class','badge font-weight-bolder '+rD.aktif.class);
                    trEl.querySelector('.badge').innerText = rD.aktif.text;

                    trEl.querySelector('[data-checkbox="row"] input[type="checkbox"]#checkbox-'+rD.id_artikel).click();
                    setTimeout(()=> {
                        if (relatedGreatGrandParent.querySelector('tbody>tr.highlight') != null) {
                            relatedGreatGrandParent.querySelector('tbody>tr.highlight').classList.remove('highlight');
                        }
                    }, 3000);
                }
            }, 100);
        }, 0);
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
};

let fetchGetDataArtikel = function(root, response) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }
    const data = response.feedback.data;

    root.querySelector('input#input-judul').value = data.judul;
    root.querySelector('.current-length').innerText = data.judul.length;

    if (data.isi != '') {
        qEditor.setContents(JSON.parse(new DOMParser().parseFromString(data.isi, "text/html").querySelector('body').innerText));
    }

    objectArtikel.id_artikel = data.id_artikel;
    objectArtikel.judul = data.judul;
    objectArtikel.isi = data.isi;

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

let fetchGetResumeArtikel = function(root, response) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }
    const rD = response.feedback.data;

    const singgle = '<div class="row my-4" id="singgle"><div class="col-12"><a href="'+ (rD.aktif != '0' ? '/artikel/'+ rD.judul.replace(/\s+/g,'-') : 'javascript::void(0);') +'"><h5 id="judul" class="font-weight-bolder text-primary">'+ rD.judul +'</h5></a></div><div class="col-12 d-flex justify-content-between"><div class="media gap-3 align-items-center"><div class="avatar rounded-box"><img src="'+ rD.path_gambar_author +'" alt="'+ rD.nama_author +'"></div><div class="font-weight-bolder small">Author<span class="d-block text-black-50 font-weight-bold mt-1">'+ rD.nama_author +'</span></div></div><div class="item d-flex justify-content-center flex-column align-items-end"><span class="small d-flex align-items-center text-black-50"><small>'+ rD.publish_at_ago +'</small><i class="far fa-clock ml-1"></i></span><span class="small d-flex align-items-center text-black-50 mt-2"><small>'+ rD.jumlah_kunjungan +'</small><i class="fas fa-eye ml-1"></i></span></div></div></div>';
    frag = new DOMParser().parseFromString(singgle, "text/html").getRootNode();
    root.querySelector('.modal-body').prepend( frag.body.children[0] );
    
    root.querySelector('input#input-reset').checked = +rD.reset_status;
    root.querySelector('input#input-aktif').checked = +rD.aktif;
    objectArtikel.reset_status = +rD.reset_status;
    objectArtikel.aktif = +rD.aktif;
};

let fetchGetsArtikel = function(root, response, data, url) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    let sentData = data;

    root.querySelectorAll('tbody tr').forEach(el => {
        el.remove();
    });
    
    data = response.feedback.data;

    let cookieValue = {},
        jumlah_halaman = 0;

    if (root.querySelector('.pagination[data-pages]') != null) {
        jumlah_halaman = root.querySelector('.pagination').getAttribute('data-pages');
    }
        
    if (response.feedback.pages > 0 && data.length == 0 && response.feedback.total_record > 0) {
        document.querySelector('body').setAttribute('data-token', response.token);

        sentData.fields.active_page = result.feedback.fields.pages;
        sentData.token = response.token;
        
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        
        fetchData(url, sentData, root, 'gets-artikel');

        jumlah_halaman = response.feedback.pages;
        controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages, jumlah_halaman);
        return false;
    }

    // Jika jumlah_halaman berganti
    if (jumlah_halaman != response.feedback.pages) {
        jumlah_halaman = response.feedback.pages;
        $(root.querySelector('.pagination')).attr('data-pages', response.feedback.pages);
        if (Object.hasOwn(sentData, 'fields')) {
            if (sentData.fields.active_page == undefined) {
                controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.pages);
            } else {
                controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages);
            }
        } else {
            controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.pages);
        }
    }

    if (data.length < 1) {
        if (response.feedback.search == null) {
            let tr = '<tr data-zero="true"><td colspan="4"><span>Belum ada bantuan untuk artikel ... </span></td></tr>'
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            // eraseCookie('artikel');
            root.classList.remove('load');
            return false;
        }
        let tr = '<tr data-zero="true"><td colspan="4"><span>Data pencarian tidak ditemukan ... </span></td></tr>';
        root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        if (selectedId.length < 1 && root.querySelector('.card-footer') != null) {
            root.querySelector('.card-footer').remove();
        }
    } else {
        data.forEach(rD => { 
            rD.aktif = ((rD.aktif == '1') ? {class: 'badge-success',text:'aktif',value:'1'}:{class:'badge-danger',text:'non-aktif',value:'0'});
            let editor = (rD.id_editor != null ? '<div class="media gap-3 align-items-center justify-content-end" data-id-editor="'+ rD.id_editor +'"><div class="avatar"><img src="'+ rD.path_gambar_editor +'" alt="'+ rD.nama_gambar_editor +'"></div><div class="font-weight-bold">Editor<span class="d-block">'+ rD.nama_editor +'</span></div></div>':'');
            const tr = '<tr data-id-artikel="'+ rD.id_artikel +'"><td class="fit" data-checkbox="row"><span class="inputGroup"><input type="checkbox" id="checkbox-'+ rD.id_artikel +'"><label for="checkbox-'+ rD.id_artikel +'"></label></span></td><td><div class="row"><div class="col"><div class="d-flex flex-column"><a class="title font-weight-bold" href="/artikel/'+ rD.judul.replace(/\s+/g,'-') +'"><span>'+ rD.judul +'</span></a><span>'+ rD.publish_at +'</span></div></div><div class="col col-lg-auto viewer-badge"><span data-count-up-value="'+ rD.jumlah_kunjungan +'" class="text-black-50 view small"><span>'+ rD.jumlah_kunjungan +'</span></span><span data-status="'+ rD.aktif.value +'" class="badge '+ rD.aktif.class +' small px-2 py-1">'+ rD.aktif.text +'</span></div></div></td><td data-author="'+ rD.id_author +'" '+ (rD.id_editor != null ? 'data-editor="'+ rD.id_editor +'" ':'') +'class="fit"><div class="d-flex gap-x-3 justify-content-end">'+editor+'<div class="media gap-3 align-items-center justify-content-end" data-id-author="'+ rD.id_author +'"><div class="font-weight-bold text-right">Author<span class="d-block">'+ rD.nama_author +'</span></div><div class="avatar"><img src="'+ rD.path_gambar_author +'" alt="'+ rD.nama_gambar_author +'"></div></div></div></td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        });
        if (root.querySelector('.card-footer') == null) {
            const cardFooter = '<div class="card-footer"><div class="row"><div class="col-auto d-flex flex-column text-black-50" id="lebel"></div><div class="col"><ul class="pagination justify-content-end mb-0" data-pages="'+ response.feedback.pages +'"></ul></div></div></div>';
            relatedGreatGrandParent.insertAdjacentHTML('beforeend', cardFooter);
            controlPaginationButton(0, $(relatedGreatGrandParent.querySelector('.pagination')), response.feedback.pages);
        }
    }


    // cookieValue.active_page = response.feedback.active_page;
    // if (response.feedback.search != null) {
    //     cookieValue.search = response.feedback.search;
    // }
    // let expiry = new Date();
    // expiry.setTime(expiry.getTime() + (60 * 60 * 1000)); // 1 hour
    // updateCookie('artikel', btoa(JSON.stringify(cookieValue)), window.location.pathname, expiry);

    // checkbox resetting
    document.querySelectorAll('table tbody td[data-checkbox] input[type="checkbox"]').forEach(element => {
        if (selectedId.length) {
            if (selectedId.findIndex(item => item.id == element.closest('tr').getAttribute('data-id-artikel')) > -1) {
                element.checked = true;
            }
        }
    });

    root.classList.remove('load');
};

let fetchGetsPilihanArtikel = function(root, response, data, url) {
    if (response.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    let sentData = data;

    root.querySelectorAll('tbody tr').forEach(el => {
        el.remove();
    });
    
    data = response.feedback.data;

    let jumlah_halaman = 0;

    if (root.querySelector('.pagination[data-pages]') != null) {
        jumlah_halaman = root.querySelector('.pagination').getAttribute('data-pages');
    } else {
        const colPagination = '<ul class="pagination justify-content-end mb-0" data-pages="'+ response.feedback.pages +'"></ul>';
        root.querySelector('.card-footer').insertAdjacentHTML('beforeend', colPagination);
    }
        
    if (response.feedback.pages > 0 && data.length == 0 && response.feedback.total_record > 0) {
        document.querySelector('body').setAttribute('data-token', response.token);

        sentData.fields.active_page = result.feedback.fields.pages;
        sentData.token = response.token;
        
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        
        // fetchGetsPilihanArtikel
        fetchData(url, sentData, root, 'gets-pilihan-artikel');

        jumlah_halaman = response.feedback.pages;
        controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages, jumlah_halaman);
        return false;
    }

    // Jika jumlah_halaman berganti
    if (jumlah_halaman != response.feedback.pages) {
        jumlah_halaman = response.feedback.pages;
        $(root.querySelector('.pagination')).attr('data-pages', response.feedback.pages);
        if (Object.hasOwn(sentData, 'fields')) {
            if (sentData.fields.active_page == undefined) {
                controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.pages);
            } else {
                controlPaginationButton(2, $(root.querySelector('.pagination')), response.feedback.pages);
            }
        } else {
            controlPaginationButton(0, $(root.querySelector('.pagination')), response.feedback.pages);
        }
    }

    if (data.length < 1) {
        if (response.feedback.search == null) {
            let tr = '<tr><td data-zero="true">Belum ada data yang pencarian terpilih ...</td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            root.classList.remove('load');
            return false;
        }
        let tr = '<tr><td data-zero="true">Belum ada data yang terpilih ...</td></tr>';
        root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        if (selectedId.length < 1 && root.querySelector('.card-footer') != null) {
            root.querySelector('.card-footer').remove();
        }
    } else {
        data.forEach(rD => { 
            rD.aktif = ((rD.aktif == '1') ? {class: 'badge-success',text:'aktif',value:'1'}:{class:'badge-danger',text:'non-aktif',value:'0'});
            let editor = (rD.id_editor != null ? '<tr data-id-artikel="1"><div class="media gap-3 align-items-center justify-content-end"><div class="font-weight-bold text-right">Author<span class="d-block">'+ rD.nama_editor +'</span></div><div class="avatar"><img src="'+ rD.path_gambar_editor +'" alt="'+ rD.nama_editor +'"></div></div>':'');
            const tr = '<td><div class="row"><div class="col-12"><div class="row"><div class="col-12 col-md"><div class="d-flex flex-column"><a class="title font-weight-bold" href="/artikel/abu"><span>'+ rD.judul +'</span></a><small><span>'+ rD.publish_at +'</span></small></div></div><div class="col-12 col-md-auto d-flex flex-md-column flex-row-reverse justify-content-end gap-x-3"><span data-count-up-value="'+ rD.jumlah_kunjungan +'" class="text-black-50 view small text-right"><span>'+ rD.jumlah_kunjungan +'</span></span><span data-status="'+ rD.aktif.value +'" class="badge '+ rD.aktif.class +' small px-2 py-1">'+ rD.aktif.text +'</span></div></div></div><div class="col-12 d-flex justify-content-between small mt-2"><div class="media gap-3 align-items-center justify-content-end"><div class="avatar"><img src="'+ rD.path_gambar_author +'" alt="'+ rD.nama_author +'"></div><div class="font-weight-bold text-left">Author<span class="d-block">'+ rD.nama_author +'</span></div></div>'+ editor +'</div></div></td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        });
        if (root.querySelector('.card-footer') == null) {
            const cardFooter = '<div class="card-footer px-3 py-2 d-flex align-items-center justify-content-between"><button class="btn btn-sm btn-link p-0" data-dismiss="modal">Tutup</button><ul class="pagination justify-content-end mb-0" data-pages="'+ response.feedback.pages +'"></ul></div>';
            relatedGreatGrandParent.insertAdjacentHTML('beforeend', cardFooter);
            controlPaginationButton(0, $(relatedGreatGrandParent.querySelector('.pagination')), response.feedback.pages);
        }
    }

    root.classList.remove('load');
};

let fetchResetArtikel = function(root, response) {
    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    if (response.error) {
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    } else {
        let rD = response.feedback.data;
        setTimeout(() => {
            $('#'+root.id).modal('hide');
            setTimeout(() => {
                rD.id_artikel.forEach(idA => {
                    const trEl = relatedGreatGrandParent.querySelector('tbody>tr[data-id-artikel="'+ idA +'"]');
                    if (trEl != null) {
                        trEl.classList.add('highlight');
                        
                        if (rD.reset == true) {
                            trEl.querySelector('td a').setAttribute('href','javascript::void(0);');
                        }

                        if (rD.id_editor != null) {
                            if (trEl.querySelector('td[data-author][data-editor="'+ rD.id_editor +'"] .media[data-id-editor]') == null) {
                                const media = '<div class="media gap-3 align-items-center" data-id-editor="'+ rD.id_editor +'"><div class="avatar"><img src="'+ rD.path_gambar_editor +'" alt="'+ rD.nama_editor +'"></div><div class="font-weight-bold small">Editor <span class="d-block">'+ rD.nama_editor +'</span><span data-modified-value="'+ rD.modified_at +'" class="small">'+ rD.time_ago +'</span></div></div>';
                                trEl.querySelector('td[data-author]').setAttribute('data-editor', rD.id_editor);
                                trEl.querySelector('td[data-editor]>div').insertAdjacentHTML('afterbegin', media);
                            } else {
                                trEl.querySelector('td[data-editor]').setAttribute('data-editor', rD.id_editor);
                                trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor]').setAttribute('data-id-editor', rD.id_editor);
                                trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] img').setAttribute('src', rD.path_editor);
                                trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] img').setAttribute('alt', rD.nama_editor);
                                trEl.querySelector('td[data-editor="'+ rD.id_editor +'"] .media[data-id-editor="'+ rD.id_editor +'"] .avatar+div>span:first-child').innerText = rD.nama_editor;
                            }

                            trEl.querySelector('td[data-editor] span[data-modified-value]').setAttribute('data-modified-value', rD.modified_at);
                            stopPassed('data-modified-value');
                            trEl.querySelector('td[data-editor] span[data-modified-value]').innerText = ' beberapa saat yang lalu';
                        }

                        if (rD.aktif != null) {
                            trEl.querySelector('.badge').setAttribute('data-status', rD.aktif.value);
                            trEl.querySelector('.badge').setAttribute('class','badge font-weight-bolder '+rD.aktif.class);
                            trEl.querySelector('.badge').innerText = rD.aktif.text;
                        }

                        trEl.querySelector('[data-checkbox="row"] input[type="checkbox"]#checkbox-'+idA).click();
                        setTimeout(()=> {
                            if (relatedGreatGrandParent.querySelector('tbody>tr.highlight') != null) {
                                relatedGreatGrandParent.querySelector('tbody>tr.highlight').classList.remove('highlight');
                            }
                        }, 3000);
                    }
                });
            }, 100);
        }, 0);
    }
    if (document.querySelectorAll('.toast[data-toast="feedback"]').length) {
        $('.toast[data-toast="feedback"]').toast('hide');
    }
    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
};