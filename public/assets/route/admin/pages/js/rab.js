let data = {},
    relatedModal,
    relatedTarget;

const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
const btnNewRencana = document.querySelector('.btn[data-target="#modalBuatRencana"]');
btnNewRencana.addEventListener('click', function(e) {
    let modal = this.getAttribute('data-target');
    // document.querySelector(modal + " #step-rencana span.date").innerText = new Date().toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');
});

// let timeModalBuatRencana;
const tableListRencana = document.querySelector('#list-rencana');
$('#modalBuatRencana').on('hidden.bs.modal', function (e) {
    e.target.querySelector('#id-bantuan').removeAttribute('disabled');

    let tab = e.target.querySelector('#tab-rencana-pencairan'),
        activeTab = tab.querySelector('.tab-pane.active.show'),
        indexActiveTab = findIndex(activeTab);

    e.target.querySelectorAll('[name]').forEach(name => {
        if (!name.parentElement.classList.contains('is-invalid')) {
            if (indexActiveTab < findIndex(name.closest('.tab-pane'))) {
                return false;
            }
            if (name.tagName.toLowerCase() == 'select') {
                name.value = '0';

                // for select2 ok
                $('#' + name.getAttribute('id')).select2('val', '0');
            } else if (name.value.length && name.tagName.toLowerCase() != 'select') {
                name.value = '';
            }

            if (name.hasAttribute('maxlength')) {
                name.parentElement.querySelector('.current-length').innerText = name.value.length;
            }
            return;
        }
        name.parentElement.classList.remove('is-invalid');
        name.parentElement.querySelector('label').removeAttribute('data-label-after');
        name.classList.remove('is-invalid');
    });

    e.target.querySelectorAll('#id-bantuan option[disabled]').forEach(option => {
        option.removeAttribute('disabled');
    });
    e.target.querySelector('#input-keterangan-rencana').removeAttribute('disabled');
    e.target.querySelector('#action .btn[data-reactive-btn="true"]').innerText = 'Buat RAB';
    e.target.querySelector('#action .btn[data-reactive-btn="true"]').setAttribute('id', 'buat-rab');
    e.target.querySelector('#action .btn[data-reactive-btn="true"]').setAttribute('type', 'submit');
    e.target.querySelector('#action [data-dismiss="modal"]').innerText = 'Batal';

    if (e.target.querySelector('#rencana #balance') != null) {
        e.target.querySelector('#rencana #balance').parentElement.remove();
    }

    if (e.target.querySelector('#rab') != null) {
        document.getElementById('rab').remove();
    }

    if (activeTab.getAttribute('id') != 'tab-rencana') {
        e.target.querySelector('#total-rab').innerText = 0;
        e.target.querySelector('#anggaran-tersedia').innerText = 0;

        activeTab.classList.remove('show');
        activeTab.classList.remove('active');
        e.target.querySelector('#tab-rencana-pencairan #tab-rencana').classList.add('active');
        e.target.querySelector('#tab-rencana-pencairan #tab-rencana').classList.add('show');

        objectAnggaran = {};
    }

    if (activeTab.getAttribute('id') == 'tab-pencairan') {
        activeTab.querySelector('#saldo-rab').innerText = 0;
        activeTab.querySelector('#anggaran-tersedia').innerText = 0;
    }

    const tableList = e.target.querySelectorAll('.tab-pane table');
    if (tableList.length) {
        tableList.forEach(table => {
            if (table.querySelectorAll('tbody>tr').length) {
                table.querySelectorAll('tbody>tr').forEach(tr => {
                    tr.remove();
                });
            }
        });
    }

    if (objectRencana.id_rencana != null) {
        if (tableListRencana.querySelector('tbody>tr[data-id-rencana="'+ objectRencana.id_rencana +'"]') != null) {
            tableListRencana.querySelector('tbody>tr[data-id-rencana="'+ objectRencana.id_rencana +'"]').classList.add('highlight');
            setTimeout(()=> {
                tableListRencana.querySelector('tbody>tr.highlight').classList.remove('highlight');
            }, 3000);
        }
    }

    if (e.target.querySelector('#budget-warning') != null) {
        e.target.querySelector('#budget-warning').remove();
    }

    e.target.querySelectorAll('#stepper ol li.c-stepper__item').forEach(el => {
        if (el.getAttribute('id') == 'step-rencana') {
            return;
        }

        el.remove();
    });

    delete data.fields;
    objectRencana = {};
    objectPenganggaran = {};
    if (typeof objectPenganggaran == 'object') {
        if ($('.toast[data-toast] .small-box.bg-danger').length) {
            $('.toast[data-toast] .small-box.bg-danger').each(function(e) {
                $(this).closest('.toast[data-toast]').toast('hide');
            });
        }
    }
    // clearInterval(timeModalBuatRencana);
}).on('hide.bs.modal', function (e) {
    if (objectRencana.id_rencana != null) {
        tableListRencana.closest('.card').querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    }
}).on('shown.bs.modal', function (e) {
    // timeModalBuatRencana = setInterval(() => {
    //     e.target.querySelector('.c-stepper>#step-rencana span.date').innerText = new Date().toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');
    // }, 1000);
});

$('#modalPerubahanRAB').on('show.bs.modal', function (e) {
    relatedTarget = e.relatedTarget.closest('tr');
    objectRencana.id_rencana = relatedTarget.getAttribute('data-id-rencana');
    let data = {
        id_rencana: objectRencana.id_rencana,
        token: document.querySelector('body').getAttribute('data-token')
    };

    fetchData('/admin/fetch/get/rab/detil', data, e.currentTarget, 'get-rab-detil');
}).on('hide.bs.modal', function (e) {
    console.log(objectRencana);
    if (objectRencana.id_rencana != null && objectRencana.dml == true) {
        tableListRencana.closest('.card').querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
    }
}).on('hidden.bs.modal', function (e) {
    if (objectRencana.id_rencana != null && objectRencana.dml == true) {
        if (tableListRencana.querySelector('tbody>tr[data-id-rencana="'+ objectRencana.id_rencana +'"]') != null) {
            tableListRencana.querySelector('tbody>tr[data-id-rencana="'+ objectRencana.id_rencana +'"]').classList.add('highlight');
            setTimeout(()=> {
                tableListRencana.querySelector('tbody>tr.highlight').classList.remove('highlight');
            }, 3000);
        }
    }
    
    if (e.currentTarget.querySelector('#budget-warning') != null) {
        e.currentTarget.querySelector('#budget-warning').remove();
    }

    e.currentTarget.querySelectorAll('table tbody>tr').forEach(el => {
        el.remove();
    });

    e.currentTarget.querySelector('#nama-bantuan-update').innerText = 'data.rencana.nama_bantuan';
    e.currentTarget.querySelector('#total-anggaran-update').innerText = 'data.rencana.total_anggaran';
    e.currentTarget.querySelector('#saldo-donasi-update').innerText = 'data.rencana.max_anggaran';
    e.currentTarget.querySelector('#keterangan-rencana-update').innerText = 'data.rencana.keterangan';
    e.currentTarget.querySelector('#nama-pembuat-rencana-update').innerText = 'data.rencana.nama_pembuat';
    e.currentTarget.querySelector('#waktu-buat-rencana-update').innerText = 'data.rencana.create_at';
    e.currentTarget.querySelector('#waktu-pembaharuan-rencana-update').innerText = 'data.rencana.modified_at';
    e.currentTarget.querySelector('#status-rencana-update').innerText = 'data.rencana.status.text';
    e.currentTarget.querySelector('#total-teranggarkan-rencana-update').innerText = 'data.rencana.total_teranggarkan';
    e.currentTarget.querySelector('#belum-teranggarkan-rencana-update').innerText = 'data.rencana.total_anggaran - data.rencana.total_teranggarkan';

    data = {};
    objectRencana = {};
});

$('#modalFormRab').on('show.bs.modal', function (e) {
    if (e.target.querySelector('#input-keterangan-rab').hasAttribute('maxlength')) {
        e.target.querySelector('#input-keterangan-rab').parentElement.querySelector('.current-length').innerText = e.target.querySelector('#input-keterangan-rab').value.length;
    }
}).on('hide.bs.modal', function (e) {
    if (e.target.getAttribute('data-related-modal') != null) {
        e.target.removeAttribute('data-related-modal');
    }
}).on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    } else if (document.getElementById('modalPerubahanRAB').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }

    const mode = e.target.querySelector('#formJudul').getAttribute('data-mode');
    if (mode == 'update') {
        // e.target.querySelector('#id-kebutuhan').value = '0';
        e.target.querySelector('#input-harga-satuan').value = '';
        e.target.querySelector('#input-jumlah').value = '';
        e.target.querySelector('#input-keterangan-rab').value = '';

        const defaultKebutuhan = selectLabelKebutuhan(x);
        resetDataSelect2($('#id-kebutuhan'), defaultKebutuhan);
        document.querySelector('#id-kebutuhan').insertAdjacentHTML('afterbegin','<option value="0" selected disabled hidden>Pilih salah satu</option>');
    }

    e.target.querySelectorAll('[name]').forEach(name => {
        if (!name.parentElement.classList.contains('is-invalid')) {
            return;
        }
        name.parentElement.classList.remove('is-invalid');
        name.parentElement.querySelector('label').removeAttribute('data-label-after');
        name.classList.remove('is-invalid');
    });

    delete data.id_rab;
});

$('#modalTambahKebutuhan').on('show.bs.modal', function () {
    console.log(objectKebutuhan);
}).on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }

    e.target.querySelectorAll('[name]').forEach(name => {
        if (!name.parentElement.classList.contains('is-invalid')) {
            return;
        }
        name.parentElement.classList.remove('is-invalid');
        name.parentElement.querySelector('label').removeAttribute('data-label-after');
        name.classList.remove('is-invalid');
    });
});

$('#modalKonfirmasiHapusRab').on('hidden.bs.modal', function () {
    if (document.getElementById('modalBuatRencana').classList.contains('show') || document.getElementById('modalPerubahanRAB').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
});

$('#modalRincianRAB').on('show.bs.modal', function (e) {
    relatedTarget = e.relatedTarget.closest('tr');
    objectRencana.id_rencana = relatedTarget.getAttribute('data-id-rencana');
    let data = {
        id_rencana: objectRencana.id_rencana,
        token: document.querySelector('body').getAttribute('data-token')
    };
    fetchData('/admin/fetch/get/rencana', data, e.currentTarget, 'get-rencana');
}).on('shown.bs.modal', function (e) {
    let tableAbsoluteFirstList = e.target.querySelectorAll('table[table-absolute-first="on"]');
    if (tableAbsoluteFirstList.length > 0) {
        tableAbsoluteFirstList.forEach(table => {
            if (table.classList.contains('table-responsive')) {
                doAbsoluteFirstAdd(table);
                if (table.querySelector('tbody tr[data-zero="true"]') == null) {
                    table.classList.add('table-absolute-first');
                }
            } else {
                doAbsoluteFirstRemove(table);
            }
        });
    }
}).on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
    
    if (e.currentTarget.querySelector('#kelola-rab') != null) {
        e.currentTarget.querySelector('#kelola-rab').remove();
    }

    e.currentTarget.querySelectorAll('body tbody tr').forEach(el => {
        el.remove();
    });

    objectRencana = {};
}).on('show.bs.modal', function(e) {
    let tableAbsoluteFirstList = e.target.querySelectorAll('table.table-absolute-first');
    if (tableAbsoluteFirstList.length > 0) {
        tableAbsoluteFirstList.forEach(table => {
            if (table.classList.contains('table-responsive')) {
                table.classList.remove('table-absolute-first');
                table.setAttribute('table-absolute-first','on');
            }
        });
    }
});

$('#modalKeteranganPerbaikanRAB').on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalRincianRAB').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }

    e.target.querySelectorAll('[name]').forEach(name => {
        if (!name.parentElement.classList.contains('is-invalid')) {
            return;
        }
        name.parentElement.classList.remove('is-invalid');
        name.parentElement.querySelector('label').removeAttribute('data-label-after');
        name.classList.remove('is-invalid');
    });
});

$('#modalKonfirmasiAksi').on('show.bs.modal', function (e) {
    const mode = e.relatedTarget.getAttribute('data-mode');
    let desc;
    switch (mode) {
        case 'TD':
            desc = 'tidak melanjutkan';
            break;
        case 'SD':
            desc = 'melanjutkan'
            break;
        case 'BP':
            desc = 'perbaikan'
            break;
        default:
            console.log('Unrecognize action mode');
            break;
    }
    e.target.querySelector('.text-sm > span').innerText = desc;
    objectRencana.status = mode;
    relatedModal = e.relatedTarget.closest('.modal').getAttribute('id');
}).on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalRincianRAB').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
});

$('#modalDetilPinbuk').on('show.bs.modal', function(e) {
    e.target.querySelectorAll('tbody tr').forEach(element => {
        element.remove();
    });

    const trEl = e.relatedTarget.closest('tr');
    let data = {
        id_ca: trEl.getAttribute('data-id-ca'),
        id_pelaksanaan: objectPencairan.id_pelaksanaan,
        id_pencairan: objectPencairan.id_pencairan,
        persentase_pencairan: objectPencairan.persentase_penarikan,
        token: document.querySelector('body').getAttribute('data-token')
    };

    // fetchReadDetilPenarikanHasPinbuk
    fetchData('/admin/fetch/read/rekalkulasi-penarikan/detil-penarikan-pinbuk', data, e.currentTarget, 'read-detil-penarikan-pinbuk');
}).on('hidden.bs.modal', function(e) {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }

    e.target.querySelectorAll('tbody tr').forEach(element => {
        element.remove();
    });
});

let objectListPenarikan = {},
    objectCaPengirim = {};
$('#modalFormPinbuk').on('show.bs.modal', function (e) {
    const trEl = e.relatedTarget.closest('tr');
    objectCaPengirim.id_ca = trEl.getAttribute('data-id-ca');
    objectCaPengirim.nama_ca = trEl.querySelector('img').getAttribute('alt');
    let data = {
        pengirim: objectCaPengirim,
        id_pencairan: objectPencairan.id_pencairan,
        id_pelaksanaan: objectPencairan.id_pelaksanaan,
        persentase_penarikan: objectPencairan.persentase_penarikan,
        token: document.querySelector('body').getAttribute('data-token')
    };

    // fetchGetKalkulasiPenarikanCA
    fetchData('/admin/fetch/get/kalkulasi/penarikan-channel-account', data, e.currentTarget, 'get-kalkulasi-penarikan-ca');
}).on('hidden.bs.modal', function (e) {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }

    e.target.querySelector('#input-nominal-pinbuk').value = '';
    e.target.querySelector('#input-keterangan-pinbuk').value = '';
    e.target.querySelector('#sudah-pinbuk').checked = false;
    if (e.target.querySelector('.inputGroup.input-file') != null) {
        e.target.querySelector('.inputGroup.input-file').remove();
    }
    // for select2
    $('#id-ca-penerima').val(0).trigger('change');

    objectCaPengirim = {};
    objectPinbuk = {};
    cropedFile = {};
});

$('#id-ca-pengirim').select2({
    data: [],
    disabled: 'readonly',
    dropdownParent: $('#modalFormPinbuk'),
    escapeMarkup: function (markup) { return markup; },
    templateSelection: formatSelectedChannelAccount
});

// Select
const selectBantuan = document.getElementById('id-bantuan');

let objectRencana = {};
selectBantuan.addEventListener('change', function () {
    if (this.value != '0') {
        objectRencana.id_bantuan = this.value;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectRencana.id_bantuan;
    }
});

const selectKebutuhan = document.getElementById('id-kebutuhan');

let objectRab = {};
// event ini jalan hanya untuk non select2
selectKebutuhan.addEventListener('change', function () {
    if (this.value != '0') {
        objectRab.id_kebutuhan = this.value;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectRab.id_kebutuhan;
    }
});

const selectKategori = document.getElementById('id-kk');

let objectKebutuhan = {};
selectKategori.addEventListener('change', function () {
    if (this.value != '0') {
        objectKebutuhan.id_kk = this.value;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectKebutuhan.id_kk;
    }
});

// Textarea
modalNameListKeyDownTextarea = document.querySelectorAll('.modal textarea');

modalNameListKeyDownTextarea.forEach(name => {
    name.addEventListener('keydown', function (e) {

        if (!e.target.value.length && (e.keyCode == 16 || e.code == 'Space' || e.code == 'Backspace' || e.code == 'Delete' || e.code == 'ArrowDown' || e.code == 'ArrowUp' || e.code == 'ArrowLeft' || e.code == 'ArrowRight')) {
            return false;
        }

        if (e.code != undefined) {
            if (!e.target.value.length && (e.code.indexOf('Key') < 0 && e.code.indexOf('Digit') < 0 && (e.keyCode != 96 && e.keyCode != 97 && e.keyCode != 98 && e.keyCode != 99 && e.keyCode != 100 && e.keyCode != 101 && e.keyCode != 102 && e.keyCode != 103 && e.keyCode != 104 && e.keyCode != 105))) {
                e.preventDefault();
                return false;
            }
        }

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.nextElementSibling.removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    });

    name.addEventListener('paste', function (e) {
        setTimeout(() => {
            this.value = escapeRegExp(escapeRegExp(this.value.trim(), '', /[^a-zA-Z0-9\s\/.-]/g), ' ', /\s+/g);

            if (!this.value.length) {
                return false;
            }

            if (this.parentElement.classList.contains('is-invalid')) {
                this.parentElement.classList.remove('is-invalid');
                this.nextElementSibling.removeAttribute('data-label-after');
                this.classList.remove('is-invalid');
            }
        }, 0);
    });
});

// Input
const modalNameListKeyDownInput = document.querySelectorAll('.modal input');

modalNameListKeyDownInput.forEach(name => {
    name.addEventListener('keydown', function (e) {

        if (!e.target.value.length && (e.keyCode == 16 || e.code == 'Space' || e.code == 'Backspace' || e.code == 'Delete' || e.code == 'ArrowDown' || e.code == 'ArrowUp' || e.code == 'ArrowLeft' || e.code == 'ArrowRight')) {
            return false;
        }

        if (e.code != undefined) {
            if (!e.target.value.length && (e.code.indexOf('Key') < 0 && e.code.indexOf('Digit') < 0 && (e.keyCode != 96 && e.keyCode != 97 && e.keyCode != 98 && e.keyCode != 99 && e.keyCode != 100 && e.keyCode != 101 && e.keyCode != 102 && e.keyCode != 103 && e.keyCode != 104 && e.keyCode != 105))) {
                e.preventDefault();
                return false;
            }
        }

        setTimeout(() => {
            if (this.parentElement.classList.contains('is-invalid') && this.value.length) {
                this.parentElement.classList.remove('is-invalid');
                this.nextElementSibling.removeAttribute('data-label-after');
                this.classList.remove('is-invalid');
            }
        }, 0);
    });

    name.addEventListener('paste', function (e) {
        setTimeout(() => {
            this.value = escapeRegExp(escapeRegExp(this.value.trim(), '', /[^a-zA-Z0-9\s\/.-]/g), ' ', /\s+/g);

            if (!this.value.length) {
                return false;
            }

            if (this.parentElement.classList.contains('is-invalid')) {
                this.parentElement.classList.remove('is-invalid');
                this.nextElementSibling.removeAttribute('data-label-after');
                this.classList.remove('is-invalid');
            }
        }, 0);
    });
});

const inputKeteranganRencana = document.getElementById('input-keterangan-rencana');

inputKeteranganRencana.addEventListener('change', function () {
    if (this.value.length) {
        objectRencana.keterangan = this.value;
    } else {
        delete objectRencana.keterangan;
    }
});

const formRab = document.getElementById('modalFormRab'),
    inputRabList = formRab.querySelectorAll('input');

inputRabList.forEach(inputRab => {
    inputRab.addEventListener('focusout', function () {
        if (oldValuePrice[this.name] == this.value) {
            return false;
        }

        oldValuePrice[this.name] = this.value;

        if (this.value.length) {
            objectRab[this.name] = this.value;
        } else {
            delete objectRab[this.name];
        }
    });
});

function setNominalPenarikan(e) {
    const persentase_penarikan = +e.target.value.replace(' %', '');
    let nominal_penarikan = '';
    if (persentase_penarikan > 0) {
        nominal_penarikan = numberToPrice(Math.round(objectPelaksanaan.total_anggaran * (persentase_penarikan / 100).toFixed(4)));
        if (e.target.parentElement.classList.contains('is-invalid')) {
            e.target.parentElement.classList.remove('is-invalid');
            e.target.parentElement.querySelector('label').removeAttribute('data-label-after');
            e.target.classList.remove('is-invalid');
        }
    }
    document.getElementById('input-nominal-penarikan').value = nominal_penarikan;
    objectPencairan[e.target.name] = persentase_penarikan;
    objectPencairan[document.getElementById('input-nominal-penarikan').getAttribute('name')] = priceToNumber(nominal_penarikan);

    const elSebrang = document.getElementById('input-nominal-penarikan');
    if (elSebrang.parentElement.classList.contains('is-invalid') && persentase_penarikan != '') {
        elSebrang.parentElement.classList.remove('is-invalid');
        elSebrang.parentElement.querySelector('label').removeAttribute('data-label-after');
        elSebrang.classList.remove('is-invalid');
    }
}

function setPersentasePenarikan(e) {
    const nominal_penarikan = priceToNumber(e.target.value);
    let persentase_penarikan = '';
    if (nominal_penarikan > 0) {
        if (nominal_penarikan == objectPelaksanaan.total_anggaran) {
            persentase_penarikan = '100 %';
        } else {
            persentase_penarikan = ((nominal_penarikan / objectPelaksanaan.total_anggaran) * 100).toFixed(2) + ' %';
        }
        if (e.target.parentElement.classList.contains('is-invalid')) {
            e.target.parentElement.classList.remove('is-invalid');
            e.target.parentElement.querySelector('label').removeAttribute('data-label-after');
            e.target.classList.remove('is-invalid');
        }
    }
    document.getElementById('input-persentase-penarikan').value = persentase_penarikan;
    objectPencairan[e.target.name] = nominal_penarikan;
    objectPencairan[document.getElementById('input-persentase-penarikan').getAttribute('name')] = +persentase_penarikan.replace(' %', '');

    const elSebrang = document.getElementById('input-persentase-penarikan');
    if (elSebrang.parentElement.classList.contains('is-invalid') && nominal_penarikan != '') {
        elSebrang.parentElement.classList.remove('is-invalid');
        elSebrang.parentElement.querySelector('label').removeAttribute('data-label-after');
        elSebrang.classList.remove('is-invalid');
    }
}

function putValueInKeydown(str, index, value) {
    return str.substr(0, index) + value + str.substr(index);
}

let objectAnggaran2 = {
    saldo_anggaran: 1500000
};

let objectPencairan = {};

const inputPersentase = document.getElementById('input-persentase-penarikan');
let oldValuePersentase,
    inputKeyState;
inputPersentase.addEventListener('keydown', function (e) {
    const prefix = ' %';
    let selfValue = e.target.value.replace(prefix, '');
    if (selfValue == 100 && +e.key >= 0 && e.target.selectionStart == e.target.selectionEnd || e.key == '.' && selfValue == 100 && e.target.selectionStart == e.target.selectionEnd && e.target.selectionStart == 3 || e.key == '.' && selfValue.indexOf('.') >= 0) {
        e.preventDefault();
        return false;
    }

    if (e.target.value.indexOf(prefix) >= 0) {
        if (e.code == "ArrowRight" && e.target.selectionStart >= e.target.value.length - prefix.length) {
            e.target.selectionStart = e.target.value.length - prefix.length;
            e.target.selectionEnd = e.target.value.length - prefix.length;
            inputKeyState = 'down';
            e.preventDefault();
            return false;
        }
    }

    if (inputKeyState == 'down') {
        e.preventDefault();
        return false;
    }

    if (e.code == "ArrowUp" || e.code == "ArrowDown" || e.code == "Home" || e.code == "End") {
        if (e.code == "ArrowUp" || e.code == "Home") {
            e.target.selectionStart = 0;
            e.target.selectionEnd = 0;
        } else if (e.code == "ArrowDown" || e.code == "End") {
            if (e.target.value.indexOf(prefix) >= 0) {
                e.target.selectionStart = e.target.value.length - prefix.length;
                e.target.selectionEnd = e.target.value.length - prefix.length;
            }
        }
        inputKeyState = 'down';
        e.preventDefault();
        return false;
    }

    if (+e.key >= 0 && e.target.selectionStart == e.target.selectionEnd) {
        if (selfValue.indexOf('.') != -1) {
            let indexDecimal = selfValue.indexOf('.');
            if (selfValue.length - indexDecimal >= 3 && e.target.selectionStart > indexDecimal) {
                e.preventDefault();
                return false;
            }
        }
        e.target.value = putValueInKeydown(selfValue, e.target.selectionStart, e.key);
        if (e.target.value >= 100) {
            e.target.value = 100;
        }
        if (e.target.value.indexOf(prefix) == -1) {
            e.target.value = percentMask(e, prefix, 'after');
        }
        if (e.target.value.indexOf(prefix) != -1) {
            e.target.selectionStart = e.target.value.length - prefix.length;
            e.target.selectionEnd = e.target.value.length - prefix.length;
        }
        e.preventDefault();
    }

    setTimeout(() => {
        if (e.target.value == prefix) {
            e.target.value = '';

            setNominalPenarikan(e);
            e.preventDefault();
            return false;
        }

        if (e.target.selectionStart == e.target.selectionEnd && +e.key > 0) {
            if (e.target.value.indexOf('%') < 0) {
                e.target.value = percentMask(e, prefix, 'after');
                e.target.selectionStart = 1;
                e.target.selectionEnd = 1;
            }
        }
        setNominalPenarikan(e);
    }, 0);
});

inputPersentase.addEventListener('keypress', function (e) {
    if (!(/[0-9\.]/.test(e.key))) {
        e.preventDefault();
        return false;
    }
});

inputPersentase.addEventListener('keyup', function (e) {
    inputKeyState = 'up';
});

inputPersentase.addEventListener('paste', function (e) {
    setTimeout(() => {
        const prefix = ' %';
        e.target.value = +escapeRegExp(e.target.value.trim(), '', /[^0-9.]/g);
        if (e.target.value >= 100) {
            e.target.value = 100;
        }
        if (e.target.value != '' && e.target.value.indexOf(prefix) == -1) {
            e.target.value = percentMask(e, prefix, 'after');
            e.target.selectionStart = e.target.value.length - prefix.length;
            e.target.selectionEnd = e.target.value.length - prefix.length;
        }
        setNominalPenarikan(e);
    }, 0);
});

inputPersentase.addEventListener('click', function (e) {
    const prefix = ' %';
    if (this.value.length && e.target.selectionStart > this.value.length - prefix.length) {
        e.target.selectionStart = this.value.length - prefix.length;
        e.target.selectionEnd = this.value.length - prefix.length;
    }
    if (e.target.value.indexOf(prefix) != -1 && e.target.selectionEnd == e.target.value.length) {
        e.target.selectionStart = e.target.value.length - prefix.length;
        e.target.selectionEnd = e.target.value.length - prefix.length;
    }
});

inputPersentase.addEventListener('focusout', function (e) {
    const prefix = ' %';
    if (+e.target.value.replace(prefix, '') < 0.01) {
        e.target.value = '';
        document.getElementById('input-nominal-penarikan').value = '';
        delete objectPencairan[e.target.name];
        delete objectPencairan[document.getElementById('input-nominal-penarikan').getAttribute('name')]
    }
});

function percentMask(event, mask = '', mask_position = 'after') {
    const value = event.target.value;
    if (mask_position == 'before') {
        return mask + value;
    } else {
        return value + mask;
    }
}

const inputPriceList = document.querySelectorAll('.modal .price');
let oldValuePrice = {};
inputPriceList.forEach(price => {
    price.addEventListener('focusout', function (e) {
        if (priceToNumber(e.target.value) == 0) {
            e.target.value = '';
        }
        if (e.target.name == 'nominal_penarikan') {
            setTimeout(() => {
                const persentase_penarikan = +inputPersentase.value.replace(' %', '');
                if (persentase_penarikan > 0) {
                    e.target.value = numberToPrice(Math.round(objectPelaksanaan.total_anggaran * (persentase_penarikan / 100).toFixed(4)));
                    objectPencairan[e.target.name] = priceToNumber(e.target.value);
                } else {
                    delete objectPencairan[e.target.name];
                    delete objectPencairan[document.getElementById('input-persentase-penarikan').getAttribute('name')];
                }
            }, 0)
        }
    });
    price.addEventListener('keypress', preventNonNumbersInInput);
    price.addEventListener('keydown', function (e) {
        let number = +e.key >= 0,
            cStart = e.target.selectionStart,
            cEnd = e.target.selectionEnd,
            cBlock = cStart != cEnd,
            value = e.target.value,
            cSparator = (e.target.value.match(/\./g) || []).length,
            prefix = '';

        if (e.code == "ArrowUp" || e.target.selectionStart == 0 && e.target.selectionStart != e.target.selectionEnd && e.code == "ArrowLeft" || e.code == "ArrowLeft" && e.target.selectionStart == prefix.length || e.code == "Home") {
            e.target.selectionStart = prefix.length;
            e.target.selectionEnd = prefix.length;
            e.preventDefault();
            return false;
        }

        if ((e.code == "Delete" || e.code == "Backspace") && !cBlock) {
            setTimeout(() => {
                let indexRemoveStart = cStart;

                if (e.code == "Backspace") {
                    if (value.substr(cStart - 1, 1) == '.') {
                        indexRemoveStart--;
                    }

                    e.target.value = numberToPrice(removeByIndex(e.target.value, indexRemoveStart - 1));
                    if ((e.target.value.match(/\./g) || []).length < cSparator) {
                        cStart--;
                    }
                    cStart--;
                    if (cStart < 0) {
                        cStart = 0;
                    }
                } else {
                    if (value.substr(cStart, 1) == '.') {
                        indexRemoveStart++;
                        cStart = indexRemoveStart;
                    }

                    e.target.value = numberToPrice(removeByIndex(e.target.value, indexRemoveStart));

                    if ((e.target.value.match(/\./g) || []).length < cSparator) {
                        cStart--;
                    }

                    if (cStart < 0) {
                        cStart = 0;
                    }
                }

                e.target.selectionStart = cStart;
                e.target.selectionEnd = cStart;

                if (e.target.name == 'nominal_penarikan') {
                    if (priceToNumber(e.target.value) > objectPelaksanaan.total_anggaran) {
                        e.target.value = numberToPrice(objectPelaksanaan.total_anggaran);
                        inputKeyState = 'down';
                    }
                    setPersentasePenarikan(e);
                }
            }, 0);
            e.preventDefault();
            return false;
        }

        if (!number && (e.code != "Delete" && e.code != "Backspace")) {
            return false;
        }

        if (cBlock) {
            const copyBlockSparator = (value.substr(cStart, cEnd).match(/\./g) || []).length;
            e.target.value = numberToPrice(priceToNumber(value.replaceAt(cStart, cEnd, e.key)));
            if (e.target.value == '0' && !number) {
                e.target.value = '';
            }

            if (copyBlockSparator > 0 && (e.target.value.match(/\./g) || []).length) {
                cStart++;
            }

            if (number) {
                cStart++;
            }

            e.target.selectionStart = cStart;
            e.target.selectionEnd = cStart;

            if (e.target.name == 'nominal_penarikan') {
                if (priceToNumber(e.target.value) > objectPelaksanaan.total_anggaran) {
                    e.target.value = numberToPrice(objectPelaksanaan.total_anggaran);
                }
                setPersentasePenarikan(e);
            }
            e.preventDefault();
            return false;
        }

        if (e.target.name == 'nominal_penarikan' && inputKeyState == 'down') {
            e.preventDefault();
            return false;
        }

        if (!cBlock && cStart != value.length) {
            e.target.value = numberToPrice(priceToNumber(value.slice(0, cStart) + e.key + value.slice(cEnd)));
            if ((e.target.value.match(/\./g) || []).length > cSparator) {
                cStart++;
                cEnd++;
            }
            e.target.selectionStart = cStart + 1;
            e.target.selectionEnd = cEnd + 1;

            if (e.target.name == 'nominal_penarikan') {
                if (priceToNumber(e.target.value) > objectPelaksanaan.total_anggaran) {
                    e.target.value = numberToPrice(objectPelaksanaan.total_anggaran);
                    inputKeyState = 'down';
                }
                setPersentasePenarikan(e);
            }
            e.preventDefault();
            return false;
        }

        if (!cBlock && cStart == value.length) {
            let maxnumeric = 1000000000000000;
            if (e.target.getAttribute('name') == 'jumlah_pelaksanaan') {
                maxnumeric = 1000000000;
            } else if (e.target.getAttribute('name') == 'nominal_pinbuk' && e.target.closest('.modal').id == 'modalFormPinbuk') {
                maxnumeric = objectPinbuk.max_pinbuk;
            }
            e.target.value = numberToPrice(priceToNumber(putValueInKeydown(value, cStart, e.key)) >= maxnumeric ? maxnumeric : priceToNumber(putValueInKeydown(value, cStart, e.key)));

            if (e.target.name == 'nominal_penarikan') {
                if (priceToNumber(e.target.value) > objectPelaksanaan.total_anggaran) {
                    e.target.value = numberToPrice(objectPelaksanaan.total_anggaran);
                    inputKeyState = 'down';
                }
                setPersentasePenarikan(e);
            }
            e.preventDefault();
            return false;
        }

        e.preventDefault();
        return false;
    });
    price.addEventListener('keyup', function (e) {
        if (e.target.name == 'nominal_penarikan') {
            inputKeyState = 'up';
        }
        //     let ceret = e.target.selectionStart,
        //         numberTPArray = numberToPrice(this.value, '', e),
        //         value = numberTPArray[0],
        //         sisa = numberTPArray[1],
        //         ribuan = numberTPArray[2],
        //         prefix = numberTPArray[3];

        //     this.value = value;

        //     console.log(this.value)

        //     if (e.code != undefined) {
        //         if (+e.key >= 0) {
        //             if (ribuan != null) {
        //                 if ((sisa == 1 && ceret + sisa > value.length - 3) || (sisa == 1 && ceret != prefix.length + 1 && ceret != value.length - prefix.length)) {
        //                     ceret++;
        //                 }
        //                 if (value != objectAnggaran.saldo_anggaran) {
        //                     e.target.selectionStart = ceret;
        //                     e.target.selectionEnd = ceret;
        //                 }
        //             }
        //         }
        //     }

        //     if (e.code == "Delete") {
        //         if (ribuan != null) {
        //             if (sisa == 0 && ceret != prefix.length && ceret != this.value.length && ceret != this.value.length - 1 || sisa == 0 && ceret >= this.value.length - 3 && ceret > prefix.length) {
        //                 ceret--;
        //             }
        //             if (oldValuePrice[price.getAttribute('name')] == this.value) {
        //                 if (sisa == 0) {
        //                     ceret += 2;
        //                 } else if (sisa == 2) {
        //                     ceret++;
        //                 } else {
        //                     ceret++;
        //                 }
        //                 this.value = numberToPrice(removeByIndex(this.value, ceret), prefix);
        //                 if (sisa == 1) {
        //                     ceret--;
        //                 }
        //             }
        //             e.target.selectionStart = ceret;
        //             e.target.selectionEnd = ceret;
        //         }
        //         if (e.target.name == 'nominal_penarikan') {
        //             setPersentasePenarikan(e);
        //         }
        //     }

        //     if (e.code == "Backspace") {
        //         if (ceret <= prefix.length && ribuan == null || ribuan != null && sisa == 0 && ceret == prefix.length) {
        //             e.target.selectionStart = ceret;
        //             e.target.selectionEnd = ceret;
        //         }
        //         if (ribuan != null && ceret > prefix.length) {
        //             if (sisa == 0 && oldValuePrice[price.getAttribute('name')] != this.value) {
        //                 ceret--;
        //             }
        //             if (oldValuePrice[price.getAttribute('name')] == this.value) {
        //                 this.value = numberToPrice(removeByIndex(this.value, --ceret), prefix);
        //                 if (sisa == 1 && ceret > prefix.length + 1) {
        //                     ceret--;
        //                 }
        //             }
        //             e.target.selectionStart = ceret;
        //             e.target.selectionEnd = ceret;
        //         }
        //         if (e.target.name == 'nominal_penarikan') {
        //             setPersentasePenarikan(e);
        //         }
        //     }
    });
    price.addEventListener('paste', function (e) {
        setTimeout(() => {
            this.value = numberToPrice(escapeRegExp(escapeRegExp(this.value.trim(), '', /[^a-zA-Z0-9\s\/.]/g), ' ', /\s+/g), '');
            if (e.target.name == 'nominal_penarikan') {
                setPersentasePenarikan(e);
            }
        }, 0);
    });
});

const inputJumlahPelaksanaan = document.getElementById('input-jumlah-pelaksanaan');
let objectPelaksanaan = {};
inputJumlahPelaksanaan.addEventListener('focusout', function () {
    if (oldValuePrice[this.name] == this.value) {
        return false;
    }

    oldValuePrice[this.name] = this.value;

    if (this.value.length) {
        objectPelaksanaan[this.name] = this.value;
    } else {
        delete objectPelaksanaan[this.name];
    }
});

const inputNamaKebutuhan = document.getElementById('input-nama-kebutuhan');

inputNamaKebutuhan.addEventListener('change', function () {
    if (this.value.length) {
        objectKebutuhan.nama = this.value;
        let data = {
            token: document.querySelector('body').getAttribute('data-token'),
            fields: {
                nama: objectKebutuhan.nama
            }
        };
        fetchData('/admin/fetch/get/kebutuhan/cek', data, this, 'cek-nama');

        this.closest('.modal').querySelector('[type="submit"]').classList.remove('disabled');
    } else {
        delete objectKebutuhan.nama;
    }
});

inputNamaKebutuhan.addEventListener('focus', function () {
    this.closest('.modal').querySelector('[type="submit"]').classList.add('disabled');
});

inputNamaKebutuhan.addEventListener('focusout', function () {
    this.closest('.modal').querySelector('[type="submit"]').classList.remove('disabled');
});

const tambahItemRab = document.getElementById('tambah-item-rab');

if (tambahItemRab != null) {
    tambahItemRab.addEventListener('click', function () {
        let mTarget = this.getAttribute('data-target');
        mTarget = document.querySelector(mTarget);
        mTarget.querySelector('#formJudul').setAttribute('data-mode', 'create');
        mTarget.querySelector('#formJudul').innerText = 'Tambah';
        if (mTarget.querySelector('.btn[type="reset"]') != null) {
            mTarget.querySelector('.btn[type="reset"]').innerText = "Kosongkan";
            mTarget.querySelector('.btn[type="reset"]').setAttribute('type', 'clear');
        }
        // console.log(mTarget.querySelector('#formJudul'));
    });
}

let resultRab = {};

document.addEventListener('click', function (e) {
    if (e.target && (e.target.parentElement.id == 'tambah-item-rab' || e.target.getAttribute('id') == 'tambah-item-rab')) {
        // e.target.parentElement.addEventListener('click', function () {
        let mTarget;
        if (e.target.parentElement.id == 'tambah-item-rab') {
            mTarget = e.target.parentElement.getAttribute('data-target');
        } else {
            mTarget = e.target.getAttribute('data-target');
        }
        mTarget = document.querySelector(mTarget);
        mTarget.querySelector('#formJudul').setAttribute('data-mode', 'create');
        mTarget.querySelector('#formJudul').innerText = 'Tambah';
        if (mTarget.querySelector('.btn[type="reset"]') != null) {
            mTarget.querySelector('.btn[type="reset"]').innerText = "Kosongkan";
            mTarget.querySelector('.btn[type="reset"]').setAttribute('type', 'clear');
        }
        console.log(mTarget.querySelector('#formJudul'));
        mTarget.setAttribute('data-related-modal', e.target.closest('.modal').getAttribute('id'));
        // });
    } else if (e.target && e.target.classList.contains('update')) {
        
        let mTarget = e.target.getAttribute('data-target');
        mTarget = document.querySelector(mTarget);
        mTarget.setAttribute('data-related-modal', e.target.closest('.modal').getAttribute('id'));

        const tr = e.target.closest('tr');
        if (tr == null) {
            console.log('TR data-id-rab is null');
            return false;
        }
        const idRab = tr.getAttribute('data-id-rab');
        // fetch
        let url = '/admin/fetch/get/rab';
        data = {
            id_rab: idRab,
            token: document.querySelector('body').getAttribute('data-token')
        };
        fetchData(url, data, e.currentTarget, 'get-rab');
        
    } else if (e.target && e.target.classList.contains('delete')) {
        
        const tr = e.target.closest('tr');
        if (tr == null) {
            console.log('TR data-id-rab is null');
            return false;
        }
        const idRab = tr.getAttribute('data-id-rab');
        // fetch
        let url = '/admin/fetch/get/rab/for-delete';

        let data = {
            id_rab: idRab,
            token: document.querySelector('body').getAttribute('data-token')
        }
        
        const modalKDeleteRab = document.getElementById('modalKonfirmasiHapusRab');

        fetchData(url, data, modalKDeleteRab, 'get-rab-for-delete');
        
    } else if (e.target.closest('td') && e.target.getAttribute('type') == 'checkbox' && objectBantuan.jumlah_target == null) {
        console.log(objectPenganggaran);
        const idRab = e.target.closest('tr').getAttribute('data-id-rab'),
            dataRab = objectPenganggaran.result.find(key => key.id_rab == idRab);

        if (e.target.checked == true) {
            if (dataRab.sub_total + objectPenganggaran.total_penganggaran > objectAnggaran.saldo_anggaran) {
                let currentDate = new Date(),
                    timestamp = currentDate.getTime(); 

                let invalid = {
                    error: true,
                    data_toast: 'invalid-anggaran-feedback',
                    feedback: {
                        message: 'Saldo anggaran tidak mencukupi <b>(max. penganggaran ' + numberToPrice(objectAnggaran.saldo_anggaran) + ')</b>'
                    }
                };
    
                invalid.id = invalid.data_toast +'-'+ timestamp;
                
                createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
                $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                    'delay': 10000
                }).toast('show');

                e.target.checked = false;
                e.target.removeAttribute('checked');
                return false;
            }
            e.target.checked = true;
            e.target.setAttribute('checked', 'true');
            objectPenganggaran.total_penganggaran += dataRab.sub_total;
            objectPenganggaran.selected.push(dataRab.id_rab);
        } else {
            e.target.checked = false;
            e.target.removeAttribute('checked');
            objectPenganggaran.total_penganggaran -= dataRab.sub_total;

            let index = objectPenganggaran.selected.indexOf(dataRab.id_rab);
            objectPenganggaran.selected.splice(index, 1);
        }

        if (objectPenganggaran.total_penganggaran <= objectAnggaran.saldo_anggaran) {
            if (document.querySelectorAll('[data-toast="invalid-anggaran-feedback"').length >= 1) {
                $('[data-toast="invalid-anggaran-feedback"').toast('hide');
            }
        }
        document.getElementById('penggunaan-anggaran').innerText = numberToPrice(objectPenganggaran.total_penganggaran);
    } else if (e.target.closest('td') && e.target.getAttribute('type') == 'checkbox' && objectBantuan.jumlah_target != null) {
        e.preventDefault();
    }
});

let clickFunction = function(e) {
    if (e.target.getAttribute('type') == 'button' && e.target.getAttribute('id') == 'rekalkulasi-penarikan' || e.target.parentElement.getAttribute('id') == 'rekalkulasi-penarikan') {
        let data = {
            id_pencairan: objectPencairan.id_pencairan,
            id_pelaksanaan: objectPencairan.id_pelaksanaan,
            persentase_penarikan: objectPencairan.persentase_penarikan,
            token: document.querySelector('body').getAttribute('data-token')
        };
        fetchData('/admin/fetch/read/rekalkulasi-penarikan', data, e.target.closest('.modal'), 'rekalkulasi-penarikan');
    }
}

document.addEventListener('click', debounceIgnoreLast(clickFunction, 1000));

// Clear
const clearList = document.querySelectorAll('[type="clear"]');

clearList.forEach(btn => {
    btn.addEventListener('click', function (e) {
        let type = this.getAttribute('type');
        if (type == 'clear') {
            this.closest('.modal').querySelectorAll('input').forEach(input => {
                input.value = '';
                oldValuePrice[input.name] = input.value;
                if (input.hasAttribute('maxlength')) {
                    input.parentElement.querySelector('.current-length').innerText = 0;
                }
                if (input.parentElement.classList.contains('inputGroup') && input.type == 'file' && input.nextElementSibling.querySelector('.result') != null) {
                    const ruleDesc = '<span class="rule">Dimensi 1280 x 768 pixel (16:9)</span>';
                    input.nextElementSibling.querySelector('.result').remove();
                    input.nextElementSibling.querySelector('.d-flex').insertAdjacentHTML('beforeend', ruleDesc);
                    input.parentElement.removeAttribute('data-file-passed');
                    delete cropedFile[input.name];
                    delete objectPinbuk[input.name];
                    input.setAttribute('title', 'Foto hasil pinbuk belum disertakan');
                    cropedFile = {};
                }
            });
            this.closest('.modal').querySelectorAll('textarea').forEach(textarea => {
                textarea.value = '';
                oldValuePrice[textarea.name] = textarea.value;
                if (input.hasAttribute('maxlength')) {
                    input.parentElement.querySelector('.current-length').innerText = 0;
                }
            });
            this.closest('.modal').querySelectorAll('select').forEach(select => {
                select.value = '0';
                oldValuePrice.id_kebutuhan = select.value;
                oldValuePrice.nama_kebutuhan = '';

                // for select2
                
                if (this.closest('.modal').getAttribute('id') == 'modalFormPinbuk' && select.getAttribute('id') == 'id-ca-pengirim') {
                    // do nothing
                } else {
                    $('#' + select.getAttribute('id')).val(0).trigger('change');
                }
            });
            const modalId = this.closest('.modal').getAttribute('id');

            if (modalId == 'modalTambahKebutuhan') {
                objectKebutuhan = {};
            }

            if (modalId == 'modalFormRab') {
                objectRab = {};
            }

            if (modalId == 'modalFormPinbuk') {
                delete objectPinbuk.id_ca_penerima;
                delete objectPinbuk.nominal_pinbuk;
                delete objectPinbuk.keterangan;
            }
        }

        if (type == 'reset') {
            e.target.closest('.modal').querySelector('#input-harga-satuan').value = resultRab.harga_satuan;
            e.target.closest('.modal').querySelector('#input-jumlah').value = resultRab.jumlah;
            e.target.closest('.modal').querySelector('#input-keterangan-rab').value = resultRab.keterangan;

            for (const property in objectRab) {
                oldValuePrice[property] = resultRab.property;
                console.log(property);
            }

            if (e.target.closest('.modal').querySelector('#input-keterangan-rab').hasAttribute('maxlength')) {
                e.target.closest('.modal').querySelector('#input-keterangan-rab').parentElement.querySelector('.current-length').innerText = resultRab.keterangan.length;
            }

            // for select2 ok
            const optionKebutuhan = selectLabelKebutuhan([resultRab.kebutuhan]);
            resetDataSelect2($('#id-kebutuhan'), optionKebutuhan);

            // $('#id-kebutuhan').val([resultRab.kebutuhan.id_kebutuhan]).trigger('change');
            console.log(objectRab);
            objectRab = {};
        }

        data = {};

        e.target.closest('.modal').querySelectorAll('[name]').forEach(name => {
            if (!name.classList.contains('is-invalid')) {
                return;
            }
            let fromGroup = name.parentElement;
            fromGroup.querySelector('label').removeAttribute('data-label-after');
            fromGroup.classList.remove('is-invalid');
            name.classList.remove('is-invalid');
        });
    });
});

// function statusRencana(status) {
//     switch (status.toUpperCase()) {
//         case 'TD':
//             return ['Tidak Disetujui', 'bg-gradient-danger'];
//             break;

//         case 'SD':
//             return ['Sudah Disetujui', 'bg-gradient-success'];
//             break;

//         case 'BP':
//             return ['Butuh Perbaikan', 'bg-gradient-warning'];
//             break;

//         default:
//             return ['Belum Disetujui', 'bg-gradient-default']
//             break;
//     }
// }

// for Pinbuk
const inputNominalPinbuk = document.getElementById('input-nominal-pinbuk');
inputNominalPinbuk.addEventListener('change', function () {
    if (this.value.length) {
        if (priceToNumber(this.value) > objectPinbuk.max_pinbuk) {
            this.value = numberToPrice(objectPinbuk.max_pinbuk);
        }
        objectPinbuk[this.name] = priceToNumber(this.value);
    } else {
        delete objectPinbuk[this.name];
    }
});

const inputKeteranganPinbuk = document.getElementById('input-keterangan-pinbuk');
inputKeteranganPinbuk.addEventListener('change', function () {
    if (this.value.length) {
        objectPinbuk.keterangan = this.value;
    } else {
        delete objectPinbuk.keterangan;
    }
});

// Submit
const submitList = document.querySelectorAll('.modal [type="submit"]');
let objectAnggaran = {},
    objectPenganggaran = {},
    objectBantuan = {};

submitList.forEach(submit => {
    submit.addEventListener('click', function (e) {
        if (e.target.getAttribute('type') == 'button') {
            if (e.target.getAttribute('id') == 'buat-pelaksanaan') {
                const tabRencana = e.target.closest('.modal').querySelector('#tab-rencana');

                if (!tabRencana.querySelectorAll('#rab table>tbody>tr[data-id-rab]').length) {
                    let currentDate = new Date(),
                        timestamp = currentDate.getTime();

                    let invalid = {
                        error: true,
                        data_toast: 'invalid-feedback',
                        feedback: {
                            message: 'Daftar Rancangan Anggaran Belanja masih kosong, mohon <b>Tambah Item</b> RAB terlebih dahulu'
                        }
                    };
        
                    invalid.id = invalid.data_toast +'-'+ timestamp;
                    
                    createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
                    $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                        'delay': 10000
                    }).toast('show');
                    return false;
                }

                if (objectAnggaran.total_rab < 10000) {
                    let currentDate = new Date(),
                        timestamp = currentDate.getTime();

                    let invalid = {
                        error: true,
                        data_toast: 'invalid-feedback',
                        feedback: {
                            message: 'Total minimum Rancangan Anggaran Belanja adalah 10.000'
                        }
                    };
        
                    invalid.id = invalid.data_toast +'-'+ timestamp;
                    
                    createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
                    $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                        'delay': 10000
                    }).toast('show');
                    return false;
                }

                tabRencana.classList.remove('active');
                tabRencana.classList.remove('show');
                e.target.closest('.modal').querySelector('#tab-pelaksanaan').classList.add('active');
                e.target.closest('.modal').querySelector('#tab-pelaksanaan').classList.add('show');

                e.target.classList.remove('btn-outline-orange');
                e.target.classList.add('btn-outline-primary');
                e.target.innerText = 'Lanjut Pencairan';
                e.target.setAttribute('type', 'submit');
                e.target.setAttribute('id', 'buat-pencairan');

                // fetch data saldo anggaran bantuan rencana ini
                let data = {
                    id_rencana: objectRencana.id_rencana,
                    token: document.querySelector('body').getAttribute('data-token')
                };

                fetchData('/admin/fetch/get/rencana/pelaksanaan', data, e.target.closest('.modal'), 'data-pelaksanaan');
            }
            
            e.preventDefault();
            return false;
        }

        const modalId = e.target.closest('.modal').getAttribute('id');

        let c_error = 0,
            nameList;

        if (modalId == 'modalBuatRencana') {
            if (e.target.getAttribute('type') == 'submit' && e.target.id == 'pencairan') {
                data.fields = {
                    id_pencairan: objectPencairan.id_pencairan,
                    id_pelaksanaan: objectPencairan.id_pelaksanaan,
                    persentase_penarikan: objectPencairan.persentase_penarikan,
                    token: document.querySelector('body').getAttribute('data-token')
                };
                data.mode = 'create';
                data.table = 'penarikan';
            } else {
                nameList = e.target.closest('.modal').querySelectorAll('.tab-pane.active.show [name]');
            }
        } else {
            nameList = e.target.closest('.modal').querySelectorAll('[name]');
        }

        if (nameList != undefined) {
            nameList.forEach(name => {
                if (name.getAttribute('unrequired')) {
                    return;
                }
                let error = false,
                    errorText = 'wajib diisi';
                if (name.tagName.toLowerCase() == 'select') {
                    if (name.id == 'id-ca-pengirim') {
                        return;
                    }
                    if (name.value == '0' || !name.value.length) {
                        error = true;
                        errorText = 'wajib dipilih';
                    }
                }
    
                if (name.tagName.toLowerCase() == 'input') {
                    if (!name.value.length && (name.type != 'file' && name.type != 'checkbox')) {
                        error = true;
                    } else {
                        if (name.type == 'file') {
                            if (cropedFile[name.name] == undefined) {
                                error = true;
                            }
                        }
                        if (name.classList.contains('price')) {
                            if (priceToNumber(name.value) < 1) {
                                error = true;
                                errorText = 'tidak valid';
                            }
                        }
    
                        if (name.name == 'nominal_penarikan' || name.name == 'nominal_pinbuk') {
                            if (priceToNumber(name.value) < 10000) {
                                error = true;
                                errorText = 'terlalu sedikit';
                            }
                        } else if (name.name == 'persentase_penarikan') {
                            if (+name.value.replace(' %', '') < 10) {
                                error = true;
                                errorText = 'minimal 10 %';
                            }
                        }
    
                        if (name.getAttribute('id') == 'input-nama-kebutuhan' && name.parentElement.classList.contains('is-invalid')) {
                            return false;
                        }
                    }
                }
    
                if (name.tagName.toLowerCase() == 'textarea') {
                    if (!name.value.length) {
                        error = true;
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

        if (c_error > 0) {
            return false;
        }

        let invalidModal = false,
            namaKebutuhan = undefined;

        switch (modalId) {
            case 'modalTambahKebutuhan':

                data.mode = 'create';
                if (Object.keys(objectKebutuhan).length) {
                    data.fields = objectKebutuhan;
                    data.table = 'kebutuhan';
                } else {
                    delete data.fields;
                }
                
                break;

            case 'modalFormRab':

                const mode = document.getElementById('formJudul').getAttribute('data-mode');
                if (mode !== 'create' && mode !== 'update') {
                    console.log('Unrecognize mode on #' + modalId);
                    return false;
                }

                data.mode = mode;

                if (Object.keys(objectRab).length) {
                    // console.log(objectRab);
                    namaKebutuhan = objectRab.nama_kebutuhan;
                    delete objectRab.nama_kebutuhan;
                    objectRab.id_rencana = objectRencana.id_rencana;
                    data.fields = objectRab;
                    data.table = 'rencana_anggaran_belanja';
                    if (mode == 'update') {
                        data.id_rencana = objectRab.id_rencana;
                        delete data.fields.id_rencana;
                        const objectNewRab = diff(resultRab, data.fields);
                        // console.log(objectNewRab);
                        if (Object.keys(objectNewRab).length) {
                            data.id_rab = resultRab.id_rab;
                            data.fields = objectNewRab;
                        } else {
                            data = {};
                        }
                        
                        if (namaKebutuhan == undefined) {
                            namaKebutuhan = resultRab.kebutuhan.nama;
                        }
                    }
                } else {
                    const currentDate = new Date();
                    const timestamp = currentDate.getTime();

                    let invalid = {
                        error: true,
                        data_toast: 'invalid-feedback',
                        feedback: {
                            message: 'Tidak ada peruahan data RAB'
                        }
                    };
        
                    invalid.id = invalid.data_toast +'-'+ timestamp;
                    
                    createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
                    $('[data-toast="'+invalid.data_toast+'"]').toast('show');
                    data = {};
                }
                break;

            case 'modalBuatRencana':

                const tabActive = e.target.closest('.modal').querySelector('.tab-pane.active.show').getAttribute('id');
                if (tabActive == 'tab-rencana') {
                    if (Object.keys(objectRencana).length) {
                        data.fields = objectRencana;
                        data.table = 'rencana';
                    } else {
                        delete data.fields;
                    }
                } else if (tabActive == 'tab-pencairan') {
                    if (Object.keys(objectPencairan).length) {
                        objectPencairan.total = objectPelaksanaan.total_anggaran;
                        objectPencairan.keterangan = document.querySelector('#keterangan-pencairan').value;
                        data.fields = objectPencairan;
                        data.table = 'pencairan';
                    } else {
                        delete data.fields;
                    }
                } else if (tabActive == 'tab-pelaksanaan') {
                    // console.log(objectPenganggaran);
                    // console.log(objectAnggaran);
                    // console.log(objectPelaksanaan);
                    if (Object.keys(objectPelaksanaan).length && e.target.closest('.modal').querySelector('#' + tabActive + ' textarea').value.length) {
                        objectPelaksanaan.deskripsi = e.target.closest('.modal').querySelector('#' + tabActive + ' textarea').value;
                        objectPelaksanaan.total_anggaran = objectPenganggaran.total_penganggaran;
                        if (objectPenganggaran.selected.length) {
                            objectPenganggaran.selected.sort(function (a, b) { return a - b });
                            data.rab = objectPenganggaran.selected;
                        }
                        objectPelaksanaan.id_rencana = objectRencana.id_rencana;
                        data.fields = objectPelaksanaan;
                        data.table = 'pelaksanaan';
                    } else {
                        delete data.fields;
                        if (objectPenganggaran.selected.length) {
                            delete data.rab;
                        }
                    }
                }

                data.mode = 'create';
                break;

            case 'modalKonfirmasiHapusRab':
                data.mode = 'delete';
                data.id_rab = resultRab.id_rab;
                data.id_rencana = objectRencana.id_rencana;
                data.table = 'rencana_anggaran_belanja';
                break;

            case 'modalKonfirmasiAksi':
                data.mode = 'update';
                if (Object.keys(objectRencana).length) {
                    data.fields = objectRencana;
                    data.id_rencana = objectRencana.id_rencana;
                    delete objectRencana.id_rencana;
                    data.table = 'rencana';
                } else {
                    delete data.fields;
                }
                break;

            case 'modalKeteranganPerbaikanRAB':
                data.mode = 'update';
                if (Object.keys(objectRencana).length && e.target.closest('.modal').querySelector('textarea').value.length) {
                    data.fields = {
                        status: 'BP',
                        pesan: e.target.closest('.modal').querySelector('textarea').value
                    };
                    data.id_rencana = objectRencana.id_rencana;
                    delete objectRencana.id_rencana;
                    data.table = 'rencana';
                } else {
                    delete data.fields;
                }
                break;
            
            case 'modalFormPinbuk':
                data.mode = 'create';
                if (Object.keys(objectPinbuk).length) {
                    document.querySelectorAll('#' + modalId+ ' input[text]').forEach(input => {
                        objectPinbuk[input.name] = input.value;
                    });
                    objectPinbuk.id_pelaksanaan = objectPencairan.id_pelaksanaan;
                    objectPinbuk.id_bantuan = objectRencana.id_bantuan;
                    data.fields = objectPinbuk;
                    data.table = 'pinbuk';
                } else {
                    delete data.fields;
                }
                break;

            default:
                invalidModal = true;
                break;
        }

        if (invalidModal) {
            console.log('Unrecognize modal ID');
            const currentDate = new Date();
            const timestamp = currentDate.getTime();

            let invalid = {
                error: true,
                data_toast: 'invalid-feedback',
                feedback: {
                    message: 'Unrecognize modal ID'
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"').toast('show');
            return false;
        }

        const url = '/admin/fetch/' + data.mode + '/' + data.table;
        let dataMode = data.mode,
            dataTable = data.table,
            message;
        delete data.mode;
        delete data.table;

        if (!Object.keys(data).length) {
            console.log('Failed to fetch, no data object!')
            return false;
        }

        data.token = document.querySelector('body').getAttribute('data-token');
        // fetch Here
        console.log(data);
        console.log(url);

        let inData = data;

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
            // if fetch success 
            if (response.feedback.message != null) {
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
            }
            let data = response.feedback.data;
            if (!response.error) {
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': true
                }).toast('show');
                if (modalId != 'modalBuatRencana') {
                    message = 'Berhasil ' + dataMode + ' data ' + dataTable.replaceAll('_', ' ');
    
                    nameList.forEach(name => {
                        if (name.tagName.toLowerCase() == 'select') {
                            name.value = '0';
    
                            // for select2
                            document.querySelector('#id-kebutuhan').insertAdjacentHTML('afterbegin','<option value="0" selected disabled hidden>Pilih salah satu</option>');
                            $('#' + name.getAttribute('id')).select2('val', '0');
                        }
    
                        if (name.tagName.toLowerCase() == 'input') {
                            name.value = '';
                            if (name.hasAttribute('maxlength')) {
                                name.parentElement.querySelector('.current-length').innerText = 0;
                            }
                        }
    
                        if (name.tagName.toLowerCase() == 'textarea') {
                            name.value = '';
                            if (name.hasAttribute('maxlength')) {
                                name.parentElement.querySelector('.current-length').innerText = 0;
                            }
                        }
                    });
    
                    if (modalId == 'modalFormRab') {
                        let budget_warning = false,
                            relatedModal;

                        if (document.querySelector('#'+modalId).getAttribute('data-related-modal') != null) {
                            relatedModal = document.querySelector('#'+modalId).getAttribute('data-related-modal');
                        } else {
                            relatedModal = 'rab';
                        }

                        // create RAB
                        if (dataMode == 'create') {
                            // return
                            const dataRab = {
                                id_rab: data.id_rab,
                                total_rab: +data.total_rab
                            };

                            objectAnggaran.saldo_anggaran = +data.max_anggaran;
    
                            objectAnggaran.total_rab = dataRab.total_rab;
    
                            if (dataRab.total_rab > objectAnggaran.saldo_anggaran) {
                                budget_warning = true;
                            }

                            if (document.querySelector('#'+ relatedModal +' table>tbody>tr:not([data-id-rab])') != undefined) {
                                document.querySelector('#'+ relatedModal +' table>tbody>tr:not([data-id-rab])').remove();
                            }

                            const trRab = '<tr data-id-rab="' + dataRab.id_rab + '" class="highlight not-budgeted"><td><span>' + namaKebutuhan + '</span></td><td>' + objectRab.keterangan + '</td><td class="text-right">' + objectRab.harga_satuan + '</td><td class="text-right">' + objectRab.jumlah + '</td><td class="text-right">' + numberToPrice(priceToNumber(objectRab.harga_satuan) * priceToNumber(objectRab.jumlah)) + '</td><td class="px-0"><a href="#" class="btn btn-outline-danger btn-sm font-weight-bolder delete">Hapus</a></td><td><a href="#" class="btn btn-outline-orange btn-sm font-weight-bolder update" data-target="#modalFormRab">Ubah</a></td></tr>';
                            document.querySelector('#'+ relatedModal +' table>tbody').insertAdjacentHTML('afterbegin', trRab);
                            
                            if (relatedModal == 'modalBuatRencana') {
                                document.querySelector('#'+ relatedModal +' .bg-lighter .update-total-rab').innerText = 'Rp. ' + numberToPrice(dataRab.total_rab);
                            } else {
                                document.querySelector('.modal .bg-lighter .update-total-rab').innerText = 'Rp. ' + numberToPrice(data.total_rab);
                                document.querySelector('.modal .bg-lighter #waktu-pembaharuan-rencana-update').innerText = data.modified_at;
                                document.querySelector('.modal .bg-lighter #status-rencana-update').innerText = data.status.text;
                                let prefix = "text-",
                                    classes = document.querySelector('.modal .bg-lighter #status-rencana-update').className.split(" ").filter(c => !c.startsWith(prefix));
                                document.querySelector('.modal .bg-lighter #status-rencana-update').className = classes.join(" ").trim();
                                document.querySelector('.modal .bg-lighter #status-rencana-update').classList.add(data.status.class);
                                document.querySelector('#total-teranggarkan-rencana-update').innerText = numberToPrice(data.total_teranggarkan);
                                document.querySelector('#belum-teranggarkan-rencana-update').innerText = numberToPrice(data.total_rab - data.total_teranggarkan);
                            }
                            
                            setTimeout(() => {
                                if (document.querySelector('#'+ relatedModal +' table>tbody>tr[data-id-rab="' + dataRab.id_rab + '"]') != null) {
                                    document.querySelector('#'+ relatedModal +' table>tbody>tr[data-id-rab="' + dataRab.id_rab + '"]').classList.remove('highlight');
                                }
                            }, 3000);
                        }

                        // update RAB
                        if (dataMode == 'update') {
                            // return
                            const dataRab = {
                                id_rab: data.id_rab,
                                total_rab: +data.total_rab
                            };
                            
                            objectAnggaran.saldo_anggaran = +data.max_anggaran;

                            objectAnggaran.total_rab = dataRab.total_rab;

                            if (dataRab.total_rab > objectAnggaran.saldo_anggaran) {
                                budget_warning = true;
                            }

                            const trRabUpdateEl = document.querySelector('table>tbody>tr[data-id-rab="' + data.id_rab + '"]');

                            Object.keys(data.fields).forEach(key => {
                                if (key == 'id_kebutuhan') {
                                    trRabUpdateEl.children[0].innerHTML = '<span>' +namaKebutuhan+ '</span>';
                                } else if (key == 'keterangan') {
                                    trRabUpdateEl.children[1].innerText = data.fields.keterangan;
                                } else if (key == 'harga_satuan') {
                                    trRabUpdateEl.children[2].innerText = numberToPrice(data.fields.harga_satuan);
                                } else if (key == 'jumlah') {
                                    trRabUpdateEl.children[3].innerText = numberToPrice(data.fields.jumlah);
                                }
                            });

                            if (data.fields.jumlah == null && data.fields.harga_satuan != null) {
                                trRabUpdateEl.children[4].innerText = numberToPrice(priceToNumber(data.fields.harga_satuan) * priceToNumber(resultRab.jumlah));
                            } 
                            
                            if (data.fields.harga_satuan == null && data.fields.jumlah != null) {
                                trRabUpdateEl.children[4].innerText = numberToPrice(priceToNumber(resultRab.harga_satuan) * priceToNumber(data.fields.jumlah));
                            }
                            
                            if (data.fields.jumlah != null && data.fields.harga_satuan != null) {
                                trRabUpdateEl.children[4].innerText = numberToPrice(priceToNumber(data.fields.harga_satuan) * priceToNumber(data.fields.jumlah));
                            }

                            document.querySelector('.modal .bg-lighter .update-total-rab').innerText = 'Rp. ' + numberToPrice(data.total_rab);
                            document.querySelector('.modal .bg-lighter .max-anggaran').innerText = numberToPrice(data.max_anggaran);
                            document.querySelector('.modal .bg-lighter #waktu-pembaharuan-rencana-update').innerText = data.modified_at;
                            document.querySelector('.modal .bg-lighter #status-rencana-update').innerText = data.status.text;
                            let prefix = "text-",
                                classes = document.querySelector('.modal .bg-lighter #status-rencana-update').className.split(" ").filter(c => !c.startsWith(prefix));
                            document.querySelector('.modal .bg-lighter #status-rencana-update').className = classes.join(" ").trim();
                            document.querySelector('.modal .bg-lighter #status-rencana-update').classList.add(data.status.class);
                            document.querySelector('#total-teranggarkan-rencana-update').innerText = numberToPrice(data.total_teranggarkan);
                            document.querySelector('#belum-teranggarkan-rencana-update').innerText = numberToPrice(data.total_rab - priceToNumber(data.total_teranggarkan));
                            trRabUpdateEl.classList.add('highlight');
                            setTimeout(() => {
                                trRabUpdateEl.classList.remove('highlight');
                            }, 3000);
                        }

                        if (budget_warning == true) {
                            if (document.querySelector('#budget-warning') == null) {
                                console.log(relatedModal);
                                const budget_warning_html_rab = '<div class="col-12" id="budget-warning"><div class="box rounded bg-gradient-danger text-white"><div class="px-2"><h4 class="mb-0 text-white">Saldo anggaran program <span class="font-weight-bolder">Tidak Cukup !!</span></h4><div class="text-sm">Resiko anda hanya dapat mencairkan program sejumlah <b>' + numberToPrice(objectAnggaran.saldo_anggaran) + '</b> untuk anggaran tertentu pada tahap ini, atau gunakan <span class="font-weight-bolder" id="btn-dana-talang">data talang</span> <a href="#" class="font-weight-light text-white small">(Syarat dan ketentuan berlaku *)</a></div></div></div></div>';
                                if (relatedModal == 'modalBuatRencana') {
                                    document.querySelector('#stepper').insertAdjacentHTML('afterend', budget_warning_html_rab);
                                    document.querySelector('#budget-warning').classList.add('px-0');
                                } else {
                                    document.querySelector('#box-bg-light-info').insertAdjacentHTML('beforeend', budget_warning_html_rab);
                                }
                            }
                        } else {
                            if (document.querySelector('#'+relatedModal+' #budget-warning') != null) {
                                document.querySelector('#'+relatedModal+' #budget-warning').remove();
                            }
                        }

                        for (const property in objectRab) {
                            delete oldValuePrice[property];
                        }

                        objectRencana.dml = true;
                    }

                    if (modalId == 'modalKonfirmasiHapusRab') {
                        const trRabDeleteElList = document.querySelectorAll('table.list-rab>tbody>tr[data-id-rab="' + data.id_rab + '"]');
                        trRabDeleteElList.forEach(el => {
                            el.remove();
                        });

                        setTimeout(() => {
                            document.querySelector('#list-rencana').closest('.card').querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
                            objectRencana.dml = true;
                        }, 0);

                        objectAnggaran.total_rab = data.total_rab;
                        
                        document.querySelector('.modal .bg-lighter .update-total-rab').innerText = 'Rp. ' + numberToPrice(data.total_rab);
                        document.querySelector('.modal .bg-lighter .max-anggaran').innerText = numberToPrice(data.max_anggaran);
                        document.querySelector('.modal .bg-lighter #waktu-pembaharuan-rencana-update').innerText = data.modified_at;
                        document.querySelector('.modal .bg-lighter #status-rencana-update').innerText = data.status.text;
                        let prefix = "text-",
                            classes = document.querySelector('.modal .bg-lighter #status-rencana-update').className.split(" ").filter(c => !c.startsWith(prefix));
                        document.querySelector('.modal .bg-lighter #status-rencana-update').className = classes.join(" ").trim();
                        document.querySelector('.modal .bg-lighter #status-rencana-update').classList.add(data.status.class);
                        document.querySelector('#total-teranggarkan-rencana-update').innerText = data.total_teranggarkan;
                        document.querySelector('#belum-teranggarkan-rencana-update').innerText = numberToPrice(data.total_rab - priceToNumber(data.total_teranggarkan));

                        if (+data.total_rab <= +data.max_anggaran) {
                            if (document.querySelector('#budget-warning') != null) {
                                document.querySelector('#budget-warning').remove();
                            }
                        } else {
                            if (document.querySelector('#budget-warning') != null) {
                                document.querySelector('#budget-warning .text-sm b').innerText = numberToPrice(data.max_anggaran);
                            } else {
                                const budget_warning_html_rab_del = '<div class="col-12 px-0" id="budget-warning"><div class="box rounded bg-gradient-danger text-white"><div class="px-2"><h4 class="mb-0 text-white">Saldo anggaran program <span class="font-weight-bolder">Tidak Cukup !!</span></h4><div class="text-sm">Resiko anda hanya dapat mencairkan program sejumlah <b>' + numberToPrice(objectAnggaran.saldo_anggaran) + '</b> untuk anggaran tertentu pada tahap ini, atau gunakan <span class="font-weight-bolder" id="btn-dana-talang">data talang</span> <a href="#" class="font-weight-light text-white small">(Syarat dan ketentuan berlaku *)</a></div></div></div></div>';
                                document.querySelector('#stepper').insertAdjacentHTML('afterend', budget_warning_html_rab_del);
                            }
                        }
                    }

                    if (modalId == 'modalFormPinbuk') {
                        if (dataMode == 'create') {
                           console.log(response); 
                        }
                    }

                    $('#' + modalId).modal('hide');
                } else {
                    if (e.target.getAttribute('id') == 'buat-rab') {
                        e.target.closest('.modal').querySelector('#id-bantuan').setAttribute('disabled', 'true');
                        e.target.closest('.modal').querySelector('#input-keterangan-rencana').setAttribute('disabled', 'true');
    
                        const optionList = e.target.closest('.modal').querySelector('#id-bantuan').children;
                        for (let index = 0; index < optionList.length; index++) {
                            const element = optionList[index];
                            if (element.value != response.feedback.input.id_bantuan) {
                                element.setAttribute('disabled', 'true');
                            }
                        }

                        objectRencana.id_rencana = response.feedback.id_rencana;
                        objectRencana.dml = true;
    
                        e.target.closest('#action').querySelector('[data-dismiss="modal"]').innerText = 'Tutup';
    
                        e.target.innerText = 'Lanjut Pelaksanaan';
                        e.target.classList.remove('btn-outline-primary');
                        e.target.classList.add('btn-outline-orange');
                        e.target.setAttribute('type', 'button');
                        e.target.setAttribute('id', 'buat-pelaksanaan');
    
                        const rencanaEl = document.querySelector('#' + modalId + ' #rencana');
                        const rabEl = '<div class="col-12 px-0 d-flex gap-4 flex-column" id="rab"><div class="row m-0"><button class="col-12 col-md-auto px-0 btn btn-primary m-0" data-target="#modalFormRab" data-toggle="modal" id="tambah-item-rab" type="button"><span class="p-3">Tambah Item</span></button><div class="col-12 col-md d-flex p-3 bg-lighter rounded align-items-center gap-x-2"><i class="fa-info fa"></i><h4 class="mb-0 update-total-rab">Rp. 0</h4></div></div><table class="table table-borderless table-hover table-responsive list-rab"><thead class="thead-light"><tr><th>Kebutuhan</th><th>Keterangan / Spesifikasi</th><th>Harga Satuan</th><th>Jumlah</th><th>Sub Total</th><th colspan="2" class="fit text-center">Aksi</th></tr></thead><tbody><tr><td colspan="6">Belum ada item RAB yang dibuat</td></tr></tbody></table></div>';
                        rencanaEl.insertAdjacentHTML('afterend', rabEl);
                        message = 'Rencana anggaran baru telah dibuat';

                        document.querySelector('#' + modalId + " #step-rencana span.date").innerText = new Date().toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');
                        
                        let obStepper = {
                            titleBox: 'Penganggaran RAB',
                            descBox: 'Membuat anggaran program',
                            dateBox: '',
                            id: 'step-rab'
                        };
        
                        createNewStepper(e.target.closest('.modal').querySelector('#stepper ol'), obStepper);
                        setTimeout(() => {
                            document.querySelector('#list-rencana').closest('.card').querySelector('.card-footer ul.pagination .page-link.page-item.active').click();
                        }, 0);
                    } else if (e.target.getAttribute('id') == 'buat-pencairan') {
                        // fetch ulang saldo_total_rab, saldo_anggaran
                        let resultFetch = data;
    
                        resultFetch.saldo_total_rab = +resultFetch.saldo_total_rab;
                        resultFetch.saldo_anggaran = +resultFetch.saldo_anggaran;
                        resultFetch.total_anggaran = +resultFetch.total_anggaran;

                        let tabActive = e.target.closest('.modal').querySelector('.tab-pane.active.show');

                        // cek ketersedian terbaru apakah saldo anggaran cukup
                        if (resultFetch.saldo_anggaran < +objectPenganggaran.total_penganggaran) {
    
                            objectAnggaran.saldo_total_rab = resultFetch.saldo_total_rab;
                            objectAnggaran.saldo_anggaran = resultFetch.saldo_anggaran;
    
                            if (e.target.closest('.modal').querySelector('#budget-warning') != null) {
                                e.target.closest('.modal').querySelector('#budget-warning .text-sm>b').innerText = numberToPrice(objectAnggaran.saldo_anggaran);
                            } else {
                                const budget_warning_html_rab_del = '<div class="col-12 px-0" id="budget-warning"><div class="box rounded bg-gradient-danger text-white"><div class="px-2"><h4 class="mb-0 text-white">Saldo anggaran program <span class="font-weight-bolder">Tidak Cukup !!</span></h4><div class="text-sm">Resiko anda hanya dapat mencairkan program sejumlah <b>' + numberToPrice(objectAnggaran.saldo_anggaran) + '</b> untuk anggaran tertentu pada tahap ini, atau gunakan <span class="font-weight-bolder" id="btn-dana-talang">data talang</span> <a href="#" class="font-weight-light text-white small">(Syarat dan ketentuan berlaku *)</a></div></div></div></div>';
                                document.querySelector('#stepper').insertAdjacentHTML('afterend', budget_warning_html_rab_del);
                            }
    
                            // recheck rab untuk dicairkan jika saldo anggaran mengurang saat hendak create pelaksanaan
                            const checkedRabList = tabActive.querySelectorAll('table tbody>tr>td .custom-toggle input[checked="true"]');
                            for (let index = checkedRabList.length - 1; index >= 0; index--) {
                                const element = checkedRabList[index];
                                element.closest('label').click();
                                if (+objectPenganggaran.total_penganggaran <= objectAnggaran.saldo_anggaran) {
                                    break;
                                }
                            }
    
                            tabActive.querySelector('#total-rab').innerText = numberToPrice(resultFetch.total_anggaran);
                            tabActive.querySelector('#anggaran-tersedia').innerText = numberToPrice(resultFetch.saldo_anggaran);
    
                            $('#notifikasi').find('.modal-body').html('Terjadi kesalahan, <b>saldo anggaran tidak mencukupi!</b>');
                            $('#notifikasi').modal('show');
                            return false;
                        }
    
                        if (resultFetch.saldo_anggaran >= objectAnggaran.saldo_anggaran && +objectPenganggaran.total_penganggaran <= resultFetch.saldo_anggaran) {
                            if (e.target.closest('.modal').querySelector('#budget-warning') != null) {
                                e.target.closest('.modal').querySelector('#budget-warning').remove();
                            }
                        } else {
                            e.target.closest('.modal').querySelector('#budget-warning .text-sm>b').innerText = numberToPrice(objectPelaksanaan.total_anggaran);
                        }
    
                        document.querySelector('body').setAttribute('data-token', response.token);
                        fetchTokenChannel.postMessage({
                            token: body.getAttribute('data-token')
                        });

                        data = inData;
                        data.token = document.querySelector('body').getAttribute('data-token');

                        return fetch(url+'/apd', {
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
                            createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                            document.querySelector('body').setAttribute('data-token', response.token);
                            fetchTokenChannel.postMessage({
                                token: body.getAttribute('data-token')
                            });

                            if (response.error) {
                                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                                    'delay': 10000
                                }).toast('show');
                                return false;
                            }

                            let resultFetch = response.feedback.data;

                            console.log(resultFetch);
                            // if success create pelaksanaan s/d apd
                            objectAnggaran.saldo_total_rab = +resultFetch.saldo_total_rab;
                            objectAnggaran.saldo_anggaran = +resultFetch.saldo_anggaran;
                            objectPelaksanaan.total_anggaran = +resultFetch.total_anggaran;
                            objectPencairan.id_pelaksanaan = resultFetch.id_pelaksanaan;
        
                            tabActive.classList.remove('active');
                            tabActive.classList.remove('show');
                            e.target.closest('.modal').querySelector('#tab-pencairan').classList.add('active');
                            e.target.closest('.modal').querySelector('#tab-pencairan').classList.add('show');
        
        
                            tabActive = e.target.closest('.modal').querySelector('.tab-pane.active.show');
        
                            tabActive.querySelector('#saldo-rab').innerText = numberToPrice(objectAnggaran.saldo_total_rab);
                            tabActive.querySelector('#anggaran-tersedia').innerText = numberToPrice(objectAnggaran.saldo_anggaran);
                            tabActive.querySelector('#max-pencairan').innerText = numberToPrice(objectPelaksanaan.total_anggaran);
        
                            e.target.innerText = 'Kalkulasi Penarikan';
                            e.target.classList.remove('btn-outline-primary');
                            e.target.classList.add('btn-outline-orange');
                            e.target.setAttribute('id', 'kalkulasi-penarikan');

                            e.target.closest('.modal').querySelector("#step-pelaksanaan span.date").innerText = new Date().toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');
                            
                            let obStepper = {
                                titleBox: 'Pencairan Anggaran',
                                descBox: 'Menentukan jumlah pencairan dan petugasnya',
                                dateBox: '',
                                id: 'step-pencairan'
                            };
                            createNewStepper(e.target.closest('.modal').querySelector('#stepper ol'), obStepper);

                            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                                'autohide': true
                            }).toast('show');
                        });
                    } else if (e.target.getAttribute('id') == 'kalkulasi-penarikan') {
                        // result create pencairan
                        objectPencairan = {
                            id_pencairan: data.id_pencairan,
                            id_pelaksanaan: data.id_pelaksanaan,
                            total: +data.total,
                            persentase_penarikan: data.persentase_penarikan
                        };

                        document.querySelector('#' + modalId + " #step-pencairan span.date").innerText = new Date().toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');

                        e.target.closest('.modal').querySelector('#total-penarikan').innerText = numberToPrice(objectPencairan.total);
                        // fetct KalkulasiPencairan berdasarkan create pencairan
                        // result fetch KalkulasiPenarikan
                        objectListPenarikan = data.list_kalkulasi_penarikan;
                        let eWallet = false; 
    
                        let incRander = 0;
                        objectListPenarikan.forEach(dataKalkulasi => {
                            if (incRander > 0) {
                                if (dataKalkulasi.id_ca == objectListPenarikan[incRander-1].id_ca) {
                                    return;
                                }
                            }
                            let statusKalkulasiAccount = '<a href="#" class="badge badge-pill badge-success">Siap ditarik</a>';
                            if (dataKalkulasi.status_k != null || dataKalkulasi.status_m != null) {
                                statusKalkulasiAccount = '<a href="#" class="px-2 badge badge-pill badge-warning" data-target="#modalDetilPinbuk" data-toggle="modal">Sedang dalam proses pinbuk</a>';
                                statusKalkulasiAccount = statusKalkulasiAccount + '<p class="px-2 font-weight-900 mb-0 small nominal-pinbuk">' + numberToPrice(dataKalkulasi.nominal) + '</p>';
                            }

                            if (dataKalkulasi.jenis.toUpperCase() == 'EW') {
                                eWallet = true;
                            }

                            const trPenarikan = '<tr data-id-ca="' + dataKalkulasi.id_ca + '"><td><div class="media align-items-center gap-x-3"><div class="avatar rounded bg-transparent border"><img src="' + dataKalkulasi.path_gambar + '" alt="' + dataKalkulasi.nama + '" class="img-fluid"></div><div class="media-body small"><div class="text-black-50 font-weight-bold">' + dataKalkulasi.nomor + '</div><div class="text-black-50 font-weight-bolder">' + dataKalkulasi.atas_nama + '</div></div></div></td><td>' + statusKalkulasiAccount + '</td><td class="text-right nominal">' + numberToPrice(dataKalkulasi.nominal) + '</td><td class="fit text-right"><button class="btn btn-outline-orange btn-sm pinbuk" data-toggle="modal" data-target="#modalFormPinbuk"></button></td></tr > ';
                            e.target.closest('.modal').querySelector('table#list-penarikan tbody').insertAdjacentHTML('beforeend', trPenarikan);
                            incRander++;
                        });

                        if (eWallet) {
                            if (e.target.closest('.modal').querySelector('#info-for-pinbuk') == null) {
                                const pinbukInfoEl = '<div class="col-12" id="info-for-pinbuk"><div class="px-4 py-3 bg-gradient-info rounded"><div class="row align-items-center justify-content-between mb-3"><div class="col"><h3 class="text-white mb-0">Informasi</h3></div><div class="col text-right"><a href="/admin/pinbuk" class="font-weight-bolder btn btn-sm btn-primary" target="_self">Pindah Buku</a></div></div><div class="text-white text-sm">Dana donasi masih ada yang tersimpan di E-Wallet segera <span class="font-weight-900">pindah buku</span> ke salah satu rekening bank. Abaikan pesan ini jika proses pembayaran <span class="font-weight-900">Pembelian Barang dan Jasa</span> akan menggunakan E-Wallet tersebut.</div></div></div>';
                                e.target.closest('.modal').querySelector('#tab-penarikan>.row').insertAdjacentHTML('beforeend', pinbukInfoEl);
                            }
                        }
    
                        let tabActive = e.target.closest('.modal').querySelector('.tab-pane.active.show');
                        tabActive.classList.remove('show');
                        tabActive.classList.remove('active');
                        e.target.closest('.modal').querySelector('#tab-penarikan').classList.add('active');
                        e.target.closest('.modal').querySelector('#tab-penarikan').classList.add('show');
    
                        e.target.innerText = 'Submit';
                        e.target.classList.remove('btn-outline-orange');
                        e.target.classList.add('btn-outline-success');
                        e.target.setAttribute('id', 'pencairan');

                        let obStepper = {
                            titleBox: 'Kalkulasi Penarikan',
                            descBox: 'Menentukan sumber pencairan anggaran',
                            dateBox: '',
                            id: 'step-kalkulasi'
                        };
                        createNewStepper(e.target.closest('.modal').querySelector('#stepper ol'), obStepper);
                    } else if (e.target.id == 'pencairan') {
                        object
                        $('#' + modalId).modal('hide');
                    }
                }
    
                if (modalId == 'modalKonfirmasiAksi') {
                    const targetDataModal = document.getElementById(relatedModal),
                        statusR = statusRencana(data.fields.status);
    
                    targetDataModal.querySelector('#status-rencana').innerText = statusR.text;
                    relatedTarget.querySelector('span.status').innerText = statusR.text.toLowerCase();
                    relatedTarget.querySelector('span.status').previousElementSibling.setAttribute('class', statusR.class);
                    relatedTarget.classList.add('highlight');
                    message = message + ' menjadi <b>' + statusR.text.toLowerCase() + '</b>';
                    setTimeout(() => {
                        relatedTarget.classList.remove('highlight');
                    }, 3000);
                    $('#' + relatedModal).modal('hide');
                }
    
                if (modalId == 'modalKeteranganPerbaikanRAB') {
                    let statusR = statusRencana(data.fields.status);
                    document.querySelector('#modalRincianRAB #status-rencana').innerText = statusR.text;
                    relatedTarget.querySelector('span.status').innerText = statusR.text.toLowerCase();
                    relatedTarget.querySelector('span.status').previousElementSibling.setAttribute('class', statusR.class);
                    relatedTarget.classList.add('highlight');
                    message = message + ' menjadi <b>' + statusR.text.toLowerCase() + '</b>';
                    setTimeout(() => {
                        relatedTarget.classList.remove('highlight');
                    }, 3000);
                    $('#modalRincianRAB').modal('hide');
                }

            } else {
                // else failed fetch
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': false
                }).toast('show');

                if (modalId == 'modalTambahKebutuhan') {
                    if (response.feedback.message == 'Nama kebutuhan sudah ada') {
                        fetchCekNamaKebutuhan(inputNamaKebutuhan, response);
                        return false;
                    }
                }
            }

            // End of submit

            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });
        });

        data = {};
    });
});

const filePinbukEl = '<div data-target="#modalImgCanvasCropper" role="button" class="form-group inputGroup input-file rounded py-2 px-3 bg-secondary d-flex align-items-center justify-content-between"><input accept="image/*" type="file" name="id_gambar" id="id_gambar" class="file" title="Belum ada gambar terpilih"><div class="desc d-block"><label for="id_gambar" class="font-weight-bolder m-0 form-control-label w-auto">Bukti Pinbuk</label><div class="d-flex"><span class="rule">Dimensi 1280 x 768 pixel (16:9)</span></div></div><i class="ni ni-image"></i></div>';
document.getElementById('sudah-pinbuk').addEventListener('click', function (e) {
    if (this.checked) {
        this.closest('.form-group').insertAdjacentHTML('afterend', filePinbukEl);
    } else {
        this.closest('.modal-body').querySelectorAll('.input-file').forEach(ic => {
            ic.remove();
        });
    }
});

let doToastForFile = function (formGroup, input, toastId, fileName, fileSize) {
    if (fileMaxSizeMb < fileSize) {
        if (!formGroup.classList.contains('is-invalid')) {
            formGroup.classList.add('is-invalid');
            input.classList.add('is-invalid');
        }

        formGroup.querySelector('label').setAttribute('data-label-after', 'tidak boleh melibihi 2 MB');

        input.setAttribute('title', 'Ukuran file terlalu besar');
        input.parentElement.setAttribute('data-file-passed', false);
        toastElTime = document.querySelector('.toast[data-toast="' + toastId + '"] .toast-header .time-passed');

        toastPassed(toastElTime);

        $('[data-toast="' + toastId + '"]').toast('show').on('shown.bs.toast', function () {
            $(this).find('.toast-body').text('Ukuran file tidak boleh melebihi ' + fileMaxSizeMb + ' MB.');
        });
    } else {
        if (formGroup.classList.contains('is-invalid')) {
            formGroup.classList.remove('is-invalid');
            input.classList.remove('is-invalid');
        }

        input.parentElement.setAttribute('data-file-passed', true);
        input.setAttribute('title', 'Ganti gambar ' + fileName + '?');

        if (!$('[data-toast="' + toastId + '"]').hasClass('show')) {
            return false;
        }

        $('[data-toast="' + toastId + '"]').toast('hide');
    }
};

const fileMaxSizeMb = 2;
let targetName,
    targetFileName,
    src;
document.addEventListener('change', function (e) {
    if (e.target.getAttribute('type') != 'file') {
        return false;
    }
    const fileList = document.querySelectorAll('.inputGroup input[type="file"]');
    if (fileList.length) {

        fileList.forEach(fileEl => {
            if (fileEl.name == e.target.name && e.target.files[0] != undefined) {
                // Get the file
                const file = e.target.files[0],
                    { name: fileName, size } = file;


                // Open Modal
                const modal = e.target.closest('[data-target]').getAttribute('data-target');

                src = URL.createObjectURL(file);
                targetName = e.target.getAttribute('name');
                targetFileName = fileName;

                $(modal).modal('show');
            }
        });
    }
});

let nCanvas;
document.addEventListener('click', function (e) {
    if (e.target && e.target.name == 'id_gambar') {
        if (nCanvas == undefined) {
            nCanvas = e.target.nextElementSibling.querySelector('span.rule').innerText;
        }

        document.querySelectorAll('#modalImgCanvasCropper [data-dismiss]').forEach(el => {
            el.setAttribute('data-dismiss-for', e.target.name);
        });
        document.querySelector('#modalImgCanvasCropperLabel .text-orange').innerHTML = nCanvas;
    }
});

// Cropper
let cropper,
    cropData = {},
    cropedFile = {};

$('#modalImgCanvasCropper').on('show.bs.modal', function () {
    this.querySelector('.modal-body img').src = src;

    cropData.aspectRatio = 16 / 9;
    cropData.width = 696;
    cropData.height = (cropData.width / 16) * 9;
}).on('shown.bs.modal', function () {
    img = this.querySelector('.modal-body img');

    cropper = new Cropper(img, {
        aspectRatio: cropData.aspectRatio,
        viewMode: 3,
        dragMode: 'move',
        autoCropArea: 1,
        minCropBoxWidth: 140,
        minContainerWidth: 272,
        minContainerHeight: 81.01
    });
}).on('hidden.bs.modal', function () {
    if (document.getElementById('modalBuatRencana').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
    
    this.querySelector('.modal-body img').setAttribute('src', '');

    this.querySelectorAll('[data-dismiss]').forEach(el => {
        el.removeAttribute('data-dismiss-for');
    });

    cropper.destroy();

    let input = document.querySelector('[name="' + targetName + '"]');

    if (cropedFile[targetName] != undefined) {
        objectPinbuk[targetName] = cropedFile[targetName].data;
        let formGroup = input.closest('.form-group'),
            desc = formGroup.querySelector('.desc'),
            toastId = input.getAttribute('id');

        if (desc.querySelector('.rule') != null) {
            const resultDesc = '<small class="result d-flex gap-x-2"><span class="name">' + cropedFile[targetName].name + '</span><span class="size">' + cropedFile[targetName].size + 'MB</span></small>';
            desc.querySelector('.rule').remove();
            desc.insertAdjacentHTML('beforeend', resultDesc);
        } else {
            desc.querySelector('span.name').innerHTML = cropedFile[targetName].name;
            desc.querySelector('span.size').innerHTML = cropedFile[targetName].size + ' MB';
        }

        doToastForFile(formGroup, input, toastId, cropedFile[targetName].name, cropedFile[targetName].size);
    }

    input.value = '';
});

$('#modalImgCanvasCropper').on('click', '.modal-footer [type="button"]', function () {
    const imgReturnType = "image/jpeg";
    cropCanvas = cropper.getCroppedCanvas({
        width: cropData.width,
        height: cropData.height,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff',
    }).toDataURL(imgReturnType);

    const head = 'data:' + imgReturnType + ';base64,',
        fileSize = (cropCanvas.length - head.length);
    let fileSizeInKB = Math.round((fileSize / 1000).toFixed(2)),
        fileSizeInMB = (fileSizeInKB / 1000).toFixed(2);

    cropedFile[targetName] = {
        'data': cropCanvas,
        'size': fileSizeInMB,
        'name': targetFileName
    };

    $(this).parents('.modal').modal('hide');
});

function diff(prevObject, nextObject) {
    return Object.entries(nextObject).reduce((acc, cv) => {
        if (JSON.stringify(nextObject[cv[0]]) != JSON.stringify(prevObject[cv[0]]))
            acc.push(cv);
        return acc;
    }, []).reduce((acc, cv) => {
        acc[cv[0]] = cv[1];
        return acc;
    }, {});
}

let jumlah_halaman = 0;

if (document.querySelector('.pagination[data-pages]') != null) {
    jumlah_halaman = document.querySelector('.pagination').getAttribute('data-pages');
}

let statusRencana = function(status) {
    let arrayStatus = {};
    if (status.toUpperCase() == 'BD') {
        arrayStatus = {
            'text' : 'Belum Disetujui',
            'class' : 'bg-gradient-default'
        };
    } else if (status.toUpperCase() == 'SD') {
        arrayStatus = {
            'text' : 'Sudah Disetujui',
            'class' : 'bg-gradient-success'
        };
    } else if (status.toUpperCase() == 'BP') {
        arrayStatus = {
            'text' : 'Butuh Perbaikan',
            'class' : 'bg-gradient-warning'
        };
    } else {
        arrayStatus = {
            'text' : 'Tidak Disetujui',
            'class' : 'bg-gradient-danger'
        };
    }
    return arrayStatus;
}

let statusTahap = function(tahap, total_penarikan, total_pengadaan, total_anggaran, status_rencana, status_pelaksanaan, banyak_penarikan) {
    let status_akhir = {};
    if (tahap.toUpperCase() == 'PERENCANAAN') {
        status_akhir = statusRencana(status_rencana);
    } else if (tahap.toUpperCase() == 'PERSIAPAN') {
        status_akhir = {
            'text' : 'menyusun pencairan',
            'class' : 'bg-gradient-info'
        };
    } else if (tahap.toUpperCase() == 'PENCAIRAN') {
        persentase = ((total_penarikan / total_anggaran) * 100).toFixed(0) + ' % tercairkan';
        status_akhir = {
            'text' : persentase,
            'class' : 'bg-gradient-info'
        };
    } else if (tahap.toUpperCase() == 'PENGADAAN LANJUTAN') {
        sisa_pengadaan = numberToPrice(total_penarikan - total_pengadaan) + ' belum dibelanjakan';
        status_akhir = {
            'text' : sisa_pengadaan,
            'class' : 'bg-gradient-warning'
        };
    } else if (tahap.toUpperCase() == 'PENGADAAN') {
        persentase = round((total_penarikan / total_pengadaan) * 100, 2) + ' % digunakan';
        status_akhir = {
            'text' : persentase,
            'class' : 'bg-gradient-success'
        };
    } else if (tahap.toUpperCase() == 'PENCAIRAN LANJUTAN') {
        sisa_pencairan = tSparator(total_anggaran - total_penarikan) + ' perlu dicairkan';
        status_akhir = {
            'text' : sisa_pencairan,
            'class' : 'bg-gradient-danger'
        };
    } else if (tahap.toUpperCase() == 'SELESAI') {
        if (total_pengadaan != total_anggaran) {
            if (banyak_penarikan == null) {
                text = 'data persiapan kosong';
            } else if (total_penarikan != total_anggaran || total_pengadaan == null) {
                text = 'data pencairan kosong';
            } else if (total_pengadaan < total_penarikan) {
                text = 'data pengadaan kosong';
            } 
        } else {
            text = 'final';
        }
        status_akhir = {
            'text' : text,
            'class' : 'bg-gradient-primary'
        };
    } else {
        if (status_pelaksanaan.toUpperCase() != 'S') {
            status_akhir = {
                'text' : 'program',
                'class' : 'bg-gradient-danger'
            };
        }
    }
    return status_akhir;
}

let fetchListRAB = function(url, data, root, result) {
    let sentData = data;
    root.find('table').removeClass('load');
    if (result.error == false) { 
        let data = result.feedback.data;

        if (result.feedback.pages > 0 && data.length == 0 && result.feedback.total_record > 0) {
            document.querySelector('body').setAttribute('data-token', result.token);

            sentData.halaman = result.feedback.pages;
            sentData.token = result.token;
            
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });
            
            fetchData(url, sentData, root, 'list-rab');
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
            data.forEach(rab => {  
                let badge_class;
                if (rab.tahap == 'Perencanaan') {
                    badge_class = 'badge-warning';
                } else if (rab.tahap == 'Pencairan') {
                    badge_class = 'badge-success';
                } else if (rab.tahap == 'Persiapan') {
                    badge_class = 'badge-primary';
                } else if (rab.tahap == 'Pengadaan') {
                    badge_class = 'badge-danger';
                } else if (rab.tahap == 'Pencairan Lanjutan') {
                    badge_class = 'badge-success';
                } else if (rab.tahap == 'Pengadaan Lanjutan') {
                    badge_class = 'badge-danger';
                } else {
                    badge_class = 'badge-secondary';
                }

                if (rab.keterangan_rencana == null) { rab.keterangan_rencana = ''; }

                let status_tahap = statusTahap(rab.tahap, priceToNumber(rab.total_penarikan), priceToNumber(rab.total_pengadaan), priceToNumber(rab.total_anggaran), rab.status_rencana, rab.status_pelaksanaan, priceToNumber(rab.banyak_penarikan));

                let tr = '<tr data-id-rencana="' + rab.id_rencana + '"><th scope="row"><div class="d-flex flex-column"><a href="#" class="font-weight-bolder"><span>' + rab.nama_bantuan + '</span></a><span class="mb-0">' + rab.create_at_rencana + '</span></div></th><td class="align-bottom"><div class="d-flex flex-column"><span class="mb-0 ">' + rab.keterangan_rencana + '</span></div></td><td class="anggaran d-flex flex-column"><div class="total_anggaran font-weight-bold"><span>' + rab.total_anggaran + '</span></div><div class="jumlah-item"><span>' + rab.jumlah_item + '</span></div></td><td class="fit"><span class="badge ' + badge_class + ' p-2 text-capitalize">' + rab.tahap + '</span></td><td class="fit"><span class="badge badge-dot mr-4"><i class="' + (status_tahap.class != null ? status_tahap.class:'bg-gradient-primary') + '"></i><span class="status">' + status_tahap.text + '</span></span></td><td class="text-right fit"><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Drop Down Action Record"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow"><a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalRincianRAB">Lihat RAB</a><a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalPerubahanRAB">Ubah RAB</a><a class="dropdown-item" href="#">Something else here</a></div></div></td></tr>';
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
            const tr0 = '<tr><td colspan="6">Data donasi tidak ditemukan...</td></tr>';
            root.find('tbody').append(tr0);
        }
    }
    else { 
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'autohide': false
        }).toast('show');
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    // nameNew.token = result.token;
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchGetRencana = function(url, inputData, root, result) {
    let data;
    if (result.error == false) {
        data = result.feedback.data;
        root.querySelector('#nama-bantuan').innerText = data.rencana.nama_bantuan;
        root.querySelector('#total-anggaran').innerText = data.rencana.total_anggaran;
        root.querySelector('#keterangan-rencana').innerText = data.rencana.keterangan;
        root.querySelector('#nama-pembuat-rencana').innerText = data.rencana.nama_pembuat;
        root.querySelector('#waktu-buat-rencana').innerText = data.rencana.create_at;
        root.querySelector('#status-rencana').innerText = data.rencana.status.text;
        const prefix = "text-";
        const classes = root.querySelector('#status-rencana').className.split(" ").filter(c => !c.startsWith(prefix));
        root.querySelector('#status-rencana').className = classes.join(" ").trim();
        root.querySelector('#status-rencana').classList.add(data.rencana.status.class);

        root.querySelector('tbody').innerHTML = '';
        if (data.rab_list.length > 0) {
            root.querySelector('table[table-absolute-first="on"]').classList.add('table-responsive');
            data.rab_list.forEach(field => {
                let tr = '<tr style="height: 0px"><td class="fit" style="width:0px"><span>'+ field.nama_kebutuhan +'</span></td><td style="padding-left: calc(0px + 1rem)">'+ field.keterangan +'</td><td class="text-right">'+ field.harga_satuan +'</td><td class="text-right">'+ field.jumlah +'</td><td class="text-right">'+ field.nominal_kebutuhan +'</td></tr>';
                root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            });
        } else {
            root.querySelector('table[table-absolute-first="on"]').classList.remove('table-responsive');
            let tr = '<tr data-zero="true"><td colspan="5">Belum ada data RAB di rencana ini ... </td></tr>'
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        }

        if (data.pegawai != null) {
            if (data.pegawai.id_pegawai != data.rencana.id_pegawai && data.pegawai.id_jabatan <= '2') {
                let button_dd = '',
                    buttons = '',
                    sd = '<a class="dropdown-item font-weight-bold text-success" href="#" data-toggle="modal" data-target="#modalKonfirmasiAksi" data-mode="SD">Setuju</a>',
                    bp = '<a class="dropdown-item font-weight-bold text-warning" href="#" data-toggle="modal" data-target="#modalKeteranganPerbaikanRAB" data-mode="BP">Butuh Perbaikan</a>',
                    td = '<a class="dropdown-item font-weight-bold text-danger" href="#" data-toggle="modal" data-target="#modalKonfirmasiAksi"data-mode="TD">Tidak Setuju</a>';

                switch (data.rencana.status.value) {
                    case 'SD':
                        buttons = bp;
                        break;
                    case 'BP':
                        buttons = td + sd;
                        break;
                    case 'TD':
                        buttons = bp;
                        break;
                    default:
                        buttons = td + bp + sd;
                        break;
                }

                button_dd = '<div class="dropdown" id="kelola-rab"><button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">Kelola Status RAB</button><div class="dropdown-menu">'+ buttons +'</div></div>';
                root.querySelector('[data-dismiss="modal"]').insertAdjacentHTML('afterend', button_dd);
            } else if (data.pegawai.id_pegawai == data.rencana.id_pegawai) {
                bp = '<button id="kelola-rab" class="btn btn-primary" type="button" href="#" data-toggle="modal" data-target="#modalPerbaikanRAB" data-mode="PRAB">Ubah Rencana & RAB</button>';
                root.querySelector('[data-dismiss="modal"]').insertAdjacentHTML('afterend', bp);
            }
        }
    }
    else {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'autohide': false
        }).toast('show');
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchGetDetilRab = function(url, inputData, root, result) {
    let data;
    if (result.error == false) {
        data = result.feedback.data;
        // console.log(data);
        root.querySelector('#nama-bantuan-update').innerText = data.rencana.nama_bantuan;
        root.querySelector('#total-anggaran-update').innerText = data.rencana.total_anggaran;
        root.querySelector('#saldo-donasi-update').innerText = data.rencana.max_anggaran;
        root.querySelector('#keterangan-rencana-update').innerText = data.rencana.keterangan;
        root.querySelector('#nama-pembuat-rencana-update').innerText = data.rencana.nama_pembuat;
        root.querySelector('#waktu-buat-rencana-update').innerText = data.rencana.create_at;
        root.querySelector('#waktu-pembaharuan-rencana-update').innerText = data.rencana.modified_at;
        root.querySelector('#status-rencana-update').innerText = data.rencana.status.text;
        root.querySelector('#total-teranggarkan-rencana-update').innerText = numberToPrice(data.rencana.total_teranggarkan);
        root.querySelector('#belum-teranggarkan-rencana-update').innerText = numberToPrice(priceToNumber(data.rencana.total_anggaran) - priceToNumber(data.rencana.total_teranggarkan));
        
        const prefix = "text-";
        const classes = root.querySelector('#status-rencana-update').className.split(" ").filter(c => !c.startsWith(prefix));
        root.querySelector('#status-rencana-update').className = classes.join(" ").trim();
        root.querySelector('#status-rencana-update').classList.add(data.rencana.status.class);
        
        if (priceToNumber(data.rencana.total_anggaran) - priceToNumber(data.rencana.total_teranggarkan) > priceToNumber(data.rencana.max_anggaran)) {
            if (root.querySelector('#budget-warning') == null) {
                const budget_warning_html_ubah_rab = '<div class="col-12" id="budget-warning"><div class="box rounded bg-gradient-danger text-white"><div class="px-2"><h4 class="mb-0 text-white">Saldo anggaran program <span class="font-weight-bolder">Tidak Cukup !!</span></h4><div class="text-sm">Resiko anda hanya dapat mencairkan program sejumlah <b>' + data.rencana.max_anggaran + '</b> untuk anggaran tertentu pada tahap ini, atau gunakan <span class="font-weight-bolder" id="btn-dana-talang">data talang</span> nanti <a href="#" class="font-weight-light text-white small">(Syarat dan ketentuan berlaku *)</a></div></div></div></div>';
                root.querySelector('.container>.row#box-bg-light-info').insertAdjacentHTML('beforeend', budget_warning_html_ubah_rab);
            } else {
                root.querySelector('#budget-warning b').innerText = data.rencana.total_anggaran;
            }
        }

        if (data.rab_list.length > 0) {
            root.querySelector('tbody').innerHTML = '';
            data.rab_list.forEach(field => {
                let tr = '<tr data-id-rab="'+ field.id_rab +'" style="height: 0px"'+ (field.teranggarkan != null ? (field.teranggarkan == 1 ? 'class="budgeted"':'class="not-budgeted"') : '') +'><td class="fit" style="width:0px"><span>'+ field.nama_kebutuhan +'</span></td><td style="padding-left: calc(0px + 1rem)">'+ field.keterangan +'</td><td class="text-right">'+ field.harga_satuan +'</td><td class="text-right">'+ field.jumlah +'</td><td class="text-right">'+ field.nominal_kebutuhan +'</td><td class="px-0"><a href="#" class="btn btn-outline-danger btn-sm font-weight-bolder delete">Hapus</a></td><td><a href="#" class="btn btn-outline-orange btn-sm font-weight-bolder update" data-target="#modalFormRab">Ubah</a></td></tr>';
                root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            });
        } else {
            let tr = '<tr data-zero="true"><td colspan="6">Belum ada data RABnya ...</td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        }
    } else {

    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchGetRab = function(url, inputData, root, result) {
    let data;
    if (result.error == false) {
        data = result.feedback.data;
        data.id_rab = inputData.id_rab;
        // console.log(data);
        
        root.querySelector('#formJudul').setAttribute('data-mode', 'update');
        root.querySelector('#formJudul').innerText = 'Ubah';
        root.querySelector('#input-harga-satuan').value = data.harga_satuan;
        root.querySelector('#input-jumlah').value = data.jumlah;
        root.querySelector('#input-keterangan-rab').value = data.keterangan;

        if (root.querySelector('.btn[type="clear"]') != null) {
            root.querySelector('.btn[type="clear"]').innerText = "Reset";
            root.querySelector('.btn[type="clear"]').setAttribute('type', 'reset');
        }

        data.kebutuhan = {
            id_kebutuhan: data.id_kebutuhan,
            nama:data.nama,
            kategori:data.kategori,
            jumlah_item_rab_ini:data.jumlah_item_rab_ini
        };

        delete data.id_kebutuhan;
        delete data.nama;
        delete data.kategori;
        delete data.jumlah_item_rab_ini;

        // data result
        resultRab = data;
        // for select2 ok
        const optionKebutuhan = selectLabelKebutuhan([data.kebutuhan]);
        resetDataSelect2($('#id-kebutuhan'), optionKebutuhan);

        /* 
        Ini di aktivkan jika list yang mau di tampilkan pada optionKebutuhan selain data.kebutuhan 
        dan data.kebutuhan terpilih posisinya di bawah
        Namun jika cuma 1 baris data tidak perlu diaktifkan 
        */
        // $('#id-kebutuhan').val(data.kebutuhan.id_kebutuhan).change();

        objectRab = {};
        $('#modalFormRab').modal('show');
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchCekNamaKebutuhan = function(thisInput, result) {
    if (result.error) {
        if (!thisInput.parentElement.classList.contains('is-invalid')) {
            thisInput.parentElement.classList.add('is-invalid');
            thisInput.classList.add('is-invalid');
        }
        if (thisInput.classList.contains('custom-select')) {
            if (!thisInput.hasAttribute('multiple')) {
                thisInput.nextElementSibling.querySelector('label').setAttribute('data-label-after', result.feedback.message);
            } else {
                thisInput.parentElement.querySelector('label').setAttribute('data-label-after', result.feedback.message);
            }
        } else {
            thisInput.parentElement.querySelector('label').setAttribute('data-label-after', result.feedback.message);
        }
    } else {
        if (thisInput.parentElement.classList.contains('is-invalid')) {
            thisInput.parentElement.classList.remove('is-invalid');
            thisInput.classList.remove('is-invalid');
            if (thisInput.classList.contains('custom-select')) {
                thisInput.nextElementSibling.querySelector('label').removeAttribute('data-label-after');
                if (!thisInput.hasAttribute('multiple')) {
                    thisInput.nextElementSibling.querySelector('label').removeAttribute('data-label-after');
                } else {
                    thisInput.parentElement.querySelector('label').removeAttribute('data-label-after');
                }
            } else {
                thisInput.parentElement.querySelector('label').removeAttribute('data-label-after');
            }
        }
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchGetRabForDelete = function(result, root) {
    if (result.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'autohide': false
        }).toast('show');
    } else {        
        resultRab = result.feedback.data;
        root.querySelector('#kebutuhan').innerText = resultRab.nama;
        root.querySelector('#spec-ket').innerText = resultRab.keterangan;

        $('#modalKonfirmasiHapusRab').modal('show');
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchRekalkulasiPenarikan = function(result, root) {
    if (result.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
    } else {
        let tbody = root.querySelector('table#list-penarikan tbody'),
            data = result.feedback.data,
            eWallet = false;

        tbody.innerHTML = '';

        if (data.length > 0) {
            let incRander = 0;
            data.forEach(dataKalkulasi => {
                if (incRander > 0) {
                    if (dataKalkulasi.id_ca == data[incRander-1].id_ca) {
                        return;
                    }
                }
                let statusKalkulasiAccount = '<a href="#" class="badge badge-pill badge-success">Siap ditarik</a>';
                if (dataKalkulasi.status_k != null || dataKalkulasi.status_m != null) {
                    statusKalkulasiAccount = '<a href="#" class="px-2 badge badge-pill badge-warning" data-target="#modalDetilPinbuk" data-toggle="modal">Sedang dalam proses pinbuk</a>';
                    statusKalkulasiAccount = statusKalkulasiAccount + '<p class="px-2 font-weight-900 mb-0 small nominal-pinbuk">' + numberToPrice(dataKalkulasi.nominal) + '</p>';
                }

                if (dataKalkulasi.jenis.toUpperCase() == 'EW') {
                    eWallet = true;
                }

                let tr = '<tr data-id-ca="' + dataKalkulasi.id_ca + '"><td><div class="media align-items-center gap-x-3"><div class="avatar rounded bg-transparent border"><img src="' + dataKalkulasi.path_gambar + '" alt="' + dataKalkulasi.nama + '" class="img-fluid"></div><div class="media-body small"><div class="text-black-50 font-weight-bold">' + dataKalkulasi.nomor + '</div><div class="text-black-50 font-weight-bolder">' + dataKalkulasi.atas_nama + '</div></div></div></td><td>' + statusKalkulasiAccount + '</td><td class="text-right nominal">' + numberToPrice(dataKalkulasi.nominal) + '</td><td class="fit text-right"><button class="btn btn-outline-orange btn-sm pinbuk" data-toggle="modal" data-target="#modalFormPinbuk"></button></td></tr > ';
                tbody.insertAdjacentHTML('beforeend', tr);
                incRander++;
            });
            objectListPenarikan = data;
        } else {
            let tr = '<tr data-zero="true"><td colspan="4">Data penarikan tidak ditemukan ...</td></tr>';
            tbody.insertAdjacentHTML('beforeend', tr);
        }

        if (eWallet) {
            if (root.querySelector('#info-for-pinbuk') == null) {
                const pinbukInfoEl = '<div class="col-12" id="info-for-pinbuk"><div class="px-4 py-3 bg-gradient-info rounded"><div class="row align-items-center justify-content-between mb-3"><div class="col"><h3 class="text-white mb-0">Informasi</h3></div><div class="col text-right"><a href="/admin/pinbuk" class="font-weight-bolder btn btn-sm btn-primary" target="_self">Pindah Buku</a></div></div><div class="text-white text-sm">Dana donasi masih ada yang tersimpan di E-Wallet segera <span class="font-weight-900">pindah buku</span> ke salah satu rekening bank. Abaikan pesan ini jika proses pembayaran <span class="font-weight-900">Pembelian Barang dan Jasa</span> akan menggunakan E-Wallet tersebut.</div></div></div>';
                root.querySelector('#tab-penarikan>.row').insertAdjacentHTML('beforeend', pinbukInfoEl);
            }
        } else {
            if (root.querySelector('#info-for-pinbuk') != null) {
                root.querySelector('#info-for-pinbuk').remove();
            }
        }
    }

    console.log(result);

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

function statusPinbuk(status_pinbuk) {
    let status = {};
    // if (!dibaca) {
        status.class = 'badge-danger';
    // } else {
        // status.class = 'badge-info';
    // }

    if (status_pinbuk == 'OP') {
        status.text = 'On Proses';
    } else if (status_pinbuk == 'WTV') {
        status.text = 'Menunggu Diverivikasi';
        status.class = 'badge-warning';
    } else {
        status.text = 'Success';
        status.class = 'badge-success';
    }
    return status;
}

let fetchReadDetilPenarikanHasPinbuk = function(result, root) {
    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });

    if (result.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    let tbody = root.querySelector('table#list-detil-pinbuk tbody'),
        data = result.feedback.data;

    if (data.length < 1) {
        let tr = '<tr data-zero="true"><td colspan="4">Data penarikan tidak ditemukan ...</td></tr>';
            tbody.insertAdjacentHTML('beforeend', tr);
    } else {
        data.forEach(detil => {

            if (detil.id_pinbuk_k != null) {
                let jenis_pinbuk = {
                    status: 'text-danger',
                    io: 'fa-arrow-right'
                };
                let status = statusPinbuk(detil.status_k);
                let trK = '<tr><td><div class="d-inline-flex"><div class="avatar rounded bg-transparent border"><img src="'+ detil.path_gambar +'" alt="'+ detil.nama +'" class="img-fluid"></div><i class="d-flex align-items-center px-3 fas '+ jenis_pinbuk.io +' '+ jenis_pinbuk.status +'"></i><div class="media align-items-center gap-x-3"><div class="avatar rounded bg-transparent border"><img src="'+ detil.path_gambar_keluar +'" alt="'+ detil.nama_keluar +'" class="img-fluid"></div><div class="media-body small"><div class="text-black-50 font-weight-bold">'+ detil.nomor +'</div><div class="text-black-50 font-weight-bolder">'+ detil.atas_nama +'</div></div></div></div></td><td class="fit"><span class="badge badge-pill pt-2 '+ status.class +'">'+ status.text +'</span></td><td class="text-right fit"><b>'+ numberToPrice(+detil.sum_nominal_pindah_keluar) +'</b></td></tr>';
                tbody.insertAdjacentHTML('beforeend', trK);
            }

            if (detil.id_pinbuk_m != null) {
                let jenis_pinbuk = {
                    status: 'text-success',
                    io: 'fa-arrow-left'
                }
                let status = statusPinbuk(detil.status_m);
                let trM = '<tr><td><div class="d-inline-flex"><div class="avatar rounded bg-transparent border"><img src="'+ detil.path_gambar +'" alt="'+ detil.nama +'" class="img-fluid"></div><i class="d-flex align-items-center px-3 fas '+ jenis_pinbuk.io +' '+ jenis_pinbuk.status +'"></i><div class="media align-items-center gap-x-3"><div class="avatar rounded bg-transparent border"><img src="'+ detil.path_gambar_masuk +'" alt="'+ detil.nama_masuk +'" class="img-fluid"></div><div class="media-body small"><div class="text-black-50 font-weight-bold">'+ detil.nomor +'</div><div class="text-black-50 font-weight-bolder">'+ detil.atas_nama +'</div></div></div></div></td><td class="fit"><span class="badge badge-pill pt-2 '+ status.class +'">'+ status.text +'</span></td><td class="text-right fit"><b>'+ numberToPrice(+detil.sum_nominal_pindah_masuk) +'</b></td></tr>';
                tbody.insertAdjacentHTML('beforeend', trM);
            }

        });
    }
}

let fetchGetKalkulasiPenarikanCA = function(result, root) {
    if (result.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
    } else {
        let data = result.feedback.data;
        // for select2 ok
        objectPinbuk.id_ca_pengirim = data.id_ca;
        objectPinbuk.max_pinbuk = +data.nominal;
        const optionPengirim = selectLabelChannelAccount([data]);
        resetDataSelect2($('#id-ca-pengirim'), optionPengirim);
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let fetchGetDataPelaksanaan = function(result, root) {
    if (result.error) {
        createNewToast(document.querySelector('[aria-live="polite"]'), result.toast.id, result.toast.data_toast, result.toast);
        $('#'+ result.toast.id +'.toast[data-toast="'+ result.toast.data_toast +'"]').toast({
            'autohide': false
        }).toast('show');
    } else {
        let data = result.feedback.data;

        root.querySelector("#step-rab span.date").innerText = new Date(data.modified_at).toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');

        let obStepper = {
            titleBox: 'Penentuan Pelaksanaan',
            descBox: 'Menentukan pelaksanaan dan anggaran yang akan dicairkan',
            dateBox: '',
            id: 'step-pelaksanaan'
        };
        createNewStepper(root.querySelector('#stepper ol'), obStepper);
    
        root.querySelector('#pelaksanaan-ke').innerText = data.urutan_pelaksanaan;
        // hasil fetch <=> objectAnggaran.saldo_anggaran
        objectAnggaran.saldo_anggaran = +data.saldo_anggaran;
        // hasil fetch <=> objectAnggaran.saldo_total_rab
        objectAnggaran.saldo_total_rab = +data.saldo_total_rab;
        // hasil fetch <=> objectAnggaran.total_rab
        objectAnggaran.total_rab = +data.total_rab;
        root.querySelector('#total-rab').innerText = numberToPrice(objectAnggaran.total_rab);
        root.querySelector('#anggaran-tersedia').innerText = numberToPrice(objectAnggaran.saldo_anggaran);

        if (objectAnggaran.saldo_anggaran < objectAnggaran.total_rab && objectAnggaran.saldo_total_rab > objectAnggaran.saldo_anggaran) {
            if (root.querySelector('#budget-warning') != null) {
                root.querySelector('#budget-warning .text-sm>b').innerText = numberToPrice(objectAnggaran.saldo_anggaran);
            } else {
                const budget_warning_html_pelaksanaan = '<div class="col-12 px-0" id="budget-warning"><div class="box rounded bg-gradient-danger text-white"><div class="px-2"><h4 class="mb-0 text-white">Saldo anggaran program <span class="font-weight-bolder">Tidak Cukup !!</span></h4><div class="text-sm">Resiko anda hanya dapat mencairkan program sejumlah <b>Rp. ' + numberToPrice(objectAnggaran.saldo_anggaran) + '</b> untuk anggaran tertentu pada tahap ini, atau gunakan <span class="font-weight-bolder" id="btn-dana-talang">data talang</span> nanti <a href="#" class="font-weight-light text-white small">(Syarat dan ketentuan berlaku *)</a></div></div></div></div>';
                document.querySelector('#stepper').insertAdjacentHTML('afterend', budget_warning_html_pelaksanaan);
            }
        } else {
            if (document.querySelector('#budget-warning') != null) {
                document.querySelector('#budget-warning').remove();
            }
        }

        // hasil fetch <=> dataListRab
        let dataListRab = data.list_rab.map(function (elments) {
            return {
                id_rab: elments.id_rab,
                nama_kebutuhan: elments.nama_kebutuhan,
                keterangan: elments.keterangan,
                sub_total: priceToNumber(elments.nominal_kebutuhan)
            };
        });
        
        let total_penggunaaan_anggaran = 0,
            checked = '',
            rab_list = [];
        dataListRab.forEach(data => {
            checked = '';
            if (total_penggunaaan_anggaran <= objectAnggaran.saldo_anggaran && (total_penggunaaan_anggaran + data.sub_total) <= objectAnggaran.saldo_anggaran) {
                total_penggunaaan_anggaran += data.sub_total;
                rab_list.push(data.id_rab);
                checked = ' checked="true"';
            }

            const trRab = '<tr data-id-rab="' + data.id_rab + '"><td>' + data.nama_kebutuhan + '</td><td>' + data.keterangan + '</td><td class="text-right">' + numberToPrice(data.sub_total) + '</td><td class="text-right"><label class="custom-toggle ml-auto"><input type="checkbox"' + checked + '><span class="custom-toggle-slider rounded-circle" data-label-off="Jangan" data-label-on="Ya"></span></label></td></tr>';
            document.querySelector('table#pilih-anggaran>tbody').insertAdjacentHTML('beforeend', trRab);
            document.getElementById('penggunaan-anggaran').innerText = numberToPrice(total_penggunaaan_anggaran);
        });

        objectPenganggaran.total_penganggaran = total_penggunaaan_anggaran;
        objectPenganggaran.result = dataListRab;
        objectPenganggaran.selected = rab_list;

        let tablePAnggaran = document.querySelector('table#pilih-anggaran');

        if (tablePAnggaran.classList.contains('table-responsive')) {
            doAbsoluteFirstAdd(tablePAnggaran);
        } else {
            doAbsoluteFirstRemove(tablePAnggaran);
        }

        if (+objectBantuan.jumlah_target > 0) {
            if (+objectBantuan.jumlah_target_diselesaikan > 0) {
                document.getElementById('input-jumlah-pelaksanaan').setAttribute('data-min', objectBantuan.jumlah_target_diselesaikan);
            }
            document.getElementById('input-jumlah-pelaksanaan').setAttribute('data-max', objectBantuan.jumlah_target - objectBantuan.jumlah_target_diselesaikan);
        }
    }

    document.querySelector('body').setAttribute('data-token', result.token);
    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
};

let resetDataSelect2 = function(el, data) {
    el.html('');

    let dataAdapter = el.data('select2').dataAdapter;
    dataAdapter.addOptions(dataAdapter.convertToOptions(data));
};

let fetchData = function (url, data, root, f) {
    if (f == 'list-rab') {
        root.find('body').addClass('load');
    }
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
        switch (f) {
            case 'list-rab':
                fetchListRAB(url, data, root, response);
                break;
            case 'get-rencana':
                fetchGetRencana(url, data, root, response);
                break;
            case 'get-rab-detil':
                fetchGetDetilRab(url, data, root, response);
                break;
            case 'get-rab':
                fetchGetRab(url, data, root, response);
                break;
            case 'cek-nama':
                fetchCekNamaKebutuhan(root, response);
                break;
            case 'get-rab-for-delete':
                fetchGetRabForDelete(response, root);
                break;
            case 'data-pelaksanaan':
                fetchGetDataPelaksanaan(response, root);
                break;
            case 'rekalkulasi-penarikan':
                fetchRekalkulasiPenarikan(response, root);
                break
            case 'get-kalkulasi-penarikan-ca':
                fetchGetKalkulasiPenarikanCA(response, root);
                break;
            case 'read-detil-penarikan-pinbuk':
                fetchReadDetilPenarikanHasPinbuk(response, root);
                break;
            default:
                break;
        }
    })
};

// Event
let delayTimer,
oldSearchValue;
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

        if (oldSearchValue != data.search) {
            oldSearchValue = data.search;
        } else {
            return false;
        }
        
        // console.log(data);
        fetchData('/admin/fetch/read/rab/list', data, root, 'list-rab');
    }, 1000);
});

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

    // console.log(data);
    fetchData('/admin/fetch/read/rab/list', data, root, 'list-rab');

    e.preventDefault();
});

let x = [
    { id_kebutuhan: 1, nama: 'Operasional', kategori: 'Jasa', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 2, nama: 'Kebutuhan 1', kategori: 'Barang', jumlah_item_rab_ini: '1' },
    { id_kebutuhan: 3, nama: 'Snak', kategori: 'Makanan', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 4, nama: 'Air Mineral', kategori: 'Minuman', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 5, nama: 'Galon', kategori: 'Barang', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 6, nama: 'Minibus Pickup', kategori: 'Kendaraan', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 7, nama: 'Engkel Box', kategori: 'Kendaraan', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 8, nama: 'Double Engkel', kategori: 'Kendaraan', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 9, nama: 'Geo Elektrik', kategori: 'Jasa', jumlah_item_rab_ini: '0' },
    { id_kebutuhan: 10, nama: 'Box Snak', kategori: 'Barang', jumlah_item_rab_ini: '0' }
];

function formatKebutuhan(kebut) {
    if (kebut.loading) {
        return kebut.text;
    }
    let $kebut;
    if (kebut.jumlah_item_rab_ini == null || kebut.jumlah_item_rab_ini == undefined || kebut.jumlah_item_rab_ini == '0') {
        $kebut = '<div class="font-weight-bolder">' + kebut.text + '</div>'
    } else {
        $kebut = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bold">' + kebut.text + '</span></div><div class="col-auto px-1 d-flex align-items-center"><span class="badge badge-circle badge-primary border-white badge-sm badge-floating font-weight-bold">' + kebut.jumlah_item_rab_ini + '</span></div></div>'
    }
    return $kebut;
};

function formatSelected(objectSelected) {
    const label = objectSelected.element.closest('select').parentElement.querySelector('label');

    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '';

    if (label != null) {
        $elSelected = label.outerHTML;
    }

    if (objectSelected.jumlah_item_rab_ini == null || objectSelected.jumlah_item_rab_ini == undefined || objectSelected.jumlah_item_rab_ini == '0') {
        $elSelected = $elSelected + '<div class="font-weight-normal">' + objectSelected.text + '</div>';
    } else {
        $elSelected = $elSelected + '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bold">' + objectSelected.text + '</span></div><div class="col-auto px-1 d-flex align-items-center"><span class="badge badge-circle badge-primary border-white badge-sm badge-floating font-weight-bold">' + objectSelected.jumlah_item_rab_ini + '</span></div></div>'
    }

    return $elSelected;
}

function formatSelectedMultiple(objectSelected) {
    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '<div class="font-weight-normal">' + objectSelected.text + '</div>';
    return $elSelected;
}

function selectLabelPetugasPencairan(array) {
    return Object.values(array.reduce((accu, { id_pegawai: id, nama_jabatan: text, nama_pegawai }) => {
        (accu[text] ??= { text, children: [] }).children.push({ id, text: nama_pegawai });
        return accu;
    }, {}));
}

function selectLabelKebutuhan(array) {
    return Object.values(array.reduce((accu, { id_kebutuhan: id, kategori: text, nama, jumlah_item_rab_ini }) => {
        (accu[text] ??= { text, children: [] }).children.push({ id, text: nama, jumlah_item_rab_ini });
        return accu;
    }, {}));
}

// Select2 for Kebutuhan
function modelMatcher(params, data) {
    data.parentText = data.parentText || "";

    // Always return the object if there is nothing to compare
    if ($.trim(params.term) === '') {
        return data;
    }

    // Do a recursive check for options with children
    if (data.children && data.children.length > 0) {
        // Clone the data object if there are children
        // This is required as we modify the object to remove any non-matches
        var match = $.extend(true, {}, data);

        // Check each child of the option
        for (var c = data.children.length - 1; c >= 0; c--) {
            var child = data.children[c];
            child.parentText += data.parentText + " " + data.text;

            var matches = modelMatcher(params, child);

            // If there wasn't a match, remove the object in the array
            if (matches == null) {
                match.children.splice(c, 1);
            }
        }

        // If any children matched, return the new object
        if (match.children.length > 0) {
            return match;
        }

        // If there were no matching children, check just the plain object
        return modelMatcher(params, match);
    }

    // If the typed-in term matches the text of this term, or the text from any
    // parent term, then it's a match.
    var original = (data.parentText + ' ' + data.text).toUpperCase();
    var term = params.term.toUpperCase();

    // Check if the text contains the term
    if (original.indexOf(term) > -1) {
        return data;
    }

    // If it doesn't contain the term, don't return anything
    return null;
}

let dataKebutuhan = {};
let idKebutuhanUrl = '/admin/fetch/ajax/kebutuhan/rab';
$('#id-kebutuhan').select2({
    placeholder: "Pilih salah satu",
    // data: selectLabelKebutuhan(x),
    ajax: {
        url: idKebutuhanUrl,
        type: 'post',
        dataType: 'json',
        delay: 750,
        contentType: "application/json",
        data: function (params) {
            let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
            // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
            // if (input.val().length) {
            //     params.search = input.val();
            // }
            // delete params.term;
            // akhir uncomment

            // JIKA delay 750 masih error saat cek token maka comment code ini
            if (input.length != 0) {
                if (input.val().length) {
                    params.term = input.val();
                    params.search = params.term;
                }
            } else {
                params.search = params.term;
            }
            // akhir comment
            
            if (dataKebutuhan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataKebutuhan.search))) {
                params.offset = parseInt(dataKebutuhan.offset) + parseInt(dataKebutuhan.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            Object.assign(params,objectRencana);
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (response.error) {
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': false
                }).toast('show');
                $('#id-kebutuhan').select2('close');
                return false;
            }

            let data = selectLabelKebutuhan(response.feedback.data);
            
            if (response.feedback.search != undefined) {
                dataKebutuhan.search = response.feedback.search;
            } else {
                delete dataKebutuhan.search;
            }
            dataKebutuhan.offset = response.feedback.offset;
            dataKebutuhan.record = response.feedback.record;
            dataKebutuhan.limit = response.feedback.limit;
            dataKebutuhan.load_more = response.feedback.load_more;
            let pagination = {
                more: dataKebutuhan.load_more
            };
            return {results: data, pagination};
        },
        cache: true
    },
    matcher: modelMatcher,
    escapeMarkup: function (markup) { return markup; },
    templateResult: formatKebutuhan,
    templateSelection: formatSelected
}).on('select2:select', function (e) {
    if (this.value != '0') {
        objectRab.id_kebutuhan = this.value;
        objectRab.nama_kebutuhan = e.params.data.text;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectRab.id_kebutuhan;
        delete objectRab.nama_kebutuhan;
    }
    $(this).find('option[value!="'+ this.value +'"]:not([value="0"])').remove();
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

let xBantuan = [
    { id_bantuan: 1, nama: 'Bantuan 1', kategori: 'Pojok Wakaf', sektor: 'sosial', max_anggaran: 1000000 },
    { id_bantuan: 2, nama: 'Bantuan 2', kategori: 'Pojok Wakaf', sektor: 'lingkungan', max_anggaran: 250000 },
    { id_bantuan: 3, nama: 'Bantuan 3', kategori: 'Pojok Wakaf', sektor: null, max_anggaran: 0 },
    { id_bantuan: 4, nama: 'Bantuan 4', kategori: 'Pojok Berdaya', sektor: 'ekonomi', max_anggaran: 5000000 },
    { id_bantuan: 5, nama: 'Bantuan 5', kategori: 'Pojok Peduli', sektor: null, max_anggaran: 100000 },
    { id_bantuan: 6, nama: 'Bantuan 6', kategori: 'Pojok Kemanusiaan', sektor: null, max_anggaran: 300000 },
    { id_bantuan: 7, nama: 'Bantuan 7', kategori: null, sektor: null, max_anggaran: 0 }
];

function formatBantuan(bantuan) {
    if (bantuan.loading) {
        return bantuan.text;
    }
    let $bantuan;
    if ((bantuan.sektor == null || bantuan.sektor == undefined) && (bantuan.kategori == null || bantuan.kategori == undefined)) {
        $bantuan = '<div class="font-weight-bold">' + bantuan.text + '</div>'
    } else {
        $bantuan = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bold">' + bantuan.text + '</span></div><div class="col-auto px-1 d-flex align-items-end flex-column">'+ (bantuan.kategori == null ? '' : '<small>'+ bantuan.kategori +'</small>') + (bantuan.sektor == null ? '' : '<span class="badge badge-primary border-white badge-sm badge-floating font-weight-bold">' + bantuan.sektor + '</span>') + '</div></div>'
    }
    return $bantuan;
};

function selectLabelBantuan(array) {
    return array.map(function (elments) {
        return {
            id: elments.id_bantuan,
            text: elments.nama,
            kategori: elments.kategori,
            sektor: elments.sektor,
            max_anggaran: elments.max_anggaran
        };
    });
}

function modelMatcherBantuan(params, data) {
    data.parentText = data.parentText || "";

    if (data.parentText == "") {
        data.parentText = (data.sektor != null ? data.sektor : '') +' '+ data.kategori;
    }

    // Always return the object if there is nothing to compare
    if ($.trim(params.term) === '') {
        return data;
    }

    // Do a recursive check for options with children
    if (data.children && data.children.length > 0) {
        // Clone the data object if there are children
        // This is required as we modify the object to remove any non-matches
        var match = $.extend(true, {}, data);

        // Check each child of the option
        for (var c = data.children.length - 1; c >= 0; c--) {
            var child = data.children[c];
            child.parentText += data.parentText + " " + data.text;

            var matches = modelMatcherBantuan(params, child);

            // If there wasn't a match, remove the object in the array
            if (matches == null) {
                match.children.splice(c, 1);
            }
        }

        // If any children matched, return the new object
        if (match.children.length > 0) {
            return match;
        }

        // If there were no matching children, check just the plain object
        return modelMatcherBantuan(params, match);
    }

    // If the typed-in term matches the text of this term, or the text from any
    // parent term, then it's a match.
    var original = (data.parentText + ' ' + data.text).toUpperCase();
    var term = params.term.toUpperCase();

    // Check if the text contains the term
    if (original.indexOf(term) > -1) {
        return data;
    }

    // If it doesn't contain the term, don't return anything
    return null;
}

let dataBantuan = {};
let select2Bantuan = function() {
    $('#id-bantuan').select2({
        // data: selectLabelBantuan(xBantuan),
        ajax: {
            url: '/admin/fetch/ajax/bantuan/rab',
            type: 'post',
            dataType: 'json',
            delay: 750,
            contentType: "application/json",
            data: function (params) {
                let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
                // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
                // if (input.val().length) {
                //     params.search = input.val();
                // }
                // delete params.term;
                // akhir uncomment

                // JIKA delay 750 masih error saat cek token maka comment code ini
                if (input.length != 0) {
                    if (input.val().length) {
                        params.term = input.val();
                        params.search = params.term;
                    }
                } else {
                    params.search = params.term;
                }
                // akhir comment
                
                if (dataBantuan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataBantuan.search))) {
                    params.offset = parseInt(dataBantuan.offset) + parseInt(dataBantuan.limit);
                }
                params.offset = params.offset || 0;
                params.token = body.getAttribute('data-token');
                // console.log(params);
                return JSON.stringify(params);
            },
            processResults: function (response) {
                document.querySelector('body').setAttribute('data-token', response.token);
                fetchTokenChannel.postMessage({
                    token: body.getAttribute('data-token')
                });

                if (response.error) {
                    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                        'autohide': false
                    }).toast('show');
                    $('#id-bantuan').select2('close');
                    return false;
                }

                let data = selectLabelBantuan(response.feedback.data);
                
                if (response.feedback.search != undefined) {
                    dataBantuan.search = response.feedback.search;
                } else {
                    delete dataBantuan.search;
                }
                dataBantuan.offset = response.feedback.offset;
                dataBantuan.record = response.feedback.record;
                dataBantuan.limit = response.feedback.limit;
                dataBantuan.load_more = response.feedback.load_more;
                let pagination = {
                    more: dataBantuan.load_more
                };
                return {results: data, pagination};
            },
            cache: true
        },
        language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, },
        // placeholder: "Pilih salah satu",
        matcher: modelMatcherBantuan,
        escapeMarkup: function (markup) { return markup; },
        templateResult: formatBantuan,
        templateSelection: formatSelected
    }).on('select2:select', function (e) {
        if (this.value != '0') {
            objectRencana.id_bantuan = this.value;

            if (this.parentElement.classList.contains('is-invalid')) {
                this.parentElement.classList.remove('is-invalid');
                this.parentElement.querySelector('label').removeAttribute('data-label-after');
                this.classList.remove('is-invalid');
            }

            // fetch data
            const result = {
                max_anggaran : e.params.data.max_anggaran
            };
            
            // fetch success
            const elment = document.getElementById('rencana-program');
            if (document.getElementById('balance') == null) {
                let boxInfo = '<div class="px-0 col-12 col-md bg-lighter rounded"><div class="p-3" id="balance"><h4 class="mb-1">Saldo Anggaran Program</h4><div class="text-sm max-anggaran">' + numberToPrice(result.max_anggaran + '</div></div></div>');
                elment.insertAdjacentHTML('afterend', boxInfo);
            } else {
                document.querySelector('#balance>.text-sm').innerText = numberToPrice(result.max_anggaran);
            }

            document.getElementById('anggaran-tersedia').innerText = numberToPrice(result.max_anggaran);
            objectAnggaran.saldo_anggaran = result.max_anggaran;
        } else {
            delete objectRencana.id_bantuan; 
            objectAnggaran = {};
        }
        $(this).find('option[value!="'+ this.value +'"]:not([value="0"])').remove();
    }).on('select2:open', function () {
        if ($(this).hasClass("select2-hidden-accessible")) {
            if ($(this).hasClass('is-invalid')) {
                $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
            } else {
                $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
            }
        }
    })
};

select2Bantuan();

function selectLabelKategori(array) {
    return array.map(function (elments) {
        return {
            id: elments.id_kk,
            text: elments.nama
        };
    });
}

let dataKategoriKebutuhan = {};
$('#id-kk').select2({
    ajax: {
        url: '/admin/fetch/ajax/kategori-kebutuhan',
        type: 'post',
        dataType: 'json',
        delay: 750,
        contentType: "application/json",
        data: function (params) {
            let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
            // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
            // if (input.val().length) {
            //     params.search = input.val();
            // }
            // delete params.term;
            // akhir uncomment

            // JIKA delay 750 masih error saat cek token maka comment code ini
            if (input.length != 0) {
                if (input.val().length) {
                    params.term = input.val();
                    params.search = params.term;
                }
            } else {
                params.search = params.term;
            }
            // akhir comment
            
            if (dataKategoriKebutuhan.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataKategoriKebutuhan.search))) {
                params.offset = parseInt(dataKategoriKebutuhan.offset) + parseInt(dataKategoriKebutuhan.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            if ($(this).parents('.modal-content').find('#formJudul').data('mode') == 'create') {
                Object.assign(params,objectRencana);
            }
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (response.error) {
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': false
                }).toast('show');
                $('#id-kk').select2('close');
                return false;
            }

            let data = selectLabelKategori(response.feedback.data);
            
            if (response.feedback.search != undefined) {
                dataKategoriKebutuhan.search = response.feedback.search;
            } else {
                delete dataKategoriKebutuhan.search;
            }
            dataKategoriKebutuhan.offset = response.feedback.offset;
            dataKategoriKebutuhan.record = response.feedback.record;
            dataKategoriKebutuhan.limit = response.feedback.limit;
            dataKategoriKebutuhan.load_more = response.feedback.load_more;
            let pagination = {
                more: dataKategoriKebutuhan.load_more
            };
            return {results: data, pagination};
        },
        cache: true
    },
    language: { inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, },
    placeholder: "Pilih salah satu",
    escapeMarkup: function (markup) { return markup; },
    templateSelection: formatSelected
}).on('select2:select', function (e) {
    if (this.value != '0') {
        objectKebutuhan.id_kk = this.value;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }
    } else {
        delete objectKebutuhan.id_kk;
    }
    $(this).find('option[value!="'+ this.value +'"]:not([value="0"])').remove();
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

let dataPetugas = {};
$('#petugas-pencairan').select2({
    ajax: {
        url: '/admin/fetch/ajax/petugas-pencairan',
        type: 'post',
        dataType: 'json',
        delay: 750,
        contentType: "application/json",
        data: function (params) {
            let input = $('#select2-'+ $(this).attr('id') +'-results').parents('span.select2-dropdown').find('input.select2-search__field');
            // JIKA delay 750 masih error cek token maka cek token di matikan pakai uncomment code ini
            // if (input.val().length) {
            //     params.search = input.val();
            // }
            // delete params.term;
            // akhir uncomment

            // JIKA delay 750 masih error saat cek token maka comment code ini
            if (input.length != 0) {
                if (input.val().length) {
                    params.term = input.val();
                    params.search = params.term;
                }
            } else {
                params.search = params.term;
            }
            // akhir comment
            
            if (dataPetugas.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataPetugas.search))) {
                params.offset = parseInt(dataPetugas.offset) + parseInt(dataPetugas.limit);
            }
            params.offset = params.offset || 0;
            params.token = body.getAttribute('data-token');
            if (objectPencairan.petugas_pencairan != undefined) {
                params.petugas = Object.assign({}, objectPencairan.petugas_pencairan);
            }
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (response.error) {
                createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                    'autohide': false
                }).toast('show');
                $('#petugas-pencairan').select2('close');
                return false;
            }

            let data = selectLabelPetugasPencairan(response.feedback.data);
            
            if (response.feedback.search != undefined) {
                dataPetugas.search = response.feedback.search;
            } else {
                delete dataPetugas.search;
            }
            dataPetugas.offset = response.feedback.offset;
            dataPetugas.record = response.feedback.record;
            dataPetugas.limit = response.feedback.limit;
            dataPetugas.load_more = response.feedback.load_more;
            let pagination = {
                more: dataPetugas.load_more
            };
            return {results: data, pagination};
        },
        cache: true
    },
    language: {
        inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, maximumSelected: function (e) { return 'Maksimum petugas pencairan terpilih adalah ' + e.maximum + ' orang'; },
    },
    placeholder: "Pilih max dua orang",
    escapeMarkup: function (markup) { return markup; },
    maximumSelectionLength: 2,
    templateSelection: formatSelectedMultiple,
    multiple: true
}).on('change', function (e) {
    objectPencairan[e.target.name] = $(this).val();
    if (e.target.parentElement.querySelector('label').hasAttribute('data-label-after')) {
        e.target.parentElement.querySelector('label').removeAttribute('data-label-after');
    }
    $('#select2-' + $(this).attr('id') + '-results ul>li.select2-results__option--highlighted').remove();
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

let objectPinbuk = {};

const ca = [
    { id_ca: 1, nama: 'Bank BJB', nomor: '0001000080001', atas_nama: 'POJOK BERBAGI INDONESIA', jenis: 'RB', path_gambar: '/img/payment/bjb.png' },
    { id_ca: 4, nama: 'Bank BSI', nomor: '7400525255', atas_nama: 'POJOK BERBAGI INDONESIA', jenis: 'RB', path_gambar: '/img/payment/bsi.png' },
    { id_ca: 5, nama: 'Bank BRI', nomor: '107001000272300', atas_nama: 'POJOK BERBAGI INDONESIA', jenis: 'RB', path_gambar: '/img/payment/bri.png' },
    { id_ca: 6, nama: 'Dana', nomor: '081233311113', atas_nama: 'Pojok Berbagi', jenis: 'NW', path_gambar: '/img/payment/dana.png' },
    { id_ca: 7, nama: 'GoPay', nomor: '081233311113', atas_nama: 'Pojok Berbagi', jenis: 'NW', path_gambar: '/img/payment/gopay.png' }
];

function selectLabelChannelAccount(array) {
    return Object.values(array.reduce((accu, { id_ca: id, jenis: text, nama, nomor, atas_nama, path_gambar }) => {
        (accu[text] ??= { text: keteranganJenisChannelAccount(text), children: [] }).children.push({ id, text: nama, nomor, atas_nama, path_gambar });
        return accu;
    }, {}));
}

function formatSelectedChannelAccount(ca) {
    if (ca.loading) {
        return ca.text;
    }

    const label = ca.element.closest('select').parentElement.querySelector('label');

    let $ca = '';

    if (label != null) {
        $ca = label.outerHTML;
    }

    if (ca.path_gambar == null || ca.path_gambar == undefined) {
        $ca = $ca + '<div ' + (ca.nomor != undefined ? 'class="font-weight-bolder">' + ca.text + ' - ' + ca.nomor : 'class="font-weight-bold">' + ca.text) + '</div>'
    } else {
        $ca = $ca + '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bold">' + ca.nomor + '</span> - <span="font-weight-bold">(' + ca.atas_nama + ')</span></div><div class="col-1 p-0 d-flex align-items-center"><img src="' + ca.path_gambar + '" alt="' + ca.text + '" class="img-fluid"></div></div>'
    }
    return $ca;
};

function formatChannelAccount(ca) {
    if (ca.loading) {
        return ca.text;
    }
    let $ca;
    if (ca.path_gambar == null || ca.path_gambar == undefined) {
        $ca = '<div class="font-weight-bolder">' + ca.text + '</div>'
    } else {
        $ca = '<div class="row w-100 m-0 align-items-center"><div class="col p-0"><span class="font-weight-bolder">' + ca.nomor + '</span> - <span="font-weight-bold">(' + ca.atas_nama + ')</span></div><div class="col-1 p-0 d-flex align-items-center"><img src="' + ca.path_gambar + '" alt="' + ca.text + '" class="img-fluid"></div></div>'
    }
    return $ca;
};

$('#id-ca-penerima').select2({
    ajax: {
        url: '/admin/fetch/ajax/channel-account/penerima-pinbuk-kalkulasi',
        type: 'post',
        dataType: 'json',
        delay: 250,
        contentType: "application/json",
        data: function (params) {
            params.token = body.getAttribute('data-token');
            params.id_ca_pengirim = objectPinbuk.id_ca_pengirim;
            // console.log(params);
            return JSON.stringify(params);
        },
        processResults: function (response) {
            // console.log(response);
            document.querySelector('body').setAttribute('data-token', response.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (response.error) {
                if (response.error) {
                    createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                    $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                        'delay': 10000
                    }).toast('show');
                    $('#id-ca-penerima').select2('close');
                    return false;
                }
            }

            let data = response.feedback.data;

            return { results: selectLabelChannelAccount(data) };
        }
    },
    language: {
        inputTooShort: function () { return 'Ketikan minimal 1 huruf'; }, noResults: function () { return "Data yang dicari tidak ditemukan"; }, searching: function () { return "Sedang melakukan pencarian..."; }, loadingMore: function () { return "Menampilkan data yang lainnya"; }, maximumSelected: function (e) { return 'Maksimum petugas pencairan terpilih adalah ' + e.maximum + ' orang'; },
    },
    matcher: modelMatcher,
    placeholder: "Pilih salah satu",
    escapeMarkup: function (markup) { return markup; },
    templateSelection: formatSelectedChannelAccount,
    templateResult: formatChannelAccount
}).on('select2:select', function (e) {
    if (this.value != '0') {
        objectPinbuk.id_ca_penerima = this.value;

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
            this.parentElement.querySelector('label').removeAttribute('data-label-after');
            this.classList.remove('is-invalid');
        }

        if (priceToNumber(this.closest('.modal').querySelector('#input-nominal-pinbuk').value) > objectPinbuk.max_pinbuk) {
            this.closest('.modal').querySelector('#input-nominal-pinbuk').value = numberToPrice(objectPinbuk.max_pinbuk);
        }
    } else {
        delete objectPinbuk.id_ca_penerima;
    }
    $(this).find('option[value!="'+ this.value +'"]:not([value="0"])').remove();
    console.log(objectPinbuk);
}).on('select2:open', function () {
    if ($(this).hasClass("select2-hidden-accessible")) {
        if ($(this).hasClass('is-invalid')) {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').addClass('is-invalid');
        } else {
            $('#select2-' + $(this).attr('id') + '-results').parents('span.select2-dropdown').removeClass('is-invalid');
        }
    }
});

// datepicker
let d = new Date('08/11/2021');

// extended function for datepicker
const dateRanges = (date = new Date(), rule = 10, sum = 0) => Math.floor(date.getFullYear() / rule) * rule + sum;

$('.datepicker').datepicker({
    todayBtn: "linked",
    language: 'id',
    format: 'd MM yyyy',
    autoclose: true,
    enableOnReadonly: false // readonly input will not show datepicker . The default value true
}).change(function (e) {
    // submitControl(e.target);
    if (this.classList.contains('is-invalid')) {
        this.classList.remove('is-invalid');
        this.closest('.form-label-group.is-invalid').removeAttribute('data-label-after');
        this.closest('.form-label-group.is-invalid').classList.remove('is-invalid');
    }
    objectPelaksanaan.tanggal_pelaksanaan = $(this).datepicker('getDate');
}).on('show', function (e) {
    let allowedPicker = [],
        untilPicker = undefined,
        year = undefined,
        timeEpoc;
    if (e.viewMode == 0) {
        timeEpoc = 'day';
    } else if (e.viewMode == 1) {
        timeEpoc = 'month';
    } else if (e.viewMode == 2) {
        timeEpoc = 'year';
    } else if (e.viewMode == 3) {
        // Start From This(d) Decade
        allowedPicker = [dateRanges(d)];
        // Until This(default dateRanges) Decade
        untilPicker = dateRanges();
        year = 10;
        timeEpoc = 'decade';
    } else if (e.viewMode == 4) {
        // daterange second params set to 100 a century
        // Start From This(d) Century
        allowedPicker = [dateRanges(d, 100)];
        // Until This(now) Century
        untilPicker = dateRanges(new Date(), 100);
        year = 100;
        timeEpoc = 'century';
    }

    $('.datepicker.datepicker-dropdown table tbody tr [class!="' + timeEpoc + '"].focused').removeClass('focused');

    if (allowedPicker.length > 0) {
        if (allowedPicker.find(element => element == untilPicker) == undefined) {
            allowedPicker.push(untilPicker)
        }
        if (allowedPicker.length > 1 && allowedPicker[1] - allowedPicker[0] > year) {
            let loop = 1;
            do {
                if (allowedPicker.find(element => element == (loop * year)) == undefined) {
                    allowedPicker.push(allowedPicker[loop - 1] + year);
                }
                loop++;
            } while ((allowedPicker[1] - allowedPicker[0]) / year > loop);
            allowedPicker.sort();
        }
        allowedPicker.forEach(pickElement => {
            $('.datepicker.datepicker-dropdown table tbody td .disabled:contains(' + pickElement + ')').removeClass('disabled');
        });
    }
}).datepicker('setStartDate', d);

function doAbsoluteFirstAdd(table) {
    let theadThEl = table.querySelector('thead tr > th:first-of-type'),
        theadThFW = theadThEl.offsetWidth,
        tfootThEl = table.querySelector('tfoot tr > th:first-of-type'),
        tableHW = table.offsetWidth / 2;

    let tbodyTFW = 0;

    if (theadThFW > tableHW) {
        theadThFW = tableHW;
        tbodyTFW = tableHW;
    }

    if (tbodyTFW == 0) {
        table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-of-type').forEach(el => {
            if (tableHW <= el.offsetWidth) {
                tbodyTFW = tableHW;
                theadThFW = tableHW;
                return false;
            }
            if (el.offsetWidth > theadThFW) {
                theadThFW = el.offsetWidth;
            } else {
                tbodyTFW = theadThFW;
            }
            if (tbodyTFW < el.offsetWidth) {
                tbodyTFW = el.offsetWidth;
            }
        });
    }

    if (table.querySelector('tbody tr[data-zero="true"]') == null) {
        theadThEl.setAttribute('style', 'width: ' + theadThFW + 'px');
        theadThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem)');
        // theadThEl.parentElement.setAttribute('style', 'height: ' + theadThEl.offsetHeight + 'px');
        table.classList.add('table-responsive');
    }

    table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-of-type').forEach(el => {
        el.setAttribute('style', 'width:' + tbodyTFW + 'px');
        el.nextElementSibling.setAttribute('style', 'padding-left: calc(' + tbodyTFW + 'px + 1rem)');
        if (el.children[0] != null) {
            const computedStyle = getComputedStyle(el);
            let elementWidth = el.clientWidth;
            elementWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
            if (el.children[0].offsetWidth > elementWidth || elementWidth - el.children[0].offsetWidth <= 1) {
                el.parentElement.setAttribute('style', '');
                setTimeout(() => {
                    el.parentElement.setAttribute('style', 'height: ' + el.offsetHeight + 'px');
                }, 0)
            }
        } else if (el.children[0] == undefined) {
            const computedStyle = getComputedStyle(el);
            let elementWidth = el.clientWidth;
            elementWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
            if (el.offsetWidth > elementWidth || elementWidth - el.offsetWidth <= 1) {
                el.parentElement.setAttribute('style', '');
                setTimeout(() => {
                    el.setAttribute('style', 'height: ' + el.nextElementSibling.offsetHeight + 'px; width: '+ tbodyTFW +'px');
                }, 0)
            }
        }
    });

    if (tfootThEl != null) {
        if (table.querySelector('tbody tr[data-zero="true"]') == null) {
            tfootThEl.setAttribute('style', 'width: ' + theadThFW + 'px');
            tfootThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem)');
            // tfootThEl.parentElement.setAttribute('style', 'height: ' + tfootThEl.offsetHeight + 'px');
        }
    }

    if (!table.classList.contains('table-absolute-first')) {
        if (table.querySelector('tbody tr[data-zero="true"]') == null) {
            table.classList.add('table-absolute-first');
        }
    }
}

function doAbsoluteFirstRemove(table) {
    let theadThEl = table.querySelector('thead tr > th:first-of-type'),
        tfootThEl = table.querySelector('tfoot tr > th:first-of-type');

    theadThEl.removeAttribute('style');
    theadThEl.nextElementSibling.removeAttribute('style');
    theadThEl.parentElement.removeAttribute('style');

    table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-of-type').forEach(el => {
        el.removeAttribute('style');
        el.nextElementSibling.removeAttribute('style');
        el.parentElement.removeAttribute('style');
    });

    if (tfootThEl != null) {
        tfootThEl.removeAttribute('style');
        tfootThEl.nextElementSibling.removeAttribute('style');
        tfootThEl.parentElement.removeAttribute('style');
    }
}

const tableAbsoluteFirstList = document.querySelectorAll('table.table-absolute-first');
if (tableAbsoluteFirstList.length > 0) {
    tableAbsoluteFirstList.forEach(table => {
        if (table.classList.contains('table-responsive')) {
            doAbsoluteFirstAdd(table);
        }
    });
    let resizeTimeoutRab
    window.addEventListener('resize', function (e) {
        clearTimeout(resizeTimeoutRab)
        resizeTimeoutRab = setTimeout(() => {
            if (tableAbsoluteFirstList.length > 0) {
                tableAbsoluteFirstList.forEach(table => {
                    if (table.classList.contains('table-responsive')) {
                        doAbsoluteFirstAdd(table);
                    } else {
                        doAbsoluteFirstRemove(table);
                    }
                })
            }
        }, 50);
    });
}

function doScollRightStepper() {
    let elList = document.querySelectorAll('.c-stepper > .c-stepper__item');
    let wrapperWidth = 0;
    elList.forEach(element => {
        wrapperWidth += element.offsetWidth;
    });
    document.querySelector('.c-stepper').scrollLeft = wrapperWidth;
}

doScollRightStepper();