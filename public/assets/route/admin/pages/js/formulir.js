function numberToPrice(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
}

function priceToNumber(v){
    if(!v){return 0;}
    v=v.split('.').join('');
    v=v.split(',').join('.');
    return Number(v.replace(/[^0-9.]/g, ""));
}

let textarea = document.querySelector(".textarea"); 
textarea.addEventListener('input', autoResize, false); 

function autoResize() { 
    this.style.height = 'auto'; 
    this.style.height = this.scrollHeight + 'px'; 
}

let charLeft = document.getElementById('charLeft');
textarea.addEventListener('keyup', function() {
    charLeft.innerHTML = (this.value.length+'/'+255);
});

let nominalRab = document.getElementById('input-rab');
nominalRab.addEventListener('keyup', function (e) {
    nominalRab.value = numberToPrice(this.value, 'Rp. ');
});

let minDonasi = document.getElementById('input-min-donasi');
minDonasi.addEventListener('keyup', function (e) {
    if (this.value < 1 && this.value.length > 0) {
        this.value = 1;
    }
    minDonasi.value = numberToPrice(this.value, 'Rp. ');
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
    var nan = count(val, /\D/, pos) // nan va calcolato prima di eliminare i separatori
    val = val.replace(/\D/g,'')

    var mask = mask.match(/^(\D*)(.+9)(\D*)$/)
    if (!mask) return // meglio exception?
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
lamaDonasi.addEventListener('keypress', function(e) {
    let selfValue = parseInt(this.value),
        ceret = e.target.selectionStart;

    if (selfValue < 1) {
        this.value = '';
    }

    if (this.getAttribute('data-value') == maxLamaPenayangan) {
        e.preventDefault();
        return false;
    }

    handleMask(e,'9999 hari');

    setTimeout(()=>{
        if (parseInt(e.target.value) > maxLamaPenayangan) {
            this.value = maxLamaPenayangan + ' hari';
            e.target.selectionEnd = maxLamaPenayangan.toString().length;
        }
    }, 0);
});

lamaDonasi.addEventListener('keyup', function(e) {
    let selfValue = parseInt(this.value),
        ceret = e.target.selectionStart;
    if (selfValue < 1 || isNaN(selfValue)) {
        this.value = '';
    }
    this.setAttribute('data-value', selfValue);
});

lamaDonasi.addEventListener('keydown', function(e) {
    let selfValue = parseInt(this.value),
        ceret = e.target.selectionStart;

        console.log(ceret, selfValue, e.key);
    
        if (ceret == 1 && isNaN(parseInt(e.key)) && selfValue < 10 && (e.key != 'ArrowUp' && e.key != 'Backspace' && e.key != 'ArrowLeft')) {
            e.preventDefault();
        }

        if (ceret == 2 && isNaN(parseInt(e.key)) && selfValue > 9 && selfValue < 100 && (e.key == 'ArrowRight' || e.key == 'Delete')) {
            e.preventDefault();
        }

        if (ceret == 3 && isNaN(parseInt(e.key)) && selfValue > 99 && selfValue < 1000 && (e.key != 'ArrowUp' && e.key != 'Backspace')) {
            e.preventDefault();
        }

        setTimeout(()=>{
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

lamaDonasi.addEventListener('click', function(e) {
    let selfValue = parseInt(this.value);
        e.target.selectionEnd = selfValue.toString().length;
});

lamaDonasi.addEventListener('paste', function(e) {
    if (parseInt(e.target.value) > maxLamaPenayangan) {
        this.value = maxLamaPenayangan;
    }
    setTimeout(()=>{
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
    },0);
});

minDonasi.addEventListener('change', function (e) {
    if (priceToNumber(e.target.value) < 1000 && this.value.length > 0) {
        this.value = 1000;
    }
    minDonasi.value = numberToPrice(this.value, 'Rp. ');
});

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

let jumlahTarget = document.getElementById('input-jumlah-target');
jumlahTarget.addEventListener('keyup', function (e) {
    jumlahTarget.value = numberToPrice(this.value);
});

jumlahTarget.addEventListener('change', function (e) {
    if (this.hasAttribute('data-min')) {
        minvalue = this.getAttribute('data-min');
        if (priceToNumber(this.value) < minvalue && this.value.length) {
            this.value = minvalue;
        }
        if (this.value == 0) {
            this.value = '';
        }
    }
    jumlahTarget.value = numberToPrice(this.value);
});

let d = new Date(); d.setDate( d.getDate() );

// $('.datepicker [name="tanggal_awal"]').datepicker('setDate', d);
// $('.datepicker [name="tanggal_akhir"]').datepicker('setDate', '');

$('.input-group-prepend').click(function () {
    $(this).siblings('textarea').focus();
});

const names = document.querySelectorAll('form [name]'),
      submit = document.querySelector('[type="submit"]');
let nameOld = {},
    nameNew = {};

let submitControl = function(self) {
    let min = self.getAttribute('data-min');
    if (self.value != nameOld[self.name] && !min || self.value != nameOld[self.name] && min && self.value.length) {
        nameNew[self.name] = self.value;
    } else {
        delete nameNew[self.name];
    }
    if (Object.keys(nameNew).length > 0) {
        submit.classList.remove('disabled');
    } else {
        submit.classList.add('disabled');
    }
};
names.forEach(element => {
    nameOld[element.name] = element.value;
    element.addEventListener('change', function() {
        submitControl(this);
        // console.log(this);
    });

    element.addEventListener('keyup', function() {
        submitControl(this);
    });

    element.addEventListener('paste', function() {
        setTimeout(()=>{
            submitControl(this);
        }, 0);
    });
});

$('.datepicker').datepicker({
    todayBtn: "linked",
    clearBtn: true,
    language: 'id',
    format: 'dd/mm/yyyy',
    startDate: new Date()
}).change(function(e) {
    submitControl(e.target);
});

submit.addEventListener('click', function(e) {
    if (!Object.keys(nameNew).length) {
        e.preventDefault();
    }
});

submit.addEventListener('mouseenter', function(e) {
    console.log(nameNew);
    if (!Object.keys(nameNew).length) {
        this.classList.add('disabled');
        return false;
    }
    if (this.classList.contains('disabled')) {
        this.classList.remove('disabled');
    }
});

// Your Own
let $buttonFile = $(".custom-file-label"),
    $inputFile = $("#file-gambar"),
    $buttonCrop = $('#crop');

$inputFile.hide();

var cropper;

$($inputFile).change(function () {
    let file = readURL(this),
        fileName = file.files[0].name,
        image;

    $('#column-wrapper').removeClass('d-none');
    $buttonFile.text(fileName);
    image = document.getElementById('image');
    if (cropper != undefined) {
        cropper.destroy();
    }
    setTimeout(()=>{
        cropper = new Cropper(image, {
            viewMode: 3,
            dragMode: 'move',
            crop: function(e) {
                cropHeight = e.detail.height;
                cropWidth = e.detail.width;
                console.log(cropHeight, cropWidth);
            }
        });
    }, 50);
});  

$buttonCrop.click(function() {
    let canvas = cropper.getCroppedCanvas({
        imageSmoothingQuality: 'high',
        height: cropHeight,
        width: cropWidth,
        minWidth: 256,
        minHeight: 256,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: '#fff'
    });

    canvas.toBlob(function(blob) {
        url = URL.createObjectURL(blob);
        
        let reader = new FileReader();
            reader.readAsDataURL(blob); 
            reader.onloadend = function() {
                let base64data = reader.result;  
                console.log(base64data);
                cropper.destroy();
                $('#image').attr('src', base64data);
                $buttonCrop.toggle();
            }
    });
});

function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $('#image').attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);
        return input;
    }
}

function deleteProperties(objectToClean) {
    for (var x in objectToClean) if (objectToClean.hasOwnProperty(x)) delete objectToClean[x];
}

$('[type="reset"]').click(function () {
    deleteProperties(nameNew);
    submit.classList.add('disabled');
    $buttonFile.text('Pilih file gambar');
    $('#column-wrapper').addClass('d-none');
    $('#gambar').attr('src', '');
    cropper.destroy();
    cropper = null;
});

$('[type="clear"]').click(function(e) {
    e.preventDefault();
    for (const key in nameNew) {
        document.querySelector('[name="'+key+'"]').value = '';
    }
});
