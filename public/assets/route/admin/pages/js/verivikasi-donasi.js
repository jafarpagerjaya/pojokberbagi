let dataVerivikasi = {};
const dateRanges = (date = new Date(), rule = 10, sum = 0) => Math.floor(date.getFullYear() / rule) * rule + sum;
$('#modalValidasiDonasi').on('hidden.bs.modal', function () {
    dataVerivikasi = {};
}).on('shown.bs.modal', function () {
    const checkDoa = $(this).find('#doa_check');
    if (checkDoa.length) {
        checkDoa.on('click', function () {
            dataVerivikasi.check = true
        });
    }

    let d = new Date('08/11/2021');
    $(this).find('.datepicker').datepicker({
        todayBtn: "linked",
        language: 'id',
        format: 'd MM yyyy',
        autoclose: true,
        maxViewMode: 2,
        endDate: '+1',
        enableOnReadonly: false // readonly input will not show datepicker . The default value true
    }).on('changeDate', function (e) {
        // dataVerivikasi.payment_date = e.date;
        dataVerivikasi.payment_date = $(this).datepicker('getFormattedDate');


        $(this).hide();
        if (!$('#ganti-tanggal').length) {
            $(this).closest('.box').prepend('<div class="px-2 mb-3 d-flex justify-content-center align-items-center gap-3"><span class="font-weight-bolder text-muted">' + $(this).datepicker('getFormattedDate') + '</span><a href="#" class="font-weight-bolder text-orange small" id="ganti-tanggal">Ganti Tanggal</a></div>');
        }

        $(this).closest('.box').find('#date-type-text').text('Waktu');
        $(this).closest('.box').find('.timepicker').show();
        if (!$('#modalValidasiDonasi').find('.timepicker > .bootstrap-datetimepicker-widget').length) {
            $('#modalValidasiDonasi').find('.timepicker').datetimepicker({
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
            $('#modalValidasiDonasi').find('.timepicker .waktu-sekarang').html('<span class="font-weight-bold">Waktu Sekarang</span>');
            $('#modalValidasiDonasi').find('.timepicker .clear-waktu').html('<span class="font-weight-bold">Clear Waktu</span>');

            $('#modalValidasiDonasi').find('.timepicker').on('click', 'tr:nth-child(2) > td', function () {
                $(this).siblings('td').removeClass('active');
                $(this).addClass('active');
            }).on('click', 'tr:not(:nth-child(2)) > td', function () {
                $(this).closest('table').find('tr:nth-child(2)').children('td').eq($(this).index()).siblings('td').removeClass('active');
                $(this).closest('table').find('tr:nth-child(2)').children('td').eq($(this).index()).addClass('active');
            });

            $('#modalValidasiDonasi').find('.timepicker .clear-waktu').click(function () {
                $(this).parents('.timepicker').data("DateTimePicker").date(null);
                $(this).parents('.timepicker').data("DateTimePicker").date('00:00:00');
            });
        }
    }).datepicker('setStartDate', d);

    $(this).on('click', '#ganti-tanggal', function (e) {
        $(this).closest('.box').find('.timepicker').hide();
        $('#date-type-text').text('Tanggal');

        $(this).closest('.box').find('.datepicker').show();
        $(this).closest('.d-flex').remove();
        e.preventDefault();
    });
}).on('hide.bs.modal', function () {
    $(this).find('.datepicker').show();
    $(this).find('.datepicker').datepicker('destroy');

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
});

const verivikasiBtn = $('#modalValidasiDonasi').find('button[type="submit"]');
verivikasiBtn.on('click', function (e) {
    dataVerivikasi.waktu_bayar = new Date(dataVerivikasi.payment_date + ' ' + dataVerivikasi.payment_time);

    if (dataVerivikasi.waktu_bayar == 'Invalid Date') {
        // tampilkan pesan waktu bayar wajib diisi

        e.preventDefault();
        return false;
    }

    console.log(dataVerivikasi);
});