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
        // console.log(result);
        if (result.error == false) {
            // Success
            // $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            // $('.toast[data-toast="feedback"] .toast-header strong').text('Pemberitahuan');
            let data = result.feedback.data;

            // console.log(data.length);

            // console.log(jumlah_halaman != result.feedback.pages);
            // console.log(jumlah_halaman, result.feedback.pages);

            if (jumlah_halaman != result.feedback.pages) {
                console.log(jumlah_halaman, result.feedback.pages);
                jumlah_halaman = result.feedback.pages;
                root.find('.pagination').attr('data-pages', result.feedback.pages);
                controlPaginationButton(0, root.find('.pagination'), result.feedback.pages);
            }

            root.find('tbody').children('tr').remove();

            if (data.length > 0) {

                const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };

                data.forEach(element => {
                    // element.waktu_bayar = new Date(element.waktu_bayar).toLocaleDateString("id-ID", optionsDate);

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

                    element.nama_donatur = ucwords(element.nama_donatur);
                    element.jenis = keteranganJenisChannelPayment(element.jenis);
                    
                    let tr = '<tr><td class="py-2"><a href="#" class="id-donasi text-primary font-weight-bolder">'+ element.id_donasi +'</a><div class="time small text-black-50 font-weight-bold">'+ element.waktu_bayar +'</div></td><td class="py-2"><div class="media align-items-center"><a href="#" class="avatar rounded-circle mr-3" data-id-donatur="'+ element.id_donatur +'"><img alt="'+ element.nama_path_gambar_akun +'" src="'+ element.path_gambar_akun +'"></a><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bolder">'+ element.nama_donatur +'</div><div class="small text-black-50 font-weight-bold"><span class="kontak">'+ element.kontak +' </span><span class="email">'+ element.email +'</span></div></div></div></td><td class="py-2"><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bolder">'+ element.jumlah_donasi +'</div><div class="small text-muted font-weight-bold">'+ element.jenis +'</div></div><div class="avatar rounded ml-3 bg-transparent border" data-id-cp="'+ element.id_cp +'"><img src="'+ element.path_gambar_cp +'" alt="'+ element.nama_path_gambar_cp +'" class="img-fluid"></div></div></td></tr>';
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
                const tr0 = '<tr><td colspan="3">Data donasi tidak ditemukan...</td></tr>';
                root.find('tbody').append(tr0);
            }
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
    let sValue = '';
    if ($(this).val().length > 0) {
        sValue = $(this).val();
        if (isNumber(sValue)) {
            $(this).val(numberToPrice(sValue));
            // sValue = priceToNumber(sValue); // jika ingin pakai ini hapus method format di kolumn serach dataDonasiDonaturBantuan
            sValue = $(this).val();
        }
    }
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
            data.search = sValue;
        }
        
        // console.log(data);

        fetchData('/admin/fetch/read/donasi/bantuan', data, root);
    }, 1000);
});