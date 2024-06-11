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
                controlPaginationButton(2, root.find('.pagination'), result.feedback.pages, jumlah_halaman);
                return false;
            }

            // Jika jumlah_halaman berganti
            if (jumlah_halaman != result.feedback.pages) {
                jumlah_halaman = result.feedback.pages;
                root.find('.pagination').attr('data-pages', result.feedback.pages);
                if (sentData.halaman == undefined) {
                    controlPaginationButton(0, root.find('.pagination'), result.feedback.pages);
                } else {
                    controlPaginationButton(2, root.find('.pagination'), result.feedback.pages);
                }
            }

            root.find('tbody').children('tr').remove();

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
    
                    let tr = '<tr><td class="py-2"><a href="#" class="id-donasi text-primary font-weight-bolder" data-id="element.id_donasi">'+ element.id_donasi +'</a><span class="font-weight-bolder"'+ (element.warna.length > 0 ? ' style="color: '+ element.warna +'"' : '') +'> '+ element.nama_bantuan +'</span>'+ (element.nama_sektor != null ? '<span class="text-muted"> ('+ element.nama_sektor +')</span>' : '') +'<div class="time small font-weight-bolder text-black-50">'+ element.create_donasi_at +'</div></td><td class="py-2"><span class="badge '+ badge +'">'+ verivikasi_text +'</span>'+ (waktu_bayar != undefined ? '<div class="small font-weight-bolder text-black-50"> '+ waktu_bayar +' </div>' : '') +'</td><td class="py-2"><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold">'+ element.jumlah_donasi +'</div><div class="small font-weight-bolder text-black-50">'+ keteranganJenisChannelPayment(element.jenis_cp) +'</div>'+ ((element.flip != null) ? '<span class="badge badge-warning">flip</span>':'') +'</div><div class="avatar rounded ml-3 bg-transparent border" data-id-donatur="'+ element.id_donatur +'"><img src="'+ element.path_gambar_cp +'" alt="'+ element.nama_path_gambar_cp +'" class="img-fluid"></div></div></td><td class="text-right auto"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" x-placement="top-end"><a class="dropdown-item" href="/admin/bantuan/data/'+ element.id_bantuan +'">Lihat Program Donasi</a>'+kuitansi_donasi+'<a class="dropdown-item'+ verivikasi_class +'" href="javascript():;"'+ verivikasi_data_modal +'>'+ verivikasi_link_text +'</a></div></div></td></tr>';
                    root.find('tbody').append(tr);
                });
                if (root.find('table thead').width() > root.find('table').parent().width()) {
                    root.find('table').addClass('table-responsive');
                } else {
                    if (root.find('table').hasClass('table-responsive')) {
                        root.find('table').removeClass('table-responsive');
                    }
                }
            } else {
                const tr0 = '<tr><td colspan="4">Data donasi tidak ditemukan...</td></tr>';
                root.find('tbody').append(tr0);
            }

            root.find('.card-header .status span').text(result.feedback.data.length);
            root.find('.card-header .status .counter-card').attr('data-count-up-value', result.feedback.total_record);
            root.find('.card-header .status .counter-card').text(result.feedback.total_record);
        } else {
            // Failed
            $('.toast[data-toast="feedback"] .time-passed').text('Baru Saja');
            $('.toast[data-toast="feedback"] .toast-body').html(data.feedback.message);
            $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
            $('.toast[data-toast="feedback"] .toast-header strong').text('Peringatan!');
            console.log('there is some error in server side');
            $('.toast').toast('show');
        }

        document.querySelector('body').setAttribute('data-token', result.token);
        // nameNew.token = result.token;
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
    });
};

$('.pagination').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', function(e) {
    const root = $(this).parents('.card');
    let page = $(this).data('id'),
        limit = root.find('tbody').data('limit'),
        token = document.querySelector('body').getAttribute('data-token');
            
    let data = {
        'token': token,
        'halaman': page,
        'limit': limit
    };

    const search = root.find('input[name="search"]');

    if (search.val().length > 0) {
        data.search = search.val();
    }
    
    fetchData('/admin/fetch/read/donasi/list', data, root);

    e.preventDefault();
});

let delayTimer;
$('input[name="search"]').on('keyup', function() {
    let sValue = '';
    if ($(this).val().length > 0) {
        sValue = $(this).val();
        if (isNumber(sValue)) {
            $(this).val(numberToPrice(sValue));
            // sValue = priceToNumber(sValue); -- jika ingin pakai ini hapus method format di kolumn serach getListDonasi
            sValue = $(this).val();
        }
    }
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = $(this).parents('.card');
        // let page = root.find('.pagination').data('id'),
        let page = root.find('.pagination .page-link.active').data('id'),
            limit = root.find('tbody').data('limit'),
            token = document.querySelector('body').getAttribute('data-token');

        let data = {
            'token': token,
            'halaman': page,
            'limit': limit
        };

        if ($(this).val().length > 0) {
            data.search = sValue;
        }
        
        // console.log(data);

        fetchData('/admin/fetch/read/donasi/list', data, root);
    }, 1000);
});