let qEditor = editor('#editor'),
    relatedTarget;
$('#modalBuatSelengkapnya').on('hidden.bs.modal', function() {
    $(this).find('input.form-control').val('');
    qEditor.deleteText(0, qEditor.getLength());
}).on('show.bs.modal', function(e) {
    relatedTarget = e.relatedTarget.closest('.card');
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
   
    // e.target.classList.add('disabled');

    let data = {
        deskripsi: {
            id_bantuan: root.querySelector('[name="id_bantuan"]').value,
            judul: root.querySelector('[name="judul"]').value,
            isi: Delta
        },
        token: document.querySelector('body').getAttribute('data-token')
    };

    // console.log(data);
    // fetchCreateDeskripsiSelengkapnya
    fetchData('/admin/fetch/create/bantuan/deskripsi-selengkapnya', data, root, 'create-deskripsi-selengkapnya');
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
            case 'create-deskripsi-selengkapnya':
                fetchCreateDeskripsiSelengkapnya(root, response);
                break;
            case 'read-deskripsi-selengkapnya':
                fetchReadDeskripsiSelengkapnya(root, response, data);
                break;
            default:
                break;
        }

    })
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

    if (relatedTarget.querySelector('.card-footer ul.pagination .page-link.page-item.active') != null) {
        relatedTarget.querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    } else {
        relatedTarget.querySelector('thead tr').insertAdjacentHTML('beforeend', '<th scope="col" class="sort" data-sort="judul">Judul</th><th scope="col" class="fit"></th>');
        relatedTarget.querySelector('tfoot tr').insertAdjacentHTML('beforeend', '<th scope="col" class="sort" data-sort="judul">Judul</th><th scope="col" class="fit"></th>');
        relatedTarget.querySelector('tbody').innerHTML = '';
        const tr = '<tr data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"><th scope="row" class="fit"><div class="d-flex flex-column"><a href="'+ (response.feedback.data.id_bantuan == null ? '#' : '/admin/bantuan/data/' + response.feedback.data.id_bantuan) +'" class="font-weight-bolder"><span>'+ response.feedback.data.nama_bantuan +'</span></a><span class="mb-0">'+ response.feedback.data.create_at +'</span></div></th><td><span>'+ response.feedback.data.judul +'</span></td><td class="text-right"><div class="dropdown"><span><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a></span><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a class="dropdown-item font-weight-bold" href="#">Lihat Isi</a><a class="dropdown-item font-weight-bold text-warning" href="#">Ubah Isi</a><a class="dropdown-item font-weight-bold text-danger" href="#">Reset</a></div></div></td></tr>';
        relatedTarget.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        controlPaginationButton(0, $(relatedTarget.querySelector('.pagination')), response.feedback.fields.pages);
    }

    setTimeout(() => {
        $('#'+root.id).modal('hide');
        setTimeout(() => {
            if (relatedTarget.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]') != null) {
                relatedTarget.querySelector('tbody>tr[data-id-deskripsi="'+ response.feedback.data.id_deskripsi +'"]').classList.add('highlight');
                setTimeout(()=> {
                    relatedTarget.querySelector('tbody>tr.highlight').classList.remove('highlight');
                }, 3000);
            }
        },100);
    },0);
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
            let tr = '<tr data-id-deskripsi="'+ elment.id_deskripsi +'"><th scope="row" class="fit"><div class="d-flex flex-column"><a href="'+ (elment.id_bantuan == null ? '#' : '/admin/bantuan/data/' + elment.id_bantuan) +'" class="font-weight-bolder"><span>'+ elment.nama_bantuan +'</span></a><span class="mb-0">'+ elment.create_at +'</span></div></th><td><span>'+ elment.judul +'</span></td><td class="text-right"><div class="dropdown"><span><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a></span><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a class="dropdown-item font-weight-bold" href="#">Lihat Isi</a><a class="dropdown-item font-weight-bold text-warning" href="#">Ubah Isi</a><a class="dropdown-item font-weight-bold text-danger" href="#">Reset</a></div></div></td></tr>';
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
}

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
    e.target.closest('.card').querySelector('table').classList.add('load');
    if (oldPage == e.target.getAttribute('data-id')) {
        if (!delayTimer) {
            clickPageLink(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
            e.target.closest('.card').querySelector('table').classList.remove('load');
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