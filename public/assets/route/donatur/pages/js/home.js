let client = JSON.parse(atob(decodeURIComponent(getCookie('client-pojokberbagi'))));

if (client) {
    if (client.auth) {
        authChannelSignin.auth = client.auth;
        authChannelSignin['sender'] = window.location.pathname;
        authChannel.postMessage({
            action: "signin",
            rule: authChannelSignin
        });
    }
}

let jumlah_halaman = 0,
    delayTimer;

if (document.querySelector('.pagination[data-pages]') != null) {
    jumlah_halaman = document.querySelector('.pagination').getAttribute('data-pages');
}

// fetch
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
            console.log(result)
            if (result.error == false) {
                let data = result.feedback.data;

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

                if (data.length > 0) {
                    data.forEach(element => {    
                        let badge_class,
                            badge_text,
                            jenis_cp_style = '';
                            listAction = '';
        
                        if (element.bayar == '1') {
                            badge_class = 'badge-success';
                            badge_text = 'Sudah Bayar';
                            listAction = '<a class="dropdown-item" data-id="'+ element.id_donasi +'" href="javascript():;" data-toggle="modal" data-target="#modalKwitansiDonasi">Kwitansi</a>';
                        } else {
                            badge_class = 'badge-warning';
                            badge_text = 'Belum Bayar';
                            listAction = '<a href="javascript:;" class="dropdown-item text-warning font-weight-bolder" role="button" data-toggle="modal" data-target="#modalGantiMetodePembayaran" data-cp="' + element.id_cp + '">Ganti Metode Bayar</a>';
                        }

                        if (element.jenis_cp == 'TN') {
                            jenis_cp_style = ' style="width: 42px;"';
                        }
        
                        let tr = '<tr><th scope="row"><div class="media align-items-center"><a target="_blank" href="/bantuan/detil/' + element.id_bantuan + '" data-nama-bantuan="' + element.nama_bantuan + '"><span class="name mb-0 text-sm font-weight-bolder">' + element.nama_bantuan + '</span></a></div></th><td class="text-right"><b data-id-donasi="' + element.id_donasi + '">' + element.jumlah_donasi + '</b></td><td><div class="channel-payment d-flex justify-content-between align-items-center"><p class="text-info m-0" data-jenis-cp="' + element.jenis_cp + '">' + keteranganJenisChannelPayment(element.jenis_cp) + '</p><img class="img-fluid" src="' + element.path_gambar_cp + '" alt="' + element.nama_cp + '"' + jenis_cp_style + '></div></td><td><span class="badge badge-pill ' + badge_class +'">' + badge_text + '</span>' + ((element.waktu_bayar != null) ? '<div class="ml-2">' + element.waktu_bayar + '</div>' :  '') + '</td><td><a class="font-weight-bolder" href="javascript:;">' + element.id_donasi + '</a><div class="font-weight-bold text-black-50">' + element.create_at + '</div></td><td class="text-right"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">' + listAction + '</div></div></td></tr>';
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
                    const tr0 = '<tr><td colspan="5">Data donasi & tagihan tidak ditemukan...</td></tr>';
                    root.find('tbody').append(tr0);
                }

                root.find('.card-header .status span').text(result.feedback.data.length);
                root.find('.card-header .status .counter-card').attr('data-count-up-value', result.feedback.total_record);
                root.find('.card-header .status .counter-card').text(result.feedback.total_record);
            } else {
                // Failed
                let tagihan_type = url.split('/').at(-1);
                $('.toast[data-toast="donatur"] .time-passed').text('Baru Saja');
                $('.toast[data-toast="donatur"] .toast-body').html(result.feedback.message + ' <spam class="font-weight-bolder">'+ tagihan_type +'</span>');
                $('.toast[data-toast="donatur"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
                $('.toast[data-toast="donatur"] .toast-header strong').text('Peringatan!');
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
    
    fetchData('/donatur/fetch/read/donasi-tagihan', data, root);

    e.preventDefault();
});

$('input[name="search"]').on('keyup', function() {
    let sValue = '';
    if ($(this).val().length > 0) {
        sValue = $(this).val();
        if (isNumber(sValue)) {
            $(this).val(numberToPrice(sValue));
            // sValue = priceToNumber(sValue); -- jika ingin pakai ini hapus method format di kolumn serach dataBantuan
            sValue = $(this).val();
        }
    }
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = $(this).parents('.card');
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

        fetchData('/donatur/fetch/read/donasi-tagihan', data, root);
    }, 1000);
});