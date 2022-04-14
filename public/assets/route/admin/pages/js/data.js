let keteranganJenisChannelPayment = function(jenis) {
    let metode_bayar;
    if (jenis == 'TB') {
        metode_bayar = "Transfer Bank";
    } else if (jenis == 'QR') {
        metode_bayar = "QRIS";
    } else if (jenis == 'EW') {
        metode_bayar = "E-Wallet";
    } else if (jenis == 'VA') {
        metode_bayar = "Virtual Akun";
    } else if (jenis == 'GM') {
        metode_bayar = "Gerai Mart";
    } else if (jenis == 'GI') {
        metode_bayar = "Giro";
    } else {
        metode_bayar = "Unrecognize (Payment Method)";
    }
    return metode_bayar;
}

let jumlah_halaman;

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
        console.log(result);
        if (result.error == false) {
            // Success
            // $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            // $('.toast[data-toast="feedback"] .toast-header strong').text('Pemberitahuan');
            let data = result.feedback.data;

            console.log(jumlah_halaman != result.feedback.pages);

            if (jumlah_halaman != result.feedback.pages) {
                root.find('.pagination').attr('data-pages', result.feedback.pages);
                controlPaginationButton(2, root.find('.pagination'), result.feedback.pages);
            }

            root.find('tbody').children('tr').remove();

            data.forEach(element => {
                console.log(element);
                if (element.nama_donatur == null) {
                    element.nama_donatur = 'Hamba Allah';
                }

                if (element.kontak == null) {
                    element.kontak = '';
                }
                
                if (element.kontak == '' && element.email == null) {
                    element.kontak = 'Tanpa Kontak dan Email';
                }

                if (element.email == null) {
                    element.email = '';
                }
                let tr = '<tr><td class="py-2"><a href="#" class="id-donasi text-primary font-weight-bolder">'+ element.id_donasi +'</a><div class="time small text-black-50">'+ element.waktu_bayar +'</div></td><td class="py-2"><div class="media align-items-center"><a href="#" class="avatar rounded-circle mr-3" data-id-donatur="'+ element.id_donatur +'"><img alt="'+ element.nama_path_gambar_akun +'" src="'+ element.path_gambar_akun +'"></a><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold">'+ ucwords(element.nama_donatur) +'</div><div class="small"><span class="small kontak">'+ element.kontak +' </span><span class="small email">'+ element.email +'</span></div></div></div></td><td class="py-2"><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold">'+ numberToPrice(element.jumlah_donasi) +'</div><div class="small text-muted">'+ keteranganJenisChannelPayment(element.jenis) +'</div></div><div class="avatar rounded ml-3 bg-transparent border" data-id-cp="'+ element.id_cp +'"><img src="'+ element.path_gambar_cp +'" alt="'+ element.nama_path_gambar_cp +'" class="img-fluid"></div></div></td></tr>';
                root.find('tbody').append(tr);
            });
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

$('.toast').toast({
    'autohide': false
});

const id_bantuan = document.querySelector('[data-id-bantuan]').getAttribute('data-id-bantuan');

$('.pagination').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', function(e) {
    const root = $(this).parents('.card'),
        page = $(this).data('id'),
        limit = root.find('tbody').data('limit'),
        token = document.querySelector('body').getAttribute('data-token');

    let bayar = 1;
            
    let data = {
        'token': token,
        'halaman': page,
        'limit': limit,
        'id_bantuan': id_bantuan,
        'bayar': bayar
    };

    const search = root.find('input[name="search"]');

    if (search.val().length > 0) {
        data.search = search.val();
    }

    // console.log(data);
    
    fetchData('/admin/fetch/read/donasi/bantuan', data, root);

    e.preventDefault();
});

let delayTimer;
$('input[name="search"]').on('keyup', function() {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = $(this).parents('.card');
            page = root.find('.pagination').data('id'),
            limit = root.find('tbody').data('limit'),
            token = document.querySelector('body').getAttribute('data-token');

        let bayar = 1;

        let data = {
            'token': token,
            'halaman': page,
            'limit': limit,
            'id_bantuan': id_bantuan,
            'bayar': bayar
        };

        if ($(this).val().length > 0) {
            data.search = $(this).val();
        }
        
        console.log(data);

        // console.log(rootPagi);
        // let state = 0;
        // controlPaginationButton(state, rootPagi, rootPagi.data('pages'));
        fetchData('/admin/fetch/read/donasi/bantuan', data, root);
    }, 1000);
});