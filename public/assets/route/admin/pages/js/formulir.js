function findIndex(node) {
    let i = 1;
    while ((node = node.previousSibling) != null) {
        if (node.nodeType === 1) i++;
    }
    return i;
}

const tabContentList = document.querySelectorAll('form.tab-content');

tabContentList.forEach(tab => {
    let buttonList = tab.querySelectorAll('.tab-pane [type="button"]');
    buttonList.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!e.target.closest('.tab-pane').classList.contains('active')) {
                return false;
            }

            if (e.target.getAttribute('data-target') == null) {
                return false;
            }

            let tabPane = e.target.closest('.tab-pane'),
                tabRequiredError = 0,
                tabIndex = findIndex(tabPane),
                target = e.target.getAttribute('data-target'),
                targetTab = document.getElementById(target).getAttribute('href'),
                targetTabIndex = findIndex(tab.querySelector(targetTab));

            if (targetTabIndex > tabIndex) {
                tabPane.querySelectorAll('[data-required="true"]').forEach(elRequred => {
                    let formGroup = elRequred.closest('.form-group');
                    if (elRequred.value.length == 0) {
                        tabRequiredError++;
                        formGroup.classList.add('error');
                        elRequred.classList.add('is-invalid');
                        formGroup.querySelector('label').setAttribute('data-label-after', 'wajib diisi');
                        return true;
                    }

                    let minlength = elRequred.getAttribute('minlength');
                    if (minlength != null && elRequred.value.length < minlength) {
                        tabRequiredError++;
                        formGroup.classList.add('error');
                        elRequred.classList.add('is-invalid');
                        formGroup.querySelector('label').setAttribute('data-label-after', 'minimal '+minlength+' karakter.');
                        return true;
                    }

                    if (formGroup.classList.contains('error') && elRequred.value.length != 0) {
                        formGroup.classList.remove('error');
                        elRequred.classList.remove('is-invalid');
                        formGroup.querySelector('label').removeAttribute('data-label-after');
                        return true;
                    }

                    if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
                        tabRequiredError++;
                        return true;
                    }
                });
            }
            
            if (tabRequiredError != 0) {
                return false;
            }

            $('#'+target).removeClass('disabled').tab('show');

            setStepTranslateX(document.getElementById(target))
        });
    })
});

const tabList = document.querySelectorAll('.steps[role="tablist"]');

let tabRequiredError = 0;
tabList.forEach(tab => {
    let aList = tab.querySelectorAll('a[role="tab"]');
    aList.forEach(a => {
        a.addEventListener('click', function() {
            let targetIndex = findIndex(this),
                targetLoop = targetIndex - 1;

            if (targetLoop <= 0) {
                return false;
            }

            const loopList = Array.from(this.parentElement.children).slice(0, targetLoop);
            tabRequiredError = 0;
            loopList.forEach(elAStep => {
                const tab = document.querySelector(elAStep.getAttribute('href'));
                tab.querySelectorAll('[data-required="true"]').forEach(elRequred => {
                    let formGroup = elRequred.closest('.form-group');
                    if (elRequred.value.length == 0) {
                        tabRequiredError++;
                        return false;
                    }

                    let minlength = elRequred.getAttribute('minlength');
                    if (minlength != null && minlength > elRequred.value.length) {
                        tabRequiredError++;
                        return false;
                    }

                    if (formGroup.classList.contains('error') && elRequred.value.length != 0) {
                        tabRequiredError++;
                        return false;
                    }

                    if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
                        tabRequiredError++;
                        return false;
                    }
                });
            });
        });
    });
});

$('[data-toggle="pill"]').on('hide.bs.tab', function (e) {
    // e.relatedTarget // newly activated tab
    // e.target // previous active tab
    let tabRequiredError = 0,
        target = e.target.getAttribute('href'),
        related = e.relatedTarget.getAttribute('href'),
        lastTabIndex = findIndex(document.querySelector(target)),
        nextTabIndex = findIndex(document.querySelector(related));
    
        document.querySelectorAll(target+' [data-required="true"]').forEach(elRequred => {
            let formGroup = elRequred.closest('.form-group');

            if (elRequred.value.length == 0) {
                tabRequiredError++;
                return false;
            }

            let minlength = elRequred.getAttribute('minlength');
            if (minlength != null && minlength > elRequred.value.length) {
                tabRequiredError++;
                return false;
            }

            if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
                tabRequiredError++;
                return false;
            }
        });

    if (tabRequiredError == 0) {
        if (nextTabIndex - lastTabIndex > 1) {
            
            tabContentList.forEach(tab => {
                let arrayTab = Array.from(tab.querySelectorAll('.tab-pane')).slice(lastTabIndex, nextTabIndex-1);

                arrayTab.forEach(elTab => {
                    elTab.querySelectorAll('[data-required="true"]').forEach(elRequred => {
                        let formGroup = elRequred.closest('.form-group');
                        if (elRequred.value.length == 0) {
                            tabRequiredError++;
                            return false;
                        }

                        let minlength = elRequred.getAttribute('minlength');
                        if (minlength != null && minlength > elRequred.value.length) {
                            tabRequiredError++;
                            return false;
                        }

                        if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
                            tabRequiredError++;
                            return false;
                        }
                    });
                });
            });

            if (tabRequiredError != 0) {
                return false;
            }
            
        }
        e.target.classList.remove('disabled');
    } else {
        if (nextTabIndex > lastTabIndex) {
            return false;
        }
    }
});

let stepList = document.querySelectorAll('.steps > .step'),
    stepActive = document.querySelector('.steps>.step.active');

function setStepTranslateX(thisElment) {
    const index = Array.prototype.indexOf.call(thisElment.parentNode.children, thisElment),
            parentWidth= thisElment.parentElement.clientWidth,
            react = thisElment.getBoundingClientRect();

    if (parseInt(index+1)*react.width <= parentWidth) {
        if (!thisElment.parentElement.classList.contains('translateX')) {
            return false;
        }
        thisElment.parentElement.classList.remove('translateX');
    } else {
        if (thisElment.parentElement.classList.contains('translateX')) {
            return false;
        }
        thisElment.parentElement.classList.add('translateX');
    }
};

setStepTranslateX(stepActive);

stepList.forEach(el => {
    el.addEventListener('click', function() {
        if (tabRequiredError) {
            return false;
        }

        const index = Array.prototype.indexOf.call(this.parentNode.children, this),
              parentWidth= this.parentElement.clientWidth,
              react = this.getBoundingClientRect();

        if (parseInt(index+1)*react.width <= parentWidth) {
            if (!this.parentElement.classList.contains('translateX')) {
                return false;
            }
            this.parentElement.classList.remove('translateX');
        } else {
            if (this.parentElement.classList.contains('translateX')) {
                return false;
            }
            this.parentElement.classList.add('translateX');
        }
    });
});

const fileList = document.querySelectorAll('.inputGroup input[type="file"]'),
    fileMaxSizeMb = 2;

let src,
    targetName,
    targetFileName;

fileList.forEach(file => {
    file.addEventListener('change', (e) => {
        // Get the file
        const [file] = e.target.files,
            {name: fileName, size} = file;
        // Open Modal
        const modal = e.target.closest('[data-target]').getAttribute('data-target');

        src = URL.createObjectURL(file);
        targetName = e.target.getAttribute('name');
        targetFileName = fileName;

        $(modal).modal('show');

        // convert size in byte to kilo byte
        const fileSize = (size / 1048 / 1048).toFixed(2),
            label = e.target.parentNode.querySelector('label'),
            toastId = e.target.getAttribute('id');
        // Set file name and file size to label
        label.querySelector('span.name').innerHTML = fileName;
        label.querySelector('span.size').innerHTML = fileSize+' MB';

        let formGroup = e.target.closest('.form-group');
        
        doToastForFile(formGroup, e.target, toastId, fileName, fileSize);
    });

    file.addEventListener('click', function (e) {
        let nCanvas = this.nextElementSibling.querySelector('span.rule').innerText,
            nInput = this.getAttribute('name');
        
        document.querySelectorAll('#imgCanvasCropper [data-dismiss]').forEach(el => {
            el.setAttribute('data-dismiss-for', nInput);
        });
        if (nInput == 'wide_img') {
            nInput = 'wide screen';
            aspectR = '468 / 139';
        } else {
            nInput = 'small screen';
            aspectR = '16 / 9';
        }
        document.querySelector('#imgCanvasCropperLabel .text-orange').innerHTML = nCanvas + ' (' + nInput + ')';
    });
});

// Cropper
let cropper,
    cropData = {},
    cropedFile = {};
    
$('#imgCanvasCropper').on('show.bs.modal', function () {
    this.querySelector('.modal-body img').src = src;

    if (targetName == 'wide_img') {
        cropData.aspectRatio = 468 / 139;
        cropData.width = 1296;
        cropData.height = 386;
    } else {
        cropData.aspectRatio = 16 / 9;
        cropData.width = 696;
        cropData.height = (cropData.width / 16) * 9;
    }
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
    this.querySelector('.modal-body img').setAttribute('src', '');

    this.querySelectorAll('[data-dismiss]').forEach(el => {
        el.removeAttribute('data-dismiss-for');
    });

    cropper.destroy();

    if (cropedFile[targetName] != undefined) {
        nameNew[targetName] = cropedFile[targetName].data;
        let input = document.querySelector('[name="'+targetName+'"]'),
            label = input.parentNode.querySelector('label'),
            formGroup = input.closest('.form-group'),
            toastId = input.getAttribute('id');

        label.querySelector('.result .size').innerText = cropedFile[targetName].size + ' MB';
        label.querySelector('.result .name').innerText = cropedFile[targetName].name;

        doToastForFile(formGroup, input, toastId, cropedFile[targetName].name, cropedFile[targetName].size);
    }
});

let doToastForFile = function (formGroup, input, toastId, fileName, fileSize) {
    let rule = formGroup.querySelector('.rule').innerHTML;
    if (fileMaxSizeMb < fileSize) {
        if (!formGroup.classList.contains('error')) {
            formGroup.classList.add('error');
        }

        input.parentElement.setAttribute('data-file-passed', false);
        input.setAttribute('title', 'Ukuran file terlalu besar');
        toastElTime = document.querySelector('.toast[data-toast="'+toastId+'"] .toast-header .time-passed');

        toastPassed(toastElTime);
        
        $('[data-toast="'+toastId+'"]').toast('show').on('shown.bs.toast', function () {
            $(this).find('.toast-body').text('Ukuran file tidak boleh melebihi '+fileMaxSizeMb+' MB. '+rule);
        });
    } else {
        if (formGroup.classList.contains('error')) {
            formGroup.classList.remove('error');
        }

        input.parentElement.setAttribute('data-file-passed', true);
        input.setAttribute('title', 'Ganti gambar '+fileName+'?');

        if (!$('[data-toast="'+toastId+'"]').hasClass('show')) {
            return false;
        }

        $('[data-toast="'+toastId+'"]').toast('hide');
    }
};

$('#imgCanvasCropper').on('click', '.modal-footer [type="button"]', function () {
    const imgReturnType = "image/jpeg";
    cropCanvas = cropper.getCroppedCanvas({
        width: cropData.width,
        height: cropData.height,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff',
    }).toDataURL(imgReturnType);

    const head = 'data:'+imgReturnType+';base64,',
        fileSize = (cropCanvas.length - head.length);
    let fileSizeInKB = Math.round((fileSize/1000).toFixed(2)),
        fileSizeInMB = (fileSizeInKB/1000).toFixed(2);

    cropedFile[targetName] = {
        'data' : cropCanvas,
        'size' : fileSizeInMB,
        'name' : targetFileName
    };

    $(this).parents('.modal').modal('hide');
}).on('click', '[data-dismiss]', function () {
    const inputFileName = this.getAttribute('data-dismiss-for'),
        eTarget = document.querySelector('[name="' + inputFileName +'"]');

    // End if this input file haven't ben crop
    if (cropedFile[inputFileName] != undefined) {
        return;
    }

    eTarget.value = '';
    eTarget.closest('[data-file-passed]').querySelector('label .result span.name').innerText = '';
    eTarget.closest('[data-file-passed]').querySelector('label .result span.size').innerText = '';
    eTarget.closest('[data-file-passed]').removeAttribute('data-file-passed');

    delete nameNew[inputFileName];
});

$('.toast').on('hidden.bs.toast', function () {
    const toastId = $(this).data('toast');
    stopPassed(toastId);
    $(this).find('.toast-header .time-passed').text('');
    $(this).find('.toast-body').text('');
});

let timeIntervalList = {};

function toastPassed(element) {
    const startTime = new Date();
    let dataToast;

    element.innerHTML = 'Beberapa saat yang lalu';

    dataToast = element.closest('.toast[data-toast]').getAttribute('data-toast');
    
    let timeInterval = setInterval(() => {
        element.innerHTML = timePassed(startTime);
        console.log(timePassed(startTime));
    }, 60000);

    timeIntervalList[dataToast] = timeInterval;
};

function stopPassed(dataToast) {
    clearInterval(timeIntervalList[dataToast]);
    delete timeIntervalList[dataToast];
}

$('.toast').toast({
    'autohide': false
});

let jumlahTarget = document.getElementById('input-jumlah-target'),
    inputSatuan = document.getElementById('input-satuan-target');

// restrict number only
jumlahTarget.addEventListener('keypress', preventNonNumbersInInput);

jumlahTarget.addEventListener('keyup', function () {
    jumlahTarget.value = numberToPrice(this.value);
});

jumlahTarget.addEventListener('change', function () {
    if (this.hasAttribute('data-min')) {
        minvalue = this.getAttribute('data-min');
        if (priceToNumber(this.value) < minvalue && this.value.length) {
            this.value = minvalue;
        }
        if (this.value == 0) {
            this.value = '';
        }
    }
    if (this.value.length == 0) {
        inputSatuan.closest('.form-group').parentElement.classList.add('d-none');
        inputSatuan.value = '';
        inputSatuan.removeAttribute('data-required');
        delete nameNew[inputSatuan.getAttribute('name')];
    } else {
        inputSatuan.closest('.form-group').parentElement.classList.remove('d-none');
        inputSatuan.setAttribute('data-required', true);
    }
    jumlahTarget.value = numberToPrice(this.value);
});

let textarea = document.querySelector(".textarea"); 
textarea.addEventListener('input', autoResize, false); 

let charLeft = document.getElementById('charLeft');
textarea.addEventListener('keyup', function () {
    charLeft.innerHTML = ('<span class="text-orange">'+ this.value.length +'</span>' + '/' +255);
});

let minDonasi = document.getElementById('input-min-donasi');
// restrict number only
minDonasi.addEventListener('keypress', preventNonNumbersInInput);

minDonasi.addEventListener('keyup', function () {
    if (this.value < 1 && this.value.length > 0) {
        this.value = 1;
    }
    minDonasi.value = numberToPrice(this.value, 'Rp. ');
});

minDonasi.addEventListener('change', function (e) {
    if (priceToNumber(e.target.value) < 1000 && this.value.length > 0) {
        this.value = 1000;
    }
    minDonasi.value = numberToPrice(this.value, 'Rp. ');
});

let nominalRab = document.getElementById('input-rab');
// restrict number only
nominalRab.addEventListener('keypress', preventNonNumbersInInput);

nominalRab.addEventListener('keyup', function () {
    nominalRab.value = numberToPrice(this.value, 'Rp. ');
});

function handleMask(event, mask) {
    with (event) {
        stopPropagation()
        preventDefault()
        if (!charCode) return
        var c = String.fromCharCode(charCode)
        if (c.match(/\D/)) return
        with (target) {
            var val = value.substring(0, selectionStart) + c + value.substr(selectionEnd)
            var pos = selectionStart + 1
        }
    }
    var nan = count(val, /\D/, pos) 
    val = val.replace(/\D/g,'')

    var mask = mask.match(/^(\D*)(.+9)(\D*)$/)
    if (!mask) return 
    if (val.length > count(mask[2], /9/)) return

    for (var txt='', im=0, iv=0; im<mask[2].length && iv<val.length; im+=1) {
        var c = mask[2].charAt(im)
        txt += c.match(/\D/) ? c : val.charAt(iv++)
    }

    with (event.target) {
        value = mask[1] + txt + mask[3]
        selectionStart = selectionEnd = pos + (pos==1 ? mask[1].length : count(value, /\D/, pos) - nan)
    }

    function count(str, c, e) {
        e = e || str.length
        for (var n=0, i=0; i<e; i+=1) if (str.charAt(i).match(c)) n+=1
        return n
    }
}

let lamaDonasi = document.getElementById('input-lama-penayangan');
const maxLamaPenayangan = 180;
lamaDonasi.addEventListener('keypress', function (e) {
    let selfValue = parseInt(this.value);

    if (selfValue < 1) {
        this.value = '';
    }

    if (this.getAttribute('data-value') == maxLamaPenayangan) {
        e.preventDefault();
        return false;
    }

    handleMask(e, '9999 hari');

    setTimeout(() => {
        if (parseInt(e.target.value) > maxLamaPenayangan) {
            this.value = maxLamaPenayangan + ' hari';
            e.target.selectionEnd = maxLamaPenayangan.toString().length;
        }
    }, 0);
});

lamaDonasi.addEventListener('keyup', function () {
    let selfValue = parseInt(this.value);
    if (selfValue < 1 || isNaN(selfValue)) {
        this.value = '';
    }
    this.setAttribute('data-value', selfValue);
});

lamaDonasi.addEventListener('keydown', function (e) {
    let selfValue = parseInt(this.value),
        ceret = e.target.selectionStart;

    // console.log(ceret, selfValue, e.key);

    if (ceret == 1 && isNaN(parseInt(e.key)) && selfValue < 10 && (e.key != 'ArrowUp' && e.key !=
            'Backspace' && e.key != 'ArrowLeft')) {
        e.preventDefault();
    }

    if (ceret == 2 && isNaN(parseInt(e.key)) && selfValue > 9 && selfValue < 100 && (e.key ==
            'ArrowRight' || e.key == 'Delete')) {
        e.preventDefault();
    }

    if (ceret == 3 && isNaN(parseInt(e.key)) && selfValue > 99 && selfValue < 1000 && (e.key !=
            'ArrowUp' && e.key != 'Backspace')) {
        e.preventDefault();
    }

    setTimeout(() => {
        if (e.key == 'ArrowDown') {
            if (selfValue < 10) {
                e.target.selectionStart = 1;
                e.target.selectionEnd = 1;
            } else if (selfValue > 9 && selfValue < 100) {
                e.target.selectionStart = 2;
                e.target.selectionEnd = 2;
            } else if (selfValue > 99 && selfValue < 1000) {
                e.target.selectionStart = 3;
                e.target.selectionEnd = 3;
            }
        }
    }, 0);
});

lamaDonasi.addEventListener('click', function (e) {
    let selfValue = parseInt(this.value);
    e.target.selectionEnd = selfValue.toString().length;
});

lamaDonasi.addEventListener('paste', function (e) {
    if (parseInt(e.target.value) > maxLamaPenayangan) {
        this.value = maxLamaPenayangan;
    }

    setTimeout(() => {
        let selfValue = parseInt(e.target.value);
        if (selfValue > maxLamaPenayangan) {
            selfValue = maxLamaPenayangan;
        }
        if (selfValue.toString().length && !isNaN(selfValue)) {
            this.value = selfValue + ' hari';
        } else {
            this.value = '';
        }
        if (selfValue < 10) {
            e.target.selectionStart = 1;
            e.target.selectionEnd = 1;
        } else if (selfValue > 9 && selfValue < 100) {
            e.target.selectionStart = 2;
            e.target.selectionEnd = 2;
        } else if (selfValue > 99 && selfValue < 1000) {
            e.target.selectionStart = 3;
            e.target.selectionEnd = 3;
        }
    }, 0);
});

$('.input-group-prepend').click(function () {
    $(this).siblings('textarea').focus();
});

$('select').on('change', function() {
    submitControl(this);
    if (this.classList.contains('is-invalid')) {
        this.classList.remove('is-invalid');
    }
    if (this.closest('.form-group').classList.contains('error')) {
        this.closest('.form-group').classList.remove('error');
    }
});

$('select').select2({
    placeholder: "Pilih salah satu"
});

$('.select2').click(function() {
    if (window.outerWidth <= 768) {
        setTimeout(() => {
            let spanSelect2 = document.querySelector('span.select2-container--open span.select2-dropdown');
            if (spanSelect2 == null) {
                return false;
            }
            let selectW = (parseFloat(getComputedStyle(spanSelect2).width) + 0.02).toFixed(2);
            spanSelect2.style.width = selectW+'px';
        }, 0);
    }
});

$('select').on("select2:open", function (e) { 
    let formGroup = e.currentTarget.closest('.form-group'),
        targetId = 'select2-'+e.currentTarget.getAttribute('id')+'-results';
    const target = document.getElementById(targetId);
    if (formGroup.classList.contains('error')) {
        target.closest('span.select2-dropdown').classList.add('error');
    } else {
        target.closest('span.select2-dropdown').classList.remove('error');
    }
});

const names = document.querySelectorAll('form [name]'),
    submit = document.querySelector('[type="submit"]');
let nameOld = {},
    nameNew = {},
    errorRequired = {};

let submitControl = function (self) {
    self.value = escapeRegExp(self.value);
    let min = self.getAttribute('data-min');

    if (self.value != nameOld[self.name] && !min || self.value != nameOld[self.name] && min && self.value.length) {
        nameNew[self.name] = self.value;
    } else {
        delete nameNew[self.name];
    }

    const form = self.closest('form');
    tabRequiredError = 0;
    form.querySelectorAll('[data-required="true"]').forEach(elRequred => {
        let formGroup = elRequred.closest('.form-group');
        if (elRequred.value.length == 0) {
            tabRequiredError++;
            return true;
        }

        if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
            tabRequiredError++;
            return true;
        }
    });

    errorRequired.count = tabRequiredError;

    if (Object.keys(nameNew).length > 0 && errorRequired.count == 0) {
        submit.classList.remove('disabled');
    } else {
        submit.classList.add('disabled');
    }
};
names.forEach(element => {
    nameOld[element.name] = element.value;
    element.addEventListener('keypress', function (e) {
        if (e.key == '<' || e.key == '>' || e.key == '{' || e.key == '}') {
            e.preventDefault();
        }
    });

    element.addEventListener('change', function () {
        submitControl(this);
    });

    element.addEventListener('keyup', function () {
        submitControl(this);
        let minlength = this.getAttribute('minlength');
        if (minlength != null && this.value.length < minlength) {
            return true;
        }

        if (this.classList.contains('is-invalid')) {
            this.classList.remove('is-invalid');
        }

        let formGroup = this.closest('.form-group');
        if (formGroup.classList.contains('error')) {
            formGroup.classList.remove('error');
        }
        if (formGroup.classList.contains('is-invalid')) {
            formGroup.classList.remove('is-invalid');
        }

        if (formGroup.querySelector('label[data-label-after]') != null) {
            formGroup.querySelector('label[data-label-after]').removeAttribute('data-label-after');
        }
    });

    element.addEventListener('paste', function () {
        setTimeout(() => {
            submitControl(this);
        }, 0);
    });
});

let action = window.location.pathname,
    fetchUrl;

if (action.split('/').at(-1) == 'formulir') {
    fetchUrl = '/admin/fetch/create/bantuan';
}
    
submit.addEventListener('click', function (e) {
    if (Object.keys(errorRequired).length) {
        e.preventDefault();
        return false;
    }

    if (!Object.keys(nameNew).length) {
        e.preventDefault();
        return false;
    }

    nameNew.token = document.querySelector('body').getAttribute('data-token');
    
    let message = undefined;
    if (nameNew.token == null) {
        message = 'Fetch being abort -> fetch token is null';
        console.log(message);
        alert(message);
        return false;
    }
    
    // Fetch with token
    fetch(fetchUrl, {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body: JSON.stringify(nameNew)
    })
    .then(response => response.json())
    .then(function(data) {
        // console.log(data);
        if (data.error == false) {
            // Success
            $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-danger').addClass('bg-success');
            $('.toast[data-toast="feedback"] .toast-header strong').text('Pemberitahuan');
        } else {
            // Failed
            $('.toast[data-toast="feedback"] .toast-header .small-box').removeClass('bg-success').addClass('bg-danger');
            $('.toast[data-toast="feedback"] .toast-header strong').text('Peringatan!');
            console.log('there is some error in server side');
        }

        $('.toast[data-toast="feedback"] .time-passed').text('Baru Saja');
        $('.toast[data-toast="feedback"] .toast-body').html(data.feedback.message);

        document.querySelector('body').setAttribute('data-token', data.token);
        nameNew.token = data.token;
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
        
        if (data.error == false) {
            deleteProperties(nameNew);
            let redirectTo = '/admin/bantuan'
            if (action.split('/').at(-1) != 'formulir') {
                redirectTo = redirectTo+'/halaman/'+data.feedback.halaman+'#'+data.feedback.id_bantuan;
            }
            window.location.href = redirectTo;
        }
    });
    
    e.preventDefault();
    deleteProperties(errorRequired);
});

submit.addEventListener('mouseenter', function () {
    // console.log(nameNew);

    const form = this.closest('form');
    let tabRequiredError = 0;
    form.querySelectorAll('[data-required="true"]').forEach(elRequred => {
        let formGroup = elRequred.closest('.form-group');
        if (elRequred.value.length == 0) {
            tabRequiredError++;
            if (!formGroup.classList.contains('error')) {
                formGroup.classList.add('error');
            }
            return true;
        }

        if (formGroup.classList.contains('warning') || formGroup.classList.contains('error')) {
            tabRequiredError++;
            return true;
        }
    });

    if (tabRequiredError != 0) {
        this.classList.add('disabled');
        errorRequired.count = tabRequiredError;
        return false;
    }

    if (!Object.keys(nameNew).length) {
        this.classList.add('disabled');
        return false;
    }

    if (fetchUrl == '/admin/fetch/update/bantuan') {
        if (Object.keys(nameNew).length < 2) {
            this.classList.add('disabled');
            return false;
        }
    }

    if (this.classList.contains('disabled')) {
        this.classList.remove('disabled');
    }

    delete errorRequired.count;
});

function deleteProperties(objectToClean) {
    for (var x in objectToClean) if (objectToClean.hasOwnProperty(x)) delete objectToClean[x];
}

$('[type="reset"]').click(function (e) {
    deleteProperties(nameNew);
    // deleteProperties(errorRequired);
    submit.classList.add('disabled');
    e.target.closest('form').querySelectorAll('.is-invalid').forEach(elInvalid => {
        elInvalid.classList.remove('is-invalid');
        let formGroup = elInvalid.closest('.form-group.error');
            formGroup.classList.remove('error');
            formGroup.querySelector('label').removeAttribute('data-label-after');
    });
    $("select").each(function(){
        if ($(this).data('selected-value') == null) {
            $(this).val('').trigger('change');
        }
    });
});

$('[type="clear"]').click(function (e) {
    for (const key in nameNew) {
        document.querySelector('[name="' + key + '"]').value = '';
    }
    e.preventDefault();
});

// Kemungkinan akan dipakai atau dipakai di tempat lain
// let tagDonasi = document.getElementById('input-tag-donasi');
// tagDonasi.addEventListener('keyup', function (e) {
//     if (this.value < 1 && this.value.length > 0) {
//         this.value = 1;
//     }
//     tagDonasi.value = numberToPrice(this.value, 'Rp. ');
// });

// tagDonasi.addEventListener('change', function (e) {
//     if (priceToNumber(e.target.value) < 1000 && this.value.length > 0) {
//         this.value = 1000;
//     }
//     tagDonasi.value = numberToPrice(this.value, 'Rp. ');
// });

// let d = new Date(); d.setDate( d.getDate() );

// $('.datepicker [name="tanggal_awal"]').datepicker('setDate', d);
// $('.datepicker [name="tanggal_akhir"]').datepicker('setDate', '');

// $('.datepicker').datepicker({
//     todayBtn: "linked",
//     clearBtn: true,
//     language: 'id',
//     format: 'dd/mm/yyyy',
//     startDate: new Date()
// }).change(function(e) {
//     submitControl(e.target);
// });

// $('[type="reset"]').click(function () {
//     deleteProperties(nameNew);
//     submit.classList.add('disabled');
//     $buttonFile.text('Pilih file gambar');
//     $('#column-wrapper').addClass('d-none');
//     $('#gambar').attr('src', '');
//     cropper.destroy();
//     cropper = null;
// });

// Your Own
// let $buttonFile = $(".custom-file-label"),
//     $inputFile = $("#file-gambar"),
//     $buttonCrop = $('#crop');

// $inputFile.hide();

// $($inputFile).change(function () {
//     let file = readURL(this),
//         fileName = file.files[0].name,
//         image;

//     $('#column-wrapper').removeClass('d-none');
//     $buttonFile.text(fileName);
//     image = document.getElementById('image');
//     if (cropper != undefined) {
//         cropper.destroy();
//     }
//     setTimeout(()=>{
//         cropper = new Cropper(image, {
//             viewMode: 3,
//             dragMode: 'move',
//             crop: function(e) {
//                 cropHeight = e.detail.height;
//                 cropWidth = e.detail.width;
//                 console.log(cropHeight, cropWidth);
//             }
//         });
//     }, 50);
// });  

// $buttonCrop.click(function() {
//     let canvas = cropper.getCroppedCanvas({
//         imageSmoothingQuality: 'high',
//         height: cropHeight,
//         width: cropWidth,
//         minWidth: 256,
//         minHeight: 256,
//         maxWidth: 4096,
//         maxHeight: 4096,
//         fillColor: '#fff'
//     });

//     canvas.toBlob(function(blob) {
//         url = URL.createObjectURL(blob);
        
//         let reader = new FileReader();
//             reader.readAsDataURL(blob); 
//             reader.onloadend = function() {
//                 let base64data = reader.result;  
//                 console.log(base64data);
//                 cropper.destroy();
//                 $('#image').attr('src', base64data);
//                 $buttonCrop.toggle();
//             }
//     });
// });

// function readURL(input) {
//     if (input.files && input.files[0]) {
//         var reader = new FileReader();
//         reader.onload = function (e) {
//             $('#image').attr('src', e.target.result);
//         }
//         reader.readAsDataURL(input.files[0]);
//         return input;
//     }
// }