const origin = window.location.origin;
let id_donasi_kuitansi;

$('#modalKuitansiDonasi').on('show.bs.modal', function(e) {
    const id_donasi = e.relatedTarget.getAttribute('data-id'), 
          modal = $(this);
    let data = {
        id_donasi: id_donasi,
        token: body.getAttribute('data-token')
    };

    if (id_donasi != id_donasi_kuitansi) {
        id_donasi_kuitansi = id_donasi;

        fetch('/donatur/fetch/get/kuitansi', {
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
            // console.log(result)
            if (result.error) {
                $('.toast[data-toast="donatur"] .time-passed').text('Baru Saja');
                $('.toast[data-toast="donatur"] .toast-body').html(data.feedback.message);
                $('.toast[data-toast="donatur"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
                $('.toast[data-toast="donatur"] .toast-header strong').text('Peringatan!');
                console.log('there is some error in server side');
                $('.toast').toast('show');
                return false;
            }

            let data = origin + '/donasi/cek/kuitansi/'+result.feedback.data.id_kuitansi;
            let jsonQrOptions = { "image": "/assets/images/brand/pojok-berbagi-transparent.png", "width": 100, "height": 100, "data": data, "margin": 0, "qrOptions": { "typeNumber": "0", "mode": "Byte", "errorCorrectionLevel": "Q" }, "imageOptions": { "hideBackgroundDots": true, "imageSize": 0.3, "margin": 2 }, "dotsOptions": { "type": "rounded", "color": "#000000", "gradient": null }, "backgroundOptions": { "color": "#ffffff" }, "dotsOptionsHelper": { "colorType": { "single": true, "gradient": false }, "gradient": { "linear": true, "radial": false, "color1": "#6a1a4c", "color2": "#6a1a4c", "rotation": "0" } }, "cornersSquareOptions": { "type": "extra-rounded", "color": "#000000" }, "cornersSquareOptionsHelper": { "colorType": { "single": true, "gradient": false }, "gradient": { "linear": true, "radial": false, "color1": "#000000", "color2": "#000000", "rotation": "0" } }, "cornersDotOptions": { "type": "", "color": "#000000" }, "cornersDotOptionsHelper": { "colorType": { "single": true, "gradient": false }, "gradient": { "linear": true, "radial": false, "color1": "#000000", "color2": "#000000", "rotation": "0" } }, "backgroundOptionsHelper": { "colorType": { "single": true, "gradient": false }, "gradient": { "linear": true, "radial": false, "color1": "#ffffff", "color2": "#ffffff", "rotation": "0" } } }
            const qrCode = new QRCodeStyling(jsonQrOptions);
            if (document.getElementById("canvas-qr").children[0] != null) {
                document.getElementById("canvas-qr").children[0].remove();
            }
            qrCode.append(document.getElementById("canvas-qr"));

            modal.find('#id-kuitansi').attr('data-id-kuitansi', result.feedback.data.id_kuitansi);
            modal.find('#id-kuitansi').text(result.feedback.data.id_kuitansi);
            modal.find('#create-kuitansi-at').text(result.feedback.data.create_kuitansi_at);
            modal.find('#nama-donatur').text(result.feedback.data.nama_donatur);
            modal.find('#samaran-donatur').text(result.feedback.data.samaran);
            modal.find('#email-donatur').text(result.feedback.data.email);
            modal.find('#kontak-donatur').text(result.feedback.data.kontak);
            modal.find('#nama-bantuan').text(result.feedback.data.nama_bantuan);
            modal.find('span#jumlah-donasi').text(result.feedback.data.jumlah_donasi);
            modal.find('span#jenis-cp').text(keteranganJenisChannelPayment(result.feedback.data.jenis));
            modal.find('#nomor-cp').text(nomorCPTunai(result.feedback.data.jenis, result.feedback.data.nomor));
            modal.find('img#img-cp').attr('src', result.feedback.data.path_gambar_cp);
            modal.find('img#img-cp').attr('alt', result.feedback.data.nama_cp);
            modal.find('#signature img').attr('src', result.feedback.data.path_gambar_signature);
            modal.find('#signature img').attr('alt', result.feedback.data.nama_gambar_signature);
            modal.find('#nama-pengesah').text(result.feedback.data.nama_pengesah);
            modal.find('#alias').text(result.feedback.data.alias_jabatan);

            body.setAttribute('data-token', result.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });
        });
    }
}).on('hidden.bs.modal', function(e) {
    if ($('.toast[data-toast="donatur"] .toast-header .small-box').hasClass('bg-success')) {
        $('.toast[data-toast="donatur"]').toast('show');
    }
});

$('#print-button').on('click', function (e) {
    let data = {
        id_kuitansi: $(this).parents('.modal').find('#id-kuitansi').data('id-kuitansi'),
        token: body.getAttribute('data-token')
    };

    fetch('/donatur/fetch/update/kuitansi', {
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
        if (result.error) {
            $('.toast[data-toast="donatur"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
            $('.toast[data-toast="donatur"] .toast-header strong').text('Peringatan!');
            console.log('there is some error in server side');
            $('.toast').toast('show');
        } else {
            window.print();
            $('.toast[data-toast="donatur"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            $('.toast[data-toast="donatur"] .toast-header strong').text('Informasi');
        }
        $('.toast[data-toast="donatur"] .time-passed').text('Baru Saja');
        $('.toast[data-toast="donatur"] .toast-body').html(result.feedback.message);
        body.setAttribute('data-token', result.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
    });
});