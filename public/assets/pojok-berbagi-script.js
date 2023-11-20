const COUNT_FORMATS = [
    { // 0 - 999
        letter: '',
        limit: 1e3
    },
    { // 1,000 - 999,999
        letter: 'Rb',
        limit: 1e6
    },
    { // 1,000,000 - 999,999,999
        letter: 'Jt',
        limit: 1e9
    },
    { // 1,000,000,000 - 999,999,999,999
        letter: 'M',
        limit: 1e12
    },
    { // 1,000,000,000,000 - 999,999,999,999,999
        letter: 'T',
        limit: 1e15
    }
];

let formatCount = function(value) {
    const format = COUNT_FORMATS.find(format => (value < format.limit));
    let formatObject = {}
    value = (1000 * value / format.limit);
    value = Math.round(value * 100) / 100; // 100 keep two decimal number, only if needed

    formatObject = {
        value: value,
        letter: format.letter,
        limit: format.limit
    }
    return formatObject;
};

let counterUpSup = function (counterTarget, counterSpeed, date = false) {
    return counterTarget.forEach(numberElem => {
        // Initial values
        counterTarget.innerHTML = '0';
        const finalValue = parseInt(numberElem.getAttribute('data-count-up-value'), 10);

        if (isNaN(finalValue)) {
            console.log('[data-count-up-value] tidak ditemukan pada ', numberElem);
            return false;
        }
        
        const animTime = counterSpeed;
        let date;
        // data-count-up-date => true or false
        if (numberElem.getAttribute('data-count-up-date')) {
            date = numberElem.getAttribute('data-count-up-date');
        }
        // const animTime = parseInt(numberElem.getAttribute('time'), 10);
        const initTime = performance.now();

        // Interval
        let interval = setInterval(function() {
            let t = (performance.now() - initTime) / animTime;

            let currentValue = Math.ceil(t * finalValue);

            if (currentValue > finalValue) {
                currentValue = finalValue;
            }

            if (date) {
                numberElem.innerHTML = formatCount(currentValue).value.toLocaleString('id-ID') + ' hari lagi';
            } else {
                numberElem.innerHTML = formatCount(currentValue).value.toLocaleString('id-ID');
            }

            if (formatCount(currentValue).letter.length) {
                numberElem.innerHTML = numberElem.innerText + ' <small class="pl-2">' + formatCount(currentValue).letter + '</small>';
                if (currentValue * 1000 / formatCount(currentValue).limit > formatCount(currentValue).value) {
                    numberElem.innerHTML = numberElem.innerHTML + '<sup>+</sup>';
                }
            }

            if (t >= 1) {
                clearInterval(interval);
            }
        }, 50);
    });
};

let counterUpProgress = function (counterTarget, counterSpeed) {
    return counterTarget.forEach(progressElem => {
        // Initial values
        const finalValue = parseInt(progressElem.getAttribute('aria-valuenow'), 10);
        const animTime = counterSpeed;
        // const animTime = parseInt(numberElem.getAttribute('time'), 10);
        const initTime = performance.now();
        progressElem.style.width = '0%';
        // Interval
        let interval = setInterval(function () {
            let t = (performance.now() - initTime) / animTime;

            let currentValue = Math.ceil(t * finalValue);

            if (currentValue > finalValue) {
                currentValue = finalValue;
            }

            progressElem.style.width = currentValue + '%';

            if (t >= 1) {
                clearInterval(interval);
            }
        }, 50);
    });
};

const doAnimations = function(elems) {
    elems.forEach(el => {
        let animationType = 'animated';

        // Add animate.css classes to
        // the elements to be animated
        // Remove animate.css classes
        // once the animation event has ended 
        // automaticly by wow
        el.classList.add(animationType);
    });
};

const detectMob = function() {
    return ( window.innerWidth <= 767 );
};

const detectTab = function() {
    return ( window.innerWidth <= 768 );
};

function keteranganJenisChannelPayment(jenis_cp, skip) {
    let keterangan_cp;
    if (jenis_cp != null) {
        jenis_cp = jenis_cp.toUpperCase();
    }
    if (jenis_cp == 'TB') {
        keterangan_cp = "Transfer Bank";
    } else if (jenis_cp == 'QR') {
        keterangan_cp = "QRIS";
    } else if (jenis_cp == 'EW') {
        keterangan_cp = "E-Wallet";
    } else if (jenis_cp == 'VA') {
        keterangan_cp = "Virtual Akun";
    } else if (jenis_cp == 'GM') {
        keterangan_cp = "Gerai Mart";
    } else if (jenis_cp == 'GI') {
        keterangan_cp = "Giro";
    } else if (jenis_cp == 'TN') {
        keterangan_cp = "Tunai";
    } else {
        keterangan_cp = "Unrecognize (Payment Method)";
    }
    return keterangan_cp;
}

function keteranganJenisChannelAccount(jenis_ca) {
    let text;
    jenis_ca = jenis_ca.toUpperCase();
    if (jenis_ca == 'RB') {
        text = 'Rekening Bank';
    } else if (jenis_ca == 'EW') {
        text = 'E-Wallet';
    } else if (jenis_ca == 'KT') {
        text = 'Tunai';
    } else {
        text = 'Unrecognize (CA Type)';
    }
    return text;
}

function iconSektor(id_sektor) {
    let icon = '';
    if (id_sektor != null) {
        id_sektor = id_sektor.toUpperCase();
    }
    if (id_sektor == 'S') {
        icon = '<i class="lni lni-heart"></i>';
    } else if (id_sektor == 'E') {
        icon = '<i class="lni lni-bar-chart"></i>';
    } else if (id_sektor == 'B') {
        icon = '<i class="lni lni-warning"></i>';
    } else if (id_sektor == 'K') {
        icon = '<i class="lni lni-sthethoscope"></i>';
    } else if (id_sektor == 'P') {
        icon = '<i class="lni lni-graduation"></i>';
    } else if (id_sektor == 'L') {
        icon = '<i class="lni lni-sprout"></i>';
    } else {
        icon = '<i class="lni lni-support"></i>';
    }
    return icon;
}

function statusBantuan(status) {
    let badge = {};
    if (status != null) {
        status = status.toLowerCase();
    }
    if (status == 'd') {
        badge.class = 'badge-primary';
        badge.text = 'Berjalan';
    } else if (status == 's') {
        badge.class = 'badge-danger';
        badge.text = "Selesai";
    } else if (status == 'b') {
        badge.class = 'badge-warning'; 
        badge.text = "Menunggu Penilaian";
    } else if (status == 'c') {
        badge.class = 'badge-info'; 
        badge.text = "Penilaian";
    } else {
        badge.class = 'badge-secondary'; 
        badge.text = "Ditolak";
    }
    return badge;
}

function deleteProperties(objectToClean) {
    for (var x in objectToClean) if (objectToClean.hasOwnProperty(x)) delete objectToClean[x];
}

function dateToID(date) {
    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
    return new Date(date).toLocaleDateString("id-ID", optionsDate).replace(/\./g,':');
}

function nomorCPTunai(jenis, nomor) {
    if (jenis != null) {
        jenis = jenis.toUpperCase();
    }
    if (jenis == 'TN') {
        return 'CR Kantor Pusat';
    } else {
        return nomor;
    }
}

// Toast
let timeIntervalList = {};

// Toast Starter Time
function toastPassed(element) {
    const startTime = new Date();
    let dataToast;

    element.innerHTML = 'Baru saja';

    if (element.closest('.toast[data-toast]').getAttribute('id') == null) {
        dataToast = element.closest('.toast[data-toast]').getAttribute('data-toast');
    } else {
        dataToast = element.closest('.toast[data-toast]').getAttribute('id');
    }
    
    let timeInterval = setInterval(() => {
        element.innerHTML = timePassed(startTime);
        console.log(timePassed(startTime));
    }, 1000);

    timeIntervalList[dataToast] = timeInterval;
};

// Toast Stoper Time
function stopPassed(dataToast) {
    clearInterval(timeIntervalList[dataToast]);
    delete timeIntervalList[dataToast];
}

// Toast Atributes Maker
function setMultipleAttributesonElement(elem, elemAttributes) {
    for(let i in elemAttributes) {
        elem.setAttribute(i, elemAttributes[i]);
    }
}

const debounceIgnoreFirst = (fn, delay) => {
    let timer = null;
    return function(...args) {
        clearTimeout(timer);
        timer = setTimeout(() => {
            fn(...args);
        }, delay);
    }
};

const debounceIgnoreLast = (fn, delay) => {
    let timer;
        return (...args) => {
        if (!timer) {
            fn(...args);
        }
        clearTimeout(timer);
        timer = setTimeout(() => {
            timer = undefined;
            // if ([...args][0].target.classList.contains('disabled')) {
            //     [...args][0].target.classList.remove('disabled');
            // }
        }, delay);
    };
};

// Example using

// let functionNameOperation = () => {
//     console.log("Like Clicked");
// };
  
// debounceNameVar = debounceIgnoreFirst(functionNameOperation, 1000);
  
// const btn = document.querySelector("#like");
// btn.addEventListener("click", debounceNameVar);