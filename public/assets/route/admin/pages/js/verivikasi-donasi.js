let dataVerivikasi = {}, modal;
$('#modalValidasiDonasi').on('hidden.bs.modal', function () {
    dataVerivikasi = {};
}).on('show.bs.modal', function () {
    if ($(this).find('.datepicker').length == 0) {
        modal = $(this);

        let d = new Date('08/11/2021');
        modal.find('#datepicker').datepicker({
            todayBtn: "linked",
            language: 'id',
            format: 'd MM yyyy',
            autoclose: true,
            maxViewMode: 2,
            endDate: '+1',
            enableOnReadonly: false // readonly input will not show datepicker . The default value true
        }).on('changeDate', function (e) {
            const eDate = e.date,
                  year = eDate.getFullYear(),
                  month = eDate.getMonth() + 1,
                  date = eDate.getDate();

            dataVerivikasi.payment_date = year +'-'+ month +'-'+ date;
            
            $(this).hide();
            if (!$('#ganti-tanggal').length) {
                $(this).closest('.box').prepend('<div class="px-2 mb-3 d-flex justify-content-center align-items-center gap-3"><span class="font-weight-bolder text-muted">' + $(this).datepicker('getFormattedDate') + '</span><a href="#" class="font-weight-bolder text-orange small" id="ganti-tanggal">Ganti Tanggal</a></div>');
            }

            // Lupa lagi ini buat apa dan ambil data dari mana
            // $('#'+ toastLocal.id +'.toast[data-toast="'+ toastLocal.data_toast +'"]').toast('hide');

            $(this).closest('.box').find('#date-type-text').text('Waktu');
            $(this).closest('.box').find('.timepicker').show();
            if (!modal.find('.timepicker > .bootstrap-datetimepicker-widget').length) {
                modal.find('.timepicker').datetimepicker({
                    format: 'HH:mm:ss',
                    inline: true,
                    icons: {
                        up: "fa fa-solid fa-angle-up",
                        down: "fa fa-solid fa-angle-down",
                        today: 'waktu-sekarang',
                        clear: 'clear-waktu'
                    },
                    tooltips: {
                        today: 'Set waktu saat ini',
                        clear: 'Set waktu ke 00:00:00'
                    },
                    showTodayButton: true,
                    showClear: true
                }).on("dp.change", function (e) {
                    if ($(this).find('.timepicker-picker').is(':hidden')) {
                        $(this).find('.timepicker-picker').show();
                        $(this).find('.timepicker-picker').siblings().hide();
                    }
                    if ($(this).find('.timepicker-picker > table > tr:nth-child(2) > td.active').length) {
                        setTimeout(() => {
                            $(this).find('.timepicker-picker > table > tr:nth-child(2) > td.active').removeClass('active')
                        }, 900);
                    }

                    if (e.date != false) {
                        dataVerivikasi.payment_time = e.date.format('HH:mm:ss');
                    } else {
                        dataVerivikasi.payment_time = '00:00:00';
                        return;
                    }
                }).data("DateTimePicker").date('00:00:00');
                modal.find('.timepicker .waktu-sekarang').html('<span class="font-weight-bold">Waktu Sekarang</span>');
                modal.find('.timepicker .clear-waktu').html('<span class="font-weight-bold">Clear Waktu</span>');

                modal.find('.timepicker').on('click', 'tr:nth-child(2) > td', function () {
                    $(this).siblings('td').removeClass('active');
                    $(this).addClass('active');
                }).on('click', 'tr:not(:nth-child(2)) > td', function () {
                    $(this).closest('table').find('tr:nth-child(2)').children('td').eq($(this).index()).siblings('td').removeClass('active');
                    $(this).closest('table').find('tr:nth-child(2)').children('td').eq($(this).index()).addClass('active');
                });

                modal.find('.timepicker .clear-waktu').click(function () {
                    $(this).parents('.timepicker').data("DateTimePicker").date(null);
                    $(this).parents('.timepicker').data("DateTimePicker").date('00:00:00');
                });
            }
        }).datepicker('setStartDate', d);
    }
}).on('shown.bs.modal', function (e) {
    const checkDoa = $(this).find('#doa_check');
    if (checkDoa.length) {
        checkDoa.on('click', function () {
            dataVerivikasi.check = true
        });
    }

    const id = e.relatedTarget.dataset.id,
          target = (e.relatedTarget.closest('tr').getAttribute('data-order-id') != null && e.relatedTarget.closest('#list-order-donasi') != null ? 'order_donasi' : 'donasi');

    let dataTarget = {
        table: target,
        token: body.getAttribute('data-token')
    };

    dataTarget['id_'+target] = id;
    dataVerivikasi['id_'+target] = id;

    modal = $(this);

    fetch('/admin/fetch/get/'+target, {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body: JSON.stringify(dataTarget)
    })
    .then(response => response.json())
    .then(function(result) {
        // console.log(result);
        if (result.error) {
            createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                delay: 10000
            }).toast('show');
            console.log('there is some error in server side');
            $('.toast').toast('show');
            return false;
        }

        if (result.feedback.data.status == 'S') {
            if (!modal.find('.container>.row>[data-status-bantuan="S"]').length) {
                let boxWarning = '<div class="col-12" data-status-bantuan="S"><div class="box rounded-box bg-gradient-danger text-white"><div class="px-2"><h3 class="mb-0 text-white">Bantuan ini sudah di <b>Take Down !!</b></h3><span>Anda diwajiban sesegera mungkin untuk menginput data pengeluaran donasi ini jika <b>memverivikasinya</b></span></div></div></div>';
                modal.find('.container>.row').append(boxWarning);
            }
        } else {
            if (modal.find('.container>.row>[data-status-bantuan="S"]').length) {
                modal.find('.container>.row>[data-status-bantuan="S"]').remove();
            }
        }

        // console.log(result.feedback.data);

        modal.find('img#donatur-avatar').attr('src', result.feedback.data.path_gambar_avatar);
        modal.find('img#donatur-avatar').attr('alt', result.feedback.data.nama_avatar);
        modal.find('#donatur-name').text(result.feedback.data.nama_donatur);
        modal.find('#donatur-email').text(result.feedback.data.email);
        modal.find('#nama-bantuan').text(result.feedback.data.nama_bantuan);
        modal.find('span#jumlah-donasi').text(result.feedback.data.jumlah_donasi);
        modal.find('span#jenis-cp').text(keteranganJenisChannelPayment(result.feedback.data.jenis));
        modal.find('img#donasi-cp').attr('src', result.feedback.data.path_gambar_cp);
        modal.find('img#donasi-cp').attr('alt', result.feedback.data.nama_cp);
        modal.find('span.create-at').text(dateToID(result.feedback.data.create_at));
        
        if (result.feedback.data.pay_gate != null) {
            console.log(modal.find('#jenis-cp-verivication'));
            modal.find('#jenis-cp-verivication').append('<span class="badge badge-warning px-2 py-1 pay_gate">'+ result.feedback.data.pay_gate +'</span>');
        }

        if (result.feedback.data.doa != null) {
            if (modal.find('#pesan-doa-is-null').length) {
                modal.find('#pesan-doa-is-null').remove();
            }
            if (modal.find('#pesan-doa').length == 0) {
                let div = '<div class="col-12" id="pesan-doa"><div class="row"><div class="col-12"><div class="box bg-lighter rounded-box"><div class="px-2"><h3>Doa dan Pesan Donatur</h3><p></p></div></div></div><div class="col-12 mt-3 mt-md-0"><div class="box"><div class="px-2"><div class="inputGroup"><input type="checkbox" name="doa_check" id="doa_check"><label for="doa_check">Tampilkan doa dan pesan donatur</label></div></div></div></div></div></div>';
                modal.find('#doa-dan-tanpa-doa').append(div);
            }
            modal.find('#pesan-doa .box>.px-2>p').text(result.feedback.data.doa);
        } else {
            if (modal.find('#pesan-doa').length) {
                modal.find('#pesan-doa').remove();
            }
            if (modal.find('#pesan-doa-is-null').length == 0) {
                let div = '<div class="col-12" id="pesan-doa-is-null"><div class="box bg-lighter rounded-box"><div class="px-2"><p class="m-0">Tidak ada doa yang ditulis oleh donatur</p></div></div></div>';
                modal.find('#doa-dan-tanpa-doa').append(div);
            }
        }

        let d = new Date(result.feedback.data.create_at);
        modal.find('#datepicker').datepicker('setStartDate', d);

        body.setAttribute('data-token', result.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
    });

    $(this).on('click', '#ganti-tanggal', function (e) {
        $(this).closest('.box').find('.timepicker').hide();
        $('#date-type-text').text('Tanggal');

        $(this).closest('.box').find('#datepicker').show();
        $(this).closest('.d-flex').remove();
        e.preventDefault();
    });
}).on('hide.bs.modal', function () {
    $(this).find('#datepicker').show();
    $(this).find('#datepicker').datepicker('update', null);

    if ($(this).find('.timepicker > .bootstrap-datetimepicker-widget').length) {
        $(this).find('.timepicker').data("DateTimePicker").destroy();
    }

    if ($(this).find('#ganti-tanggal').length) {
        $(this).find('#ganti-tanggal').parent().remove();
    }

    const checkDoa = $(this).find('#doa_check');
    if (checkDoa.length) {
        checkDoa.prop('checked', false)
    }

    stopPassed($('.toast[data-toast="feedback"]').data('toast'));
    $('.toast').toast("dispose");

    document.getElementById('datepicker').parentElement.classList.remove('border','border-warning');
}).on('hidden.bs.modal', function() {
    if ($(this).find('#pesan-doa').length) {
        modal.find('#pesan-doa .box>.px-2>p').text('');
    }

    if (modal.find('#jenis-cp-verivication .pay_gate') != null) {
        modal.find('#jenis-cp-verivication .pay_gate').remove();
    }
});

const verivikasiBtn = $('#modalValidasiDonasi').find('button[type="submit"]');

let toastLocal;
verivikasiBtn.on('click', function (e) {
    dataVerivikasi.waktu_bayar = new Date(dataVerivikasi.payment_date + ' ' + dataVerivikasi.payment_time);

    if (dataVerivikasi.waktu_bayar == 'Invalid Date') {
        // tampilkan pesan waktu bayar wajib diisi
        let currentDate = new Date(),
            timestamp = currentDate.getTime(); 

        let invalid = {
            error: true,
            data_toast: 'invalid-donasi',
            feedback: {
                message: '<b>Waktu bayar</b> donasi wajib diisi'
            }
        };

        invalid.id = invalid.data_toast +'-'+ timestamp;
        
        createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
        $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
            delay: 10000
        }).toast('show');

        toastLocal = invalid;

        document.getElementById('datepicker').parentElement.classList.add('border','border-warning');
        e.preventDefault();
        return false;
    }

    let target;
    if (dataVerivikasi.id_donasi != null && dataVerivikasi.id_order_donasi == null) {
        target = 'donasi';
    } else if (dataVerivikasi.id_donasi == null && dataVerivikasi.id_order_donasi != null) {
        target = 'order_donasi';
    } else {
        let invalid = {
            error: true,
            data_toast: 'invalid-donasi',
            feedback: {
                message: 'target update verivikasi tidak dikenal'
            }
        };

        invalid.id = invalid.data_toast +'-'+ timestamp;
        
        createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
        $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
            delay: 10000
        }).toast('show');

        toastLocal = invalid;

        document.getElementById('datepicker').parentElement.classList.add('border','border-warning');
        e.preventDefault();
        return false;
    }

    dataVerivikasi.token = body.getAttribute('data-token');

    fetch('/admin/fetch/verivikasi/'+target, {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body: JSON.stringify(dataVerivikasi)
    })
    .then(response => response.json())
    .then(function(result) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        if (result.error) {
            $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
                autohide: false
            }).toast('show');
            return false;
        }

        body.setAttribute('data-token', result.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });

        if (relatedTarget == 'order-donasi') {
            document.querySelector('tr[data-order-id="'+ dataVerivikasi.id_order_donasi +'"').remove();
            if (document.querySelector('#list-'+relatedTarget+' tbody>tr') == null) {
                document.querySelector('#list-'+relatedTarget+' tbody').insertAdjacentHTML('beforeend','<tr><td colspan="4">Data donasi tidak ditemukan...</td></tr>');
            }
            document.getElementById('list-donasi').querySelector('.pagination .page-link.active').click();
        } else {
            let elIdDonasi = $('table tbody>tr a[data-id="' + parseInt(result.feedback.data.id_donasi) + '"].verivikasi');
            if (elIdDonasi.length) {
                elIdDonasi.parents('tr').addClass('highlight');
                elIdDonasi.parents('tr').find('span.badge-warning').text('sudah diverivikasi');
                elIdDonasi.parents('tr').find('span.badge-warning').addClass('badge-success');
                elIdDonasi.parents('tr').find('span.badge-success').removeClass('badge-warning');
                elIdDonasi.text('Sudah Terverivikasi');
                elIdDonasi.removeAttr('data-id');
                elIdDonasi.removeAttr('data-toggle');
                elIdDonasi.removeAttr('data-target');
                elIdDonasi.attr('class', 'dropdown-item disabled');

                setTimeout(() => {
                    elIdDonasi.parents('tr').removeClass('highlight');
                }, 3100);
            }
        }

        modal.modal('hide');

        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast('show');
    });
});