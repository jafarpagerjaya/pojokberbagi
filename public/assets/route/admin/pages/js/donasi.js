let jumlah_halaman = 0;

if (document.querySelector('.pagination[data-pages]') != null) {
    jumlah_halaman = document.querySelector('.pagination').getAttribute('data-pages');
}

let fetchData = function(url, data, root) {
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
    .then(function(result) {
        document.querySelector('body').setAttribute('data-token', result.token);
        // nameNew.token = result.token;
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        let sentData = data;
        if (result.error == false) {
            // Success
            // $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            // $('.toast[data-toast="feedback"] .toast-header strong').text('Pemberitahuan');
            let data = result.feedback.data;

            // console.log(jumlah_halaman != result.feedback.pages);
            // console.log(jumlah_halaman, result.feedback.pages);

            if (result.feedback.pages > 0 && data.length == 0 && result.feedback.total_record > 0) {
                document.querySelector('body').setAttribute('data-token', result.token);

                sentData.halaman = result.feedback.pages;
                sentData.token = result.token;
                
                fetchTokenChannel.postMessage({
                    token: body.getAttribute('data-token')
                });
                
                fetchData(url, sentData, root);
                jumlah_halaman = result.feedback.pages;
                controlPaginationButton(2, $(root.querySelector('.pagination')), result.feedback.pages, jumlah_halaman);
                return false;
            }

            // Jika jumlah_halaman berganti
            if (jumlah_halaman != result.feedback.pages) {
                jumlah_halaman = result.feedback.pages;
                root.querySelector('.pagination').setAttribute('data-pages', result.feedback.pages);
                if (sentData.halaman == undefined) {
                    controlPaginationButton(0, $(root.querySelector('.pagination')), result.feedback.pages);
                } else {
                    controlPaginationButton(2, $(root.querySelector('.pagination')), result.feedback.pages);
                }
            }

            root.querySelector('tbody').innerHTML = '';

            const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };

            if (data.length > 0) {
                data.forEach(element => {
                    // element.create_donasi_at = new Date(element.create_donasi_at).toLocaleDateString("id-ID", optionsDate);
    
                    if (element.nama_donatur == null) {
                        element.nama_donatur = 'Hamba Allah';
                    }
    
                    if (element.warna == null) {
                        element.warna = '#727272';
                    }
    
                    let badge,
                        verivikasi_text,
                        verivikasi_data_modal = '',
                        waktu_bayar,
                        kuitansi_donasi = '';
    
                    if (element.bayar == '1') {
                        badge = 'badge-success';
                        verivikasi_text = 'Sudah Diverivikasi';
                        verivikasi_class = ' disabled';
                        verivikasi_link_text = verivikasi_text;
                        waktu_bayar = element.waktu_bayar;
                        kuitansi_donasi = '<a class="dropdown-item" data-id="'+ element.id_donasi +'" href="javascript():;" data-toggle="modal" data-target="#modalKuitansiDonasi">Kuitansi</a>';
                    } else {
                        badge = 'badge-warning';
                        verivikasi_text = 'Belum Diverivikasi';
                        verivikasi_class = ' text-warning font-weight-bold';
                        verivikasi_link_text = 'Verivikasi';
                        verivikasi_data_modal = ' data-toggle="modal" data-target="#modalValidasiDonasi" data-id="'+ element.id_donasi +'"';
                    }
    
                    let tr;
                    if (result.feedback.target == 'list-donasi') {
                        tr = '<tr><td class="py-2"><a href="#" class="id-donasi text-primary font-weight-bolder" data-id="element.id_donasi"><span>'+ element.id_donasi +'</span></a><span class="font-weight-bolder"'+ (element.warna.length > 0 ? ' style="color: '+ element.warna +'"' : '') +'> '+ element.nama_bantuan +'</span>'+ (element.nama_sektor != null ? '<span class="text-muted"> ('+ element.nama_sektor +')</span>' : '') +'<div class="time small font-weight-bolder text-black-50"><span>'+ element.create_donasi_at +'</span></div></td><td class="py-2"><span class="badge '+ badge +'">'+ verivikasi_text +'</span>'+ (waktu_bayar != undefined ? '<div class="small font-weight-bolder text-black-50"><span>'+ waktu_bayar +'</span></div>' : '') +'</td><td class="py-2"><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ element.jumlah_donasi +'</span></div><div class="small font-weight-bolder text-black-50"><span>'+ keteranganJenisChannelPayment(element.jenis_cp) +'</span>'+ ((element.flip != null) ? ' <span class="badge badge-warning">flip</span>':'') +'</div></div><div class="avatar rounded ml-3 bg-transparent border" data-id-donatur="'+ element.id_donatur +'"><img src="'+ element.path_gambar_cp +'" alt="'+ element.nama_path_gambar_cp +'" class="img-fluid"></div></div></td><td class="text-right auto"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" x-placement="top-end"><a class="dropdown-item" href="/admin/bantuan/data/'+ element.id_bantuan +'">Lihat Program Donasi</a>'+kuitansi_donasi+'<a class="dropdown-item'+ verivikasi_class +'" href="javascript():;"'+ verivikasi_data_modal +'>'+ verivikasi_link_text +'</a></div></div></td></tr>';
                    } else {
                        tr = '<tr'+ (element.id_order_donasi != null ? ' data-order-id="'+ element.id_order_donasi +'"' : '')+'><td><div class="d-flex gap-x-1"><div class="id"><span class="text-primary font-weight-bolder" data-id="'+ element.id_order_donasi+'">#'+ element.id_order_donasi+'</span></div><div class="desc"><a '+ (element.id_bantuan != null ? 'href="/bantuan/data/'+element.id_bantuan+'"' : 'javascript::void(0);')+' class="font-weight-bolder" '+ (element.warna != null ? ' style="color: '+ element.warna + '"' : '') +'><span>'+ element.nama_bantuan +'</span></a>'+ (element.nama_sektor != null ? '<span class="text-muted"> (' + element.nama_sektor + ')</span>' : '')+'<div class="time small text-black-50 font-weight-bolder"><span>'+ element.create_order_at+'</span></div></div></div></td><td><span class="badge '+ badge +'">'+ element.status+'</span>'+ (element.external_id != null ? '<span class="small text-black-50 font-weight-bolder d-block">' +element.external_id+ '</span>' : '')+'</td><td><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ element.jumlah_donasi +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ keteranganJenisChannelPayment(element.jenis_cp)+'</span> '+ (element.flip != null ? '<span class="badge badge-warning">flip</span>':'')+'</div></div><div class="avatar rounded ml-3 bg-transparent border" data-id-donatur="'+ element.id_donatur+'"><img src="'+ element.path_gambar_cp+'" alt="'+ element.nama_path_gambar_cp+'" class="img-fluid"></div></div></td><td><div class="dropdown show"><a class="btn btn-sm btn-icon-only text-light" href="javascript::void(0)" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" x-placement="top-end">'+ (element.id_bantuan != null ? '<a class="dropdown-item" href="/admin/bantuan/data/'+ element.id_bantuan +'">Lihat Campaign</a>' : '')+'</a>'+ (element.flip != null ? '' : '<a class="verivikasi dropdown-item' + (element.status == 'SUCCESFUL' ? ' disabled">Sudah Terverivikasi' : ' text-warning font-weight-bold" data-id="'+element.id_order_donasi+'" href="javascript::void(0);" data-toggle="modal" data-target="#modalValidasiDonasi">Verivikasi') + '</a>')+'</div></div></td></tr>';
                    }
                    root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
                });
                // if (root.querySelector('table thead').width() > root.find('table').parent().width()) {
                //     root.querySelector('table').addClass('table-responsive');
                // } else {
                //     if (root.querySelector('table').hasClass('table-responsive')) {
                //         root.querySelector('table').removeClass('table-responsive');
                //     }
                // }
            } else {
                const tr0 = '<tr><td colspan="4">Data donasi tidak ditemukan...</td></tr>';
                root.querySelector('tbody').insertAdjacentHTML('beforeend', tr0);
            }

            root.querySelector('.log-info span').innerText = result.feedback.data.length;
            root.querySelector('.log-info .counter-card').setAttribute('data-count-up-value', result.feedback.total_record);
            root.querySelector('.log-info .counter-card').innerText = result.feedback.total_record;
        } else {
            // Failed
            console.log('there is some error in server side');
            // tampilkan pesan waktu bayar wajib diisi
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'invalid-fetch-read',
                feedback: {
                    message: result.feedback.message
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                delay: 10000
            }).toast('show');
        }
        if (document.querySelector('#'+result.feedback.target+' table.load')) {
            document.querySelector('#'+result.feedback.target+' table').classList.remove('load');
        }
    });
};

let clickPageLink = function(e) {
    const root = document.getElementById(relatedTarget);
    let page = e.target.getAttribute('data-id'),
        limit = root.querySelector('#'+relatedTarget+' tbody').getAttribute('data-limit'),
        token = document.querySelector('body').getAttribute('data-token');
            
    let data = {
        'token': token,
        'halaman': page,
        'limit': limit,
        'target': relatedTarget
    };

    const search = root.querySelector('#'+relatedTarget+' input[name="search"]');

    if (search.value.length > 0) {
        data.search = search.value;
    }
    
    // console.log(data);
    const targetFetch = relatedTarget.split('list-')[1];
    fetchData('/admin/fetch/read/'+targetFetch+'/list', data, root);
    e.preventDefault();
}

let oldPage,
    oldSearchValue = {},
    relatedTarget,
    delayTimer;

let clickPagination = function(e) {
    if (e.target.closest('.tab-pane.active.show') == null) {
        relatedTarget = e.target.closest('.tab-pane:not(.active):not(.show)').id;
    } else {
        relatedTarget = e.target.closest('.tab-pane.active.show').id;
    }
    document.querySelector('#'+ relatedTarget + ' table').classList.add('load');
    if (oldPage == e.target.getAttribute('data-id')) {
        if (!delayTimer) {
            clickPageLink(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
            if (document.querySelector('#'+ relatedTarget + ' table').classList.contains('load')) {
                document.querySelector('#'+ relatedTarget + ' table').classList.remove('load');
            }
        }, 1000);
    } else {
        clickPageLink(e);
    }
    oldPage = e.target.getAttribute('data-id');
    // console.log('click');
};

$('.pagination').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', clickPagination);


let searchFunction = function(e) {
    relatedTarget = e.target.closest('.tab-pane.active.show').id;
    let sValue = '';
    if ($(this).val().length > 0) {
        sValue = $(this).val();
        if (isNumber(sValue)) {
            $(this).val(numberToPrice(sValue));
            // sValue = priceToNumber(sValue); -- jika ingin pakai ini hapus method format di kolumn serach getListDonasi dan getListOrderDonasi
            sValue = $(this).val();
        }
    }
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = document.getElementById(relatedTarget);
        let target = e.target.closest('.tab-pane.active.show').id,
            limit = root.querySelector('tbody').getAttribute('data-limit'),
            token = document.querySelector('body').getAttribute('data-token');

        let data = {
            'token': token,
            'limit': limit,
            'target': target
        };

        if (root.querySelector('.pagination .page-link.active') != null) {
            data.halaman = root.querySelector('.pagination .page-link.active').getAttribute('data-id');
        }

        if ($(this).val().length > 0) {
            data.search = sValue;
        }

        if (oldSearchValue[relatedTarget] != data.search) {
            oldSearchValue[relatedTarget] = data.search;
            document.querySelector('#'+ relatedTarget + ' table').classList.add('load');
        } else {
            document.querySelector('#'+ relatedTarget + ' table').classList.remove('load');
            return false;
        }
        
        // console.log(data);
        target = target.split('list-')[1];
        fetchData('/admin/fetch/read/'+ target +'/list', data, root);
    }, 1000);
}

$('input[name="search"]').on('keyup', searchFunction);

document.querySelectorAll('button[role="tab"]').forEach(btn => {
    btn.addEventListener('click', function(e) {
        if (e.target.type == undefined) {
            relatedTarget = e.target.parentElement.getAttribute('data-target').replace('#list-','')
        } else {
            relatedTarget = e.target.getAttribute('data-target').replace('#list-','');
        }
    });
})