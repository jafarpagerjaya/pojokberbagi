// Diulangn dengan fungsi resize
let footerSetter = function() {
    let wh = $( window ).height(),
    kurangHeight = wh - $('footer').offset().top - $('footer').height() - parseInt($('footer').css('padding-bottom')),
    mc = $('#main-content'),
    mcHeight = mc.height();
    
    if (kurangHeight > 0) {
        mc.css('min-height', (mcHeight + kurangHeight)+'px');
    }
};

footerSetter();

let resizeTimeout;
$(window).resize(function () {
    clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(function () {
        footerSetter();
    }, 50);
});


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
    
// Format Method:
function formatCount(value) {
    const format = COUNT_FORMATS.find(format => (value < format.limit));
    let formatObject = {}
    value = (1000 * value / format.limit);
    value = Math.round(value * 100) / 100; // 100 keep two decimal number, only if needed

    formatObject = {
        value: value,
        letter: format.letter
    }
    return formatObject;
}

let counterUp = function(counterTarget, counterSpeed) {
    return counterTarget.forEach(numberElem=> {
        // Initial values
        counterTarget.innerHTML = '0';
        const finalValue = parseInt(numberElem.getAttribute('data-target'), 10);
        const animTime = counterSpeed;
        // const animTime = parseInt(numberElem.getAttribute('time'), 10);
        const initTime = performance.now();
    
        // Interval
        let interval = setInterval(function() {
            let t = (performance.now() - initTime) / animTime;
    
            let currentValue = Math.ceil(t * finalValue);
    
            if (currentValue > finalValue) {
                currentValue = finalValue;
            }
    
            numberElem.innerHTML = formatCount(currentValue).value.toLocaleString('id-ID');
    
            if (formatCount(currentValue).letter.length) {
                numberElem.innerHTML = numberElem.innerText+ '<small class="pl-2">' +formatCount(currentValue).letter+'</small>';
                if (formatCount(currentValue).value*1000 < finalValue) {
                    numberElem.innerHTML = numberElem.innerHTML+ '<sup>+</sup>';
                }
            }
            
            if (t >= 1) {
                clearInterval(interval);
            }
        }, 50);
    });
}

$("#notif-modal").modal({backdrop: "static"});

const signoutButton = document.querySelectorAll('a[href="/auth/signout"]');

signoutButton.forEach((el)=>{
    el.addEventListener('click', function(e) {
        // BroadcastChannel
        authChannel.postMessage({
            action: "signout",
            rule: authChannelSignout
        });
        // Copy new cookie client-pojokberbagi into localStorage
        // let jsonClient = decodeURIComponent(getCookie('client-pojokberbagi'));
        // localStorage.setItem("client-pojokberbagi", jsonClient);
    });
});


// Form Control
const formControl = document.querySelectorAll('.form-control');
formControl.forEach(el => {
    el.addEventListener('keypress', function(e) {
        let ceret = e.target.selectionStart;
        if (ceret == 0 && e.keyCode == 32) {
            e.preventDefault();
        }
        if (e.keyCode == 32 && e.target.selectionStart != 0 && e.target.value.indexOf(' ') >= 0 && this.value.substring(e.target.selectionStart, e.target.selectionStart-1) == ' ' && e.target.value.charCodeAt(e.target.selectionStart-1) == 32) {
            e.preventDefault();
        }
    });

    el.addEventListener('paste', function(e) {
        setTimeout(()=>{
            if (e.target.value.indexOf('  ') >= 0) {
                let ceret = e.target.selectionStart;
                e.target.value = e.target.value.trim().replace(/\s+/g, " ");
                e.target.selectionEnd = ceret-1;
            }
        }, 0);
    });
});

let noSpace = function(event) {
    if (event.keyCode === 32) {
        event.preventDefault();
    }
}

let counterUpProgress = function (counterTarget, counterSpeed) {
    return counterTarget.forEach(progressElem => {
        // Initial values
        const finalValue = parseInt(progressElem.getAttribute('aria-valuenow'), 10);
        const animTime = counterSpeed;
        // const animTime = parseInt(numberElem.getAttribute('time'), 10);
        const initTime = performance.now();

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
}