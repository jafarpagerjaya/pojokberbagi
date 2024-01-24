let selectCreate = function(target, name) {
    let multi = false;
    if (name == 'id_pelaksanaan') {
        label_name = 'Pelaksanaan';
    } else if (name == 'id_bantuan') {
        label_name = 'Informasi';
    } else if (name == 'id_pencairan') {
        label_name = 'Pencairan';
    } else if (name == 'id_penarikan') {
        label_name = 'Penarikan';
        multi = true;
    } else if (name == 'id_pengadaan') {
        label_name = 'Pengadaan';
    }

    const optionSelect = '<div class="col'+(multi == true ? '-12 mt-lg-4':'')+'"><div class="form-group position-relative mb-0"><span class="font-weight-bolder position-absolute '+ (multi == true ? 'mt-2':'mt-3') +'"><label for="'+ name +'" class="form-control-label m-0">'+ label_name +'</label></span><select class="custom-select" id="'+ name +'" name="'+ name +'"'+(multi == true ? 'multiple':'')+' required>'+(multi == true ? '':'<option value="0" disabled selected hidden>Pilih salah satu</option>')+'</select></div></div>';
    target.insertAdjacentHTML('beforeend', optionSelect);
    return name;
};

let formatSelected = function(objectSelected) {
    const label = objectSelected.element.closest('select').parentElement.querySelector('label');

    if (objectSelected.loading) {
        return objectSelected.text;
    }

    let $elSelected = '',
        circle = true;

    if (label != null) {
        $elSelected = label.outerHTML;
    }

    if ($elSelected.search(/id_penarikan/i) >= 0) {
        circle = false;
        return '<div class="font-weight-normal">' + objectSelected.text + '<span class="badge '+(circle == true ? 'badge-circle':'')+' badge-primary border-white badge-sm badge-floating font-weight-bold">' + objectSelected.additional_text + '</span></div>';
    }

    if (objectSelected.additional_text == null || objectSelected.additional_text == undefined || +objectSelected.additional_text === 0) {
        $elSelected = $elSelected + '<div class="font-weight-normal">' + objectSelected.text + '</div>';
    } else {
        $elSelected = $elSelected + '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-bold">' + objectSelected.text + '</span></div><div class="col-auto p-0 d-flex align-items-center"><span class="badge '+(circle == true ? 'badge-circle':'')+' badge-primary border-white badge-sm badge-floating font-weight-bold">' + objectSelected.additional_text + '</span></div></div>'
    }

    return $elSelected;
};

function formatResult(result) {
    if (result.loading) {
        return result.text;
    }
    let $result;
    if (result.additional_text == null || result.additional_text == undefined || +result.additional_text === 0) {
        $result = '<div class="font-weight-bolder">' + result.text + '</div>'
    } else {
        $result = '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-bold">' + result.text + '</span></div><div class="col-auto p-0 d-flex align-items-center"><span class="badge badge-circle badge-primary border-white badge-sm badge-floating font-weight-bold">' + result.additional_text + '</span></div></div>'
    }
    return $result;
};

function formatResult2(result) {
    if (result.loading) {
        return result.text;
    }
    let $result;
    if (result.additional_text == null || result.additional_text == undefined || +result.additional_text === 0) {
        $result = '<div class="d-flex flex-start align-items-center"><img style="width: 35px; height: auto;" class="m-0" src="'+ result.path_gambar +'" alt="'+ result.nama_gambar +'"><div class="font-weight-bolder">' + keteranganJenisChannelAccount(result.text) + '</div></div>'
    } else {
        $result = '<div class="row w-100 m-0 align-items-center"><div class="col px-0"><span class="font-weight-bold">' + result.text + '</span></div><div class="col-auto p-0 d-flex align-items-center"><span class="badge badge-primary border-white badge-sm badge-floating font-weight-bold">Rp. ' + result.additional_text + '</span></div></div>'
    }
    return $result;
};

function selectLabelOption(array) {
    return Object.values(array.reduce((accu, { id: id, group_by: text, nama, additional_text }) => {
        (accu[text] ??= { text, children: [] }).children.push({ id, text: nama, additional_text });
        return accu;
    }, {}));
};

function selectLabelOption2(array) {
    return Object.values(array.reduce((accu, { id: id, group_by: text, nama, additional_text, path_gambar, nama_gambar }) => {
        (accu[text] ??= { text, path_gambar, nama_gambar, children: [] }).children.push({ id, text: nama, additional_text });
        return accu;
    }, {}));
};

function setIdOnMultipleSelect(array) {
    return array.map(item => item['id']);
};

let objectInformasi = {},
    dataSelectOption = {};

$('#jenis-label').on('change', function(e) {
    if ($(this).val() == null) {
        e.preventDefault();
        return false;
    }

    if ($(this).val().toUpperCase() == 'PL') {
        if (objectInformasi.select_name != undefined && objectInformasi.select_name != 'id_pelaksanaan') {
            if ($('#'+ objectInformasi.select_name).length) {
                $('#'+ objectInformasi.select_name).select2('destroy');
                $('#'+ objectInformasi.select_name).closest('.col').remove();
            }
        }
        if (objectInformasi.select_name2 != undefined) {
            if ($('#'+ objectInformasi.select_name2).length) {
                $('#'+ objectInformasi.select_name2).select2('destroy');
                $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
            }
        }
        if (document.querySelector('#'+objectInformasi.select_name) == null) {
            objectInformasi.select_name = selectCreate(e.target.closest('.row'), 'id_pelaksanaan');
        }
        objectInformasi.select_url = '/admin/fetch/ajax/pelaksanaan/informasi';
    } else if ($(this).val().toUpperCase() == 'I') {
        if (objectInformasi.select_name != undefined && objectInformasi.select_name != 'id_bantuan') {
            if ($('#'+ objectInformasi.select_name).length) {
                $('#'+ objectInformasi.select_name).select2('destroy');
                $('#'+ objectInformasi.select_name).closest('.col').remove();
            }
        }
        if (objectInformasi.select_name2 != undefined) {
            if ($('#'+ objectInformasi.select_name2).length) {
                $('#'+ objectInformasi.select_name2).select2('destroy');
                $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
            }
        }
        if (document.querySelector('#'+objectInformasi.select_name) == null) {
            objectInformasi.select_name = selectCreate(e.target.closest('.row'), 'id_bantuan');
        }
        objectInformasi.select_url = '/admin/fetch/ajax/bantuan/informasi'
    } else if ($(this).val().toUpperCase() == 'PN') {
        if (objectInformasi.select_name != undefined && objectInformasi.select_name != 'id_pencairan') {
            if ($('#'+ objectInformasi.select_name).length) {
                $('#'+ objectInformasi.select_name).select2('destroy');
                $('#'+ objectInformasi.select_name).closest('.col').remove();
            }
        }
        if (document.querySelector('#'+objectInformasi.select_name) == null) {
            objectInformasi.select_name = selectCreate(e.target.closest('.row'), 'id_pencairan');
        }
        objectInformasi.select_url = '/admin/fetch/ajax/pencairan/informasi';
    } else if ($(this).val().toUpperCase() == 'PD') {
        if (objectInformasi.select_name != undefined && objectInformasi.select_name != 'id_pengadaan') {
            if ($('#'+ objectInformasi.select_name).length) {
                $('#'+ objectInformasi.select_name).select2('destroy');
                $('#'+ objectInformasi.select_name).closest('.col').remove();
            }
        }
        if (objectInformasi.select_name2 != undefined) {
            if ($('#'+ objectInformasi.select_name2).length) {
                $('#'+ objectInformasi.select_name2).select2('destroy');
                $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
            }
        }
        if (document.querySelector('#'+objectInformasi.select_name) == null) {
            objectInformasi.select_name = selectCreate(e.target.closest('.row'), 'id_pengadaan');
        }
        objectInformasi.select_url = '/admin/fetch/ajax/pengadaan/informasi';
    } else {
        return false;
    }

    $('#'+ objectInformasi.select_name).on('change', function(e) {
        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }

        if (this.parentElement.classList.contains('is-invalid')) {
            this.parentElement.classList.remove('is-invalid');
        }
        
        if (e.target.name == 'id_pencairan') {
            if (objectInformasi.select_name2 != undefined && objectInformasi.select_name2 != 'id_penarikan') {
                if ($('#'+ objectInformasi.select_name2).length) {
                    $('#'+ objectInformasi.select_name2).select2('destroy');
                    $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
                }
            }
            if (document.querySelector('#'+objectInformasi.select_name2) == null) {
                objectInformasi.select_name2 = selectCreate(e.target.closest('.row'), 'id_penarikan');
            }
            objectInformasi.select_url = '/admin/fetch/ajax/penarikan/informasi';
        }

        objectInformasi.select_value = this.value;

        if (objectInformasi.select_name2 != undefined || objectInformasi.select_name2 != null) {
            $('#'+ objectInformasi.select_name2).on('change', function(e) {
                objectInformasi.select_value2 = $(this).val();
                if (this.classList.contains('is-invalid')) {
                    this.classList.remove('is-invalid');
                }
        
                if (this.parentElement.classList.contains('is-invalid')) {
                    this.parentElement.classList.remove('is-invalid');
                }
            }).select2({
                placeholder: "Pilih minimal satu",
                templateSelection: formatSelected,
                templateResult: formatResult2,
                escapeMarkup: function (markup) { return markup; },
                ajax: {
                    url: objectInformasi.select_url,
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
                        
                        if (dataSelectOption.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataSelectOption.search))) {
                            params.offset = parseInt(dataSelectOption.offset) + parseInt(dataSelectOption.limit);
                        }
                        params.offset = params.offset || 0;
                        params.token = body.getAttribute('data-token');
                        if (objectInformasi.select_name2 == 'id_penarikan') {
                            Object.assign(params, {
                                'select_name': objectInformasi.select_name,
                                'select_value': objectInformasi.select_value,
                                'select_name2': objectInformasi.select_name2, 
                                'select_value2': objectInformasi.select_value2
                            });
                        }
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
                            createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
                            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                                'autohide': false
                            }).toast('show');
                            $('#id-kebutuhan').select2('close');
                            return false;
                        }
        
                        /* Data response Structure Wajib */
                        // id: elments.id,
                        // text: elments.text,
                        // group_by: elments.group_by,
                        // additional_text: elments.additional_text
        
                        let data = selectLabelOption2(response.feedback.data);
        
                        if (response.feedback.search != undefined) {
                            dataSelectOption.search = response.feedback.search;
                        } else {
                            delete dataSelectOption.search;
                        }
                        dataSelectOption.offset = response.feedback.offset;
                        dataSelectOption.record = response.feedback.record;
                        dataSelectOption.limit = response.feedback.limit;
                        dataSelectOption.load_more = response.feedback.load_more;
                        let pagination = {
                            more: dataSelectOption.load_more
                        };
                        return {results: data, pagination};
                    },
                    cache: true
                }
            }).on('select2:close', function(e) {
                dataSelectOption.offset = 0;
                dataSelectOption.load_more = undefined;
            });
        }
    }).select2({
        placeholder: "Pilih salah satu",
        templateSelection: formatSelected,
        templateResult: formatResult,
        escapeMarkup: function (markup) { return markup; },
        ajax: {
            url: objectInformasi.select_url,
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
                
                if (dataSelectOption.load_more && ((params.search == undefined) || (params.search != undefined && params.search == dataSelectOption.search))) {
                    params.offset = parseInt(dataSelectOption.offset) + parseInt(dataSelectOption.limit);
                }
                params.offset = params.offset || 0;
                params.token = body.getAttribute('data-token');
                if (objectInformasi.select_name == 'id_pelaksanaan' || objectInformasi.select_name == 'id_pencairan' || objectInformasi.select_name == 'id_pengadaan') {
                    Object.assign(params, {'select_name': objectInformasi.select_name});
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
                    $('#id-kebutuhan').select2('close');
                    return false;
                }

                /* Data response Structure Wajib */
                // id: elments.id,
                // text: elments.text,
                // group_by: elments.group_by,
                // additional_text: elments.additional_text

                let data = selectLabelOption(response.feedback.data);

                if (response.feedback.search != undefined) {
                    dataSelectOption.search = response.feedback.search;
                } else {
                    delete dataSelectOption.search;
                }
                dataSelectOption.offset = response.feedback.offset;
                dataSelectOption.record = response.feedback.record;
                dataSelectOption.limit = response.feedback.limit;
                dataSelectOption.load_more = response.feedback.load_more;
                let pagination = {
                    more: dataSelectOption.load_more
                };
                return {results: data, pagination};
            },
            cache: true
        }
    }).on('select2:select', function(e) {
        objectInformasi.select_text = e.params.data.text;
    }).on('select2:close', function(e) {
        dataSelectOption.offset = 0;
        dataSelectOption.load_more = undefined;
    });

    objectInformasi.label = this.value;
}).select2({
    placeholder: "Pilih salah satu",
    templateSelection: formatSelected,
    escapeMarkup: function (markup) { return markup; }
});

defaultOptions.placeholder = 'Isi deskripsi informasi';

let qEditor = editor('#editor', defaultOptions);

const modalForm = document.querySelector('#modalFormInformasi');
modalForm.addEventListener('click', function(e) {
    if (e.target.nodeName == 'BUTTON') {
        const input = this.querySelectorAll('input'),
              select = this.querySelectorAll('select');

        if (e.target.getAttribute('type') == 'clear') {
            input.forEach(el => {
                el.value = '';
                if (el.parentElement.classList.contains('is-invalid')) {
                    el.parentElement.classList.remove('is-invalid');
                    el.classList.remove('is-invalid');
                    el.parentElement.querySelector('label').removeAttribute('data-label-after');
                }
            });

            select.forEach(el => {        
                if (el.name == 'label') {
                    $('#' + el.getAttribute('id')).val(0).trigger('change');
                } else {
                    if ($('#'+ objectInformasi.select_name).length) {
                        $('#'+ objectInformasi.select_name).select2('destroy');
                        $('#'+ objectInformasi.select_name).closest('.col').remove();
                    }
                    if ($('#'+ objectInformasi.select_name2).length) {
                        $('#'+ objectInformasi.select_name2).select2('destroy');
                        $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
                    }
                }
            });

            qEditor.deleteText(0, qEditor.getLength());

            if (qEditor.root.closest('.is-invalid') != null) {
                qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
            }
        } else if (e.target.getAttribute('type') == 'reset') {
            input.forEach(el => {
                el.value = '';
                if (el.parentElement.classList.contains('is-invalid')) {
                    el.parentElement.classList.remove('is-invalid');
                    el.classList.remove('is-invalid');
                    el.parentElement.querySelector('label').removeAttribute('data-label-after');
                }
            });

            this.querySelector('input[name="judul"]').value = objectInformasi.data.judul;
            this.querySelector('.current-length').innerText = objectInformasi.data.judul.length;
            if (objectInformasi.data.isi != '') {
                qEditor.setContents(JSON.parse(objectInformasi.data.isi));
                if (qEditor.root.closest('.is-invalid') != null) {
                    qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
                }
            }
            // for select2
            setTimeout(() => {
                $('#jenis-label').val(objectInformasi.data.label).trigger('change');
                setTimeout(() => {
                    let defaultSelectChain = [objectInformasi.data.selected];
                    resetDataSelect2($('#'+ objectInformasi.select_name), defaultSelectChain); 
                    if (objectInformasi.data.selected_chain != undefined) {
                        $('#'+ objectInformasi.select_name).trigger('change');
                        let defaultSelectChain2 = objectInformasi.data.selected_chain;
                        resetDataSelect2($('#'+ objectInformasi.select_name2), defaultSelectChain2);
                        $('#'+ objectInformasi.select_name2).val(setIdOnMultipleSelect(defaultSelectChain2)).trigger('change');
                    }
                }, 0);
            }, 0);
        }
    }
});

let submitFunction = function(e) {
    let c_error = 0,
        nameList = e.target.closest('.modal').querySelectorAll('[name]'),
        Delta = qEditor.getContents(),
        root = e.target.closest('.modal');

    let fields = [];

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
                if (name.getAttribute('multiple') != null) {
                    fields[name.name] = objectInformasi.select_value2;
                } else {
                    fields[name.name] = name.value;
                }
                if (name.name != 'label') {
                    fields.select_text = objectInformasi.select_text;
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

    objectInformasi.fields = fields;
    objectInformasi.fields.isi = Delta;

    switch (objectInformasi.mode.toLowerCase()) {
        case 'create':
            
        break;

        case 'update':
            if (objectInformasi.data.isi == JSON.stringify(objectInformasi.fields.isi)) {
                delete objectInformasi.fields.isi;
            }
            if (objectInformasi.data.judul == objectInformasi.fields.judul) {
                delete objectInformasi.fields.judul;
            }
            if (objectInformasi.data.label == objectInformasi.fields.label) {
                delete objectInformasi.fields.label;
            }
            if (objectInformasi.data.selected.id == objectInformasi.fields[objectInformasi.select_name]) {
                delete objectInformasi.fields[objectInformasi.select_name];
                delete objectInformasi.fields.select_text;
            } else {
                objectInformasi.fields.select_name = objectInformasi.select_name;
            }
            if (objectInformasi.data.selected_chain != null) {
                
                let aData = setIdOnMultipleSelect(objectInformasi.data.selected_chain),
                    aEvent = objectInformasi.fields[objectInformasi.select_name2].reduce( (acc, x ) => acc.concat(+x), []);
                if (compareArrays(aData, aEvent)) {
                    delete objectInformasi.fields[objectInformasi.select_name2];
                } else {
                    let delete_ids = aData.filter(x => !aEvent.includes(x));
                    if (delete_ids.length) {
                        objectInformasi.fields.select_chain_delete_id = delete_ids;
                    }

                    let insert_ids = aEvent.filter(x => !aData.includes(x));
                    if (insert_ids.length) {
                        insert_ids = insert_ids.map(value => {
                            return { [objectInformasi.select_name2]: value }
                        });
                        objectInformasi.fields.select_chain_insert_id = insert_ids;
                    }
                    objectInformasi.fields.select_chain_name = objectInformasi.select_name2;
                    delete objectInformasi.fields[objectInformasi.select_name2];
                }
            }
            if (Object.keys(objectInformasi.fields).length < 1) {
                return false;
            }
            objectInformasi.fields.id_informasi = objectInformasi.id_informasi;
        break;

        default:
            let currentDate = new Date(),
                timestamp = currentDate.getTime(),
                invalid = {
                error: true,
                data_toast: 'invalid-show-modal-feedback',
                feedback: {
                    message: 'Informasi mode is unrecognize'
                }
            };

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                'delay': 10000
            }).toast('show');

            e.preventDefault();
            return false;
        break;
    }

    let data = {
        fields: Object.assign({}, objectInformasi.fields),
        token: body.getAttribute('data-token')
    };

    // console.log(data);
    // fetchCreateInformasi || fetchUpdateInformasi
    fetchData('/admin/fetch/'+ objectInformasi.mode +'/bantuan/informasi', data, root, objectInformasi.mode+'-informasi');
};

const submit = document.querySelector('button[type="submit"]');
submit.addEventListener('click', debounceIgnoreLast(submitFunction, 500));

let resetDataSelect2 = function(el, data) {
    el.html('');

    let dataAdapter = el.data('select2').dataAdapter;
    dataAdapter.addOptions(dataAdapter.convertToOptions(data));
};

let fetchGetInformasiBerita = function(result, root) {
    if (!result.error) {
        const data = result.feedback.data;
        objectInformasi.data = data;
        root.querySelector('#judul-berita').value = data.judul;
        root.querySelector('.current-length').innerText = data.judul.length;
        if (data.isi != '') {
            qEditor.setContents(JSON.parse(data.isi));
        }
        // for select2
        setTimeout(() => {
            $('#jenis-label').val(data.label).trigger('change');
            setTimeout(() => {
                let defaultSelectChain = [objectInformasi.data.selected];
                resetDataSelect2($('#'+ objectInformasi.select_name), defaultSelectChain);
                if (data.selected_chain != undefined) {
                    $('#'+ objectInformasi.select_name).trigger('change');
                    let defaultSelectChain2 = data.selected_chain;
                    resetDataSelect2($('#'+ objectInformasi.select_name2), defaultSelectChain2);
                    $('#'+ objectInformasi.select_name2).val(setIdOnMultipleSelect(defaultSelectChain2)).trigger('change');
                }
            }, 0);
        }, 0);
    }
};

let relatedCard;
$('#modalFormInformasi').on('hidden.bs.modal', function(e) {
    if (objectInformasi.mode == 'update') {
        if ($('#'+ objectInformasi.select_name).length) {
            $('#'+ objectInformasi.select_name).select2('destroy');
            $('#'+ objectInformasi.select_name).closest('.col').remove();
        }
        if ($('#'+ objectInformasi.select_name2).length) {
            $('#'+ objectInformasi.select_name2).select2('destroy');
            $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
        }
        
        const input = document.querySelectorAll('input');
        input.forEach(el => {
            el.value = '';
            if (el.parentElement.classList.contains('is-invalid')) {
                el.parentElement.classList.remove('is-invalid');
                el.classList.remove('is-invalid');
                el.parentElement.querySelector('label').removeAttribute('data-label-after');
            }
        });

        const select = document.querySelectorAll('select');

        select.forEach(el => {        
            if (el.name == 'label') {
                $('#' + el.getAttribute('id')).val(0).trigger('change');
            } else {
                if ($('#'+ objectInformasi.select_name).length) {
                    $('#'+ objectInformasi.select_name).select2('destroy');
                    $('#'+ objectInformasi.select_name).closest('.col').remove();
                }
                if ($('#'+ objectInformasi.select_name2).length) {
                    $('#'+ objectInformasi.select_name2).select2('destroy');
                    $('#'+ objectInformasi.select_name2).closest('.col-12').remove();
                }
            }
        });

        qEditor.deleteText(0, qEditor.getLength());

        if (qEditor.root.closest('.is-invalid') != null) {
            qEditor.root.closest('.is-invalid').classList.remove('is-invalid');
        }

        objectInformasi = {};
    }
})
.on('show.bs.modal', function(e) {
    relatedCard = e.relatedTarget.closest('.card');
    if (e.relatedTarget.getAttribute('data-type') == 'update') {
        let data = {
            'id_informasi': e.relatedTarget.closest('tr').getAttribute('data-id-informasi'),
            'token': body.getAttribute('data-token')
        };
        
        if (e.relatedTarget.closest('tr') == null || data.id_informasi == null || data.id_informasi == '') {
            let currentDate = new Date(),
                timestamp = currentDate.getTime(); 

            let invalid = {
                error: true,
                data_toast: 'invalid-show-modal-feedback',
                feedback: {
                    message: 'TR not found'
                }
            };

            if (data.id_informasi == null || data.id_informasi == '') {
                invalid.feedback.message = 'Data id informasi tidak ditemukan';
            }

            invalid.id = invalid.data_toast +'-'+ timestamp;
            
            createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
            $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                'delay': 10000
            }).toast('show');

            e.preventDefault();
            return false;
        }

        if (e.target.querySelector('button[type="clear"]') != null) {
            e.target.querySelector('button[type="clear"]').setAttribute('type','reset');
            e.target.querySelector('button[type="reset"]').innerText = 'Kembalikan';
        }

        objectInformasi.id_informasi = data.id_informasi;
        
        // fetchGetInformasiBerita
        fetchData('/admin/fetch/get/informasi', data, e.target, 'get-informasi');
        objectInformasi.mode = 'update';
    } else {
        if (e.target.querySelector('button[type="reset"]') != null) {
            e.target.querySelector('button[type="reset"]').setAttribute('type','clear');
            e.target.querySelector('button[type="clear"]').innerText = 'Kosongkan';
        }

        objectInformasi.mode = 'create';
    }
    e.target.querySelector('#modalFormInformasiLebel span').innerText = objectInformasi.mode;
});

let fetchCreateInformasi = function(response, modal) {
    if (!response.error) {
        if (relatedCard.querySelector('.pagination .page-link.page-item.active') != null) {
            relatedCard.querySelector('.pagination .page-link.page-item.active').click();
        } else {
            let el = response.feedback.data;
            relatedCard.querySelector('tbody').innerHTML = '';
            const tr = '<tr data-id-informasi="'+ el.id_informasi +'"><td><a href="#" target="_blank" rel="noopener noreferrer" class="font-weight-bolder"><span>'+ el.nama_bantuan +'</span></a></td><td><p class="font-weight-bold mb-0"><span>'+ el.judul +'</span></p><div class="row justify-content-between"><div class="col-auto"><span class="badge badge-primary font-weight-bolder">'+ el.label +'</span></div><div class="col-auto"><span><i class="far fa-clock small"></i></span><small><span data-modifiedt-value="'+ el.modified_at +'">'+ el.time_ago +'</span></small></div></div></td><td><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ el.jabatan_author +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ el.nama_author +'</span></div></div><div class="avatar rounded ml-3 bg-transparent border overflow-hidden" data-id-author="'+ el.id_author +'"><img src="'+ el.path_author +'" alt="'+ el.nama_author +'" class="img-fluid"></div></div></td><td><div class="media align-items-center"><div class="avatar rounded mr-3 bg-transparent border overflow-hidden" data-id-editor="'+ el.id_editor +'"><img src="'+ el.path_editor +'" alt="'+ el.nama_editor +'" class="img-fluid"></div><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ el.jabatan_editor +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ el.nama_editor +'</span></div></div></div></td><td><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light mr-0" href="javascript::void();" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Drop Down Action Record"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" data-value="'+ el.id_informasi +'"><a class="dropdown-item" href="#">Non-akifkan Berita</a><a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalFormInformasi" data-type="update">Ubah Berita</a></div></div></td></tr>';
            relatedCard.querySelector('tbody').insertAdjacentHTML('beforeend', tr);

            if (relatedCard.querySelector('tr[data-id-informasi="'+ response.feedback.data.id_informasi +'"') != null) {
                timeAgoRuns('table tr span[data-modified-value]','data-modified-value','data-modified-value-', 60000, 'tr','data-id-informasi');

                relatedCard.querySelector('tr[data-id-informasi="'+ response.feedback.data.id_informasi +'"').classList.add('highlight');
                setTimeout(() => {
                    relatedCard.querySelector('tr[data-id-informasi="'+ response.feedback.data.id_informasi +'"').classList.remove('highlight')
                }, 3000);
            }

            controlPaginationButton(0, $(relatedCard.querySelector('.pagination')), response.feedback.pages);
        }

        modal.querySelector('button[type="clear"]').click();
        objectInformasi = {};
        
        objectInformasi.id_informasi = response.feedback.data.id_informasi;
        objectInformasi.mode = 'create';
        $(modal).modal('hide');
    }
};

let fetchUpdateInformasi = function(response, modal) {
    if (!response.error) {
        
        const data = response.feedback.data;
        objectInformasi.id_informasi = data.id_informasi;
        $(modal).modal('hide');
        objectInformasi = {};

        const relatedTR = relatedCard.querySelector('tr[data-id-informasi="'+ data.id_informasi +'"');

        if (relatedTR != null) {
            stopPassed('data-modified-value');

            if (data.id_bantuan != null) {
                relatedTR.querySelector('a>span').innerText = data.nama_bantuan;
            }

            if (data.judul != null) {
                relatedTR.querySelector('p>span').innerText = data.judul;
            }

            if (data.label != null) {
                relatedTR.querySelector('.badge').innerText = data.label;
            }

            if (data.id_editor != null) {
                relatedTR.querySelector('[data-id-editor]').setAttribute('data-id-editor', data.id_editor);
                relatedTR.querySelector('[data-id-editor] img').setAttribute('src', data.path_editor);
                const media = '<div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ data.jabatan_editor +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ data.nama_editor +'</span></div></div>';
                relatedTR.querySelector('[data-id-editor]').insertAdjacentHTML('afterend', media);
            }

            relatedTR.querySelector('[data-modified-value]').setAttribute('data-modified-value', data.modified_at);

            timeAgoRuns('table tr span[data-modified-value]','data-modified-value','data-modified-value-', 60000, 'tr','data-id-informasi');

            relatedTR.classList.add('highlight');
            setTimeout(() => {
                relatedTR.classList.remove('highlight')
            }, 3000);
        }
    }
};

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

        switch(f) {
            case 'create-informasi':
                fetchCreateInformasi(response, root);
            break;

            case 'update-informasi':
                fetchUpdateInformasi(response, root);
            break;

            case 'read-list-informasi':
                if (!fetchReadInformasiList(response, root)) {
                    return false;
                }
            break;

            case 'get-informasi':
                fetchGetInformasiBerita(response, root);
            break;

            default:
                let currentDate = new Date(),
                    timestamp = currentDate.getTime(),
                    invalid = {
                        error: true,
                        data_toast: 'invalid-show-modal-feedback',
                        feedback: {
                            message: 'Unrecognize function-alias'
                        }
                    };

                invalid.id = invalid.data_toast +'-'+ timestamp;
                
                response.error = true;
                response.toast = invalid;
            break;
        }

        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
        if (response.error) {
            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                'delay': 10000
            });
        }
        
        if (response.feedback.message != null) {
            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast('show');
        }
    })
};

// fetchData('/admin/fetch/read/informasi', {token})
// timeAgoRuns('table tr span[data-modified-value]','data-modified-value','data-modified-value-', 60000, 'tr','data-id-informasi');

let jumlah_halaman = 0;

if (document.querySelector('.pagination[data-pages]') != null) {
    jumlah_halaman = document.querySelector('.pagination').getAttribute('data-pages');
}

let fetchReadInformasiList = function(result, root) {
    if (!result.error) {
        root.querySelector('tbody').innerHTML = '';

        let data = result.feedback.data;
        if (data.length > 0) {
            data.forEach(el => {
                el.id_informasi = reverseString(btoa(el.id_informasi));
                el.id_author = reverseString(btoa(el.id_author));
                el.id_editor = reverseString(btoa(el.id_editor));
                let tr = '<tr data-id-informasi="'+ el.id_informasi +'"><td><a href="#" target="_blank" rel="noopener noreferrer" class="font-weight-bolder"><span>'+ el.nama_bantuan +'</span></a></td><td><p class="font-weight-bold mb-0"><span>'+ el.judul +'</span></p><div class="row justify-content-between"><div class="col-auto"><span class="badge badge-primary font-weight-bolder">'+ el.label +'</span></div><div class="col-auto"><span><i class="far fa-clock small"></i></span><small><span data-modifiedt-value="'+ el.modified_at +'">'+ el.time_ago +'</span></small></div></div></td><td><div class="media align-items-center"><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ el.jabatan_author +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ el.nama_author +'</span></div></div><div class="avatar rounded ml-3 bg-transparent border overflow-hidden" data-id-author="'+ el.id_author +'"><img src="'+ el.path_author +'" alt="'+ el.nama_author +'" class="img-fluid"></div></div></td><td><div class="media align-items-center"><div class="avatar rounded mr-3 bg-transparent border overflow-hidden" data-id-editor="'+ el.id_editor +'"><img src="'+ el.path_editor +'" alt="'+ el.nama_editor +'" class="img-fluid"></div><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ el.jabatan_editor +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ el.nama_editor +'</span></div></div></div></td><td><div class="dropdown"><a class="btn btn-sm btn-icon-only text-light mr-0" href="javascript::void();" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Drop Down Action Record"><i class="fas fa-ellipsis-v"></i></a><div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow" data-value="'+ el.id_informasi +'"><a class="dropdown-item" href="#">Non-akifkan Berita</a><a class="dropdown-item" href="#" data-toggle="modal" data-target="#modalFormInformasi" data-type="update">Ubah Berita</a></div></div></td></tr>';
                root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
            });
        } else {
            if (result.feedback.search == null) {
                let tr = '<tr data-zero="true"><td colspan="5"><span>Belum ada deskripsi selengkapnya yang ditulis untuk campange ... </span></td></tr>'
                root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
                // eraseCookie('deskripsi-selengkapnya');
                root.classList.remove('load');
                return false;
            }
            let tr = '<tr data-zero="true"><td colspan="5"><span>Data pencarian tidak ditemukan ... </span></td></tr>';
            root.querySelector('tbody').insertAdjacentHTML('beforeend', tr);
        }

        const lInfo = root.querySelector('#lebel');
        if (lInfo != null) {
            lInfo.querySelector('#jumlah-data').innerText = data.length;
            lInfo.querySelector('#total-data').innerText = result.feedback.total_record;

            if (result.feedback.search != null) {
                if (lInfo.querySelector('#hasil-penelusuran') != null) {
                    lInfo.querySelector('#hasil-penelusuran span').innerText = result.feedback.total_record;
                } else {
                    lInfo.insertAdjacentHTML('beforeend', '<small id="hasil-penelusuran"><span class="text-orange">'+ result.feedback.total_record +'</span> pencarian ditemukan</small>');
                }
            } else {
                if (lInfo.querySelector('#hasil-penelusuran') != null) {
                    lInfo.querySelector('#hasil-penelusuran').remove();
                }
            }
        }

        root.classList.remove('load');

        if (objectInformasi.id_informasi != null && objectInformasi.mode == 'create') {
            if (relatedCard.querySelector('tr[data-id-informasi="'+ objectInformasi.id_informasi +'"') != null) {
                relatedCard.querySelector('tr[data-id-informasi="'+ objectInformasi.id_informasi +'"').classList.add('highlight');
                setTimeout(() => {
                    relatedCard.querySelector('tr[data-id-informasi="'+ objectInformasi.id_informasi +'"').classList.remove('highlight');
                    delete objectInformasi.id_informasi;
                }, 3000);
            }
        }

        if (result.feedback.pages != jumlah_halaman) {
            jumlah_halaman = result.feedback.pages;
            controlPaginationButton(2, $(relatedCard.querySelector('.pagination')), result.feedback.pages);
        }
        return false;
    }    
};

const limitListInformasi = document.querySelector('table#list-informasi tbody').getAttribute('data-limit');

let clickPageLink = function(e) {
    const root = e.target.closest('.card');
    let active_page = e.target.getAttribute('data-id'),
        token = document.querySelector('body').getAttribute('data-token');
            
    let data = {
        'token': token,
        'fields': {
            'active_page': active_page,
            'limit': limitListInformasi
        }
    };

    const search = root.querySelector('input[name="search"]');

    if (search.value.length > 0) {
        data.fields.search = search.value;
    }

    // console.log(data);
    // fetchReadInformasiList
    fetchData('/admin/fetch/read/informasi', data, root, 'read-list-informasi');
    e.preventDefault();
};

// Satu Paket Paging and Search
let oldPage,
    delayTimer,
    oldSearchValue;

let clickPagination = function(e) {
    relatedCard = e.target.closest('.card');
    relatedCard.classList.add('load');
    if (oldPage == e.target.getAttribute('data-id')) {
        if (!delayTimer) {
            clickPageLink(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
            if (relatedCard.classList.contains('load')) {
                relatedCard.classList.remove('load');
            }
        }, 1000);
    } else {
        clickPageLink(e);
    }
    oldPage = e.target.getAttribute('data-id');
    console.log('click');
};

$('.pagination').on('click', '.page-link:not(.disabled)[data-id!="?"]:not(.prev):not(.next)', clickPagination);

let searchFunction = function(e) {
    let sValue = '';
    if (e.target.value.length > 0) {
        sValue = e.target.value;
        if (isNumber(sValue)) {
            e.target.value = numberToPrice(e.target.value);
            sValue = e.target.value;
        }
    }

    if (oldSearchValue == sValue || oldSearchValue == undefined && sValue == '') {
        clearTimeout(delayTimer);
        e.target.closest('.card').classList.remove('load');
        return false;
    }
    
    if (oldSearchValue != undefined || e.target.value.length > 0) {
        e.target.closest('.card').classList.add('load');
        relatedCard = e.target.closest('.card');
    }

    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const root = e.target.closest('.card');

        let data = {
            'token': body.getAttribute('data-token'),
            'fields': {
                'limit': limitListInformasi
            }
        };

        if (root.querySelector('.pagination .page-link.active') != null) {
            data.fields.active_page = root.querySelector('.pagination .page-link.active').getAttribute('data-id');
        }        

        if (e.target.value.length > 0) {
            data.fields.search = sValue;
        }

        if (!sValue.length && root.querySelector('#hasil-penelusuran') != null) {
            root.querySelector('#hasil-penelusuran').remove();
        }
        
        if (oldSearchValue != data.fields.search) {
            oldSearchValue = data.fields.search;
        } else {
            root.classList.remove('load');
            return false;
        }
        
        // console.log(data);
        // fetchReadInformasiList
        fetchData('/admin/fetch/read/informasi', data, root, 'read-list-informasi');
    }, 1000);
};

document.querySelectorAll('input[name="search"]').forEach(input => {
    input.addEventListener('keyup', searchFunction);
});