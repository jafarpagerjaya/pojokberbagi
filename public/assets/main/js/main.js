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

function timePassed(startTime) {
    const units = {
        year  : 24 * 60 * 60 * 1000 * 365,
        month : 24 * 60 * 60 * 1000 * 365/12,
        day   : 24 * 60 * 60 * 1000,
        hour  : 60 * 60 * 1000,
        minute: 60 * 1000,
        second: 1000
    };

    let rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });

    let getRelativeTime = (d1, d2 = new Date()) => {
        let elapsed = d1 - d2;

        for (let u in units) {
            if (Math.abs(elapsed) > units[u] || u == 'second') {
                return rtf.format(Math.round(elapsed/units[u]), u);
            }
        }
    }
    return getRelativeTime(startTime);
};

// Form Control
const formControl = document.querySelectorAll('.form-control');
formControl.forEach(el => {
    el.addEventListener('keypress', function(e) {
        let ceret = e.target.selectionStart;
        if (ceret == 0 && e.keyCode == 32) {
            e.preventDefault();
        }
        if ((e.keyCode == 32 && ceret != 0 && e.target.value.indexOf(' ') >= 0) && (
                (this.value.substring(ceret, ceret-1) == ' ' && e.target.value.charCodeAt(ceret-1) == 32)
                || (this.value.substring(ceret, ceret+1) == ' ' && e.target.value.charCodeAt(ceret) == 32)
            )) {
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

// Dinon aktivkan sementara karena keyup mode masih ada buffer forming text
// let textCapList2 = document.querySelectorAll('.text-capitalize2');
// textCapList2.forEach(textCap2 => {
//     textCap2.addEventListener('keypress', function(e) {
//         let charCode = e.keyCode;
//         if (((charCode > 64 && charCode < 91) || (charCode > 96 && charCode < 123) || charCode == 8 || charCode == 32 || charCode == 20 || charCode == 18) == false) {
//             e.preventDefault();
//         }
//     });
//     textCap2.addEventListener('keyup', function(e) {
//         e.target.value = e.target.value.toLowerCase();
//         this.value = ucwords(e.target.value, false);
//     });
// });

let textCapList = document.querySelectorAll('.text-capitalize');
textCapList.forEach(textCap => {
    let ctrl;
    textCap.addEventListener('keyup', function(e) {
        if (e.key == 'Control') {
            ctrl = 'up';
        }
    });

    textCap.addEventListener('keydown', function(e) {
        let pattern = /^[^a-zA-Z\s]+$/;
        let ceretS = e.target.selectionStart;
        
        if (pattern.test(e.key) === true) {
            e.preventDefault();
            return false;
        }

        if (e.code == 'Space' && e.key == ' ' && (this.value.substring(ceretS, ceretS+1) == ' ' || this.value.substring(ceretS, ceretS-1) == ' ')) {
            e.preventDefault();
            return false;
        }

        if (e.key == 'Control' || ctrl == 'down' && e.code.indexOf('Key') >= 0) {
            ctrl = 'down';
            return false;
        }

        if (e.code.indexOf('Key') >= 0 || e.code == 'Space') {
            if (this.value.length > 0 && ceretS == 0 && e.target.selectionEnd == this.value.length) {
                return false;
            }
            this.value = ucwords(this.value.slice(0,ceretS) + e.key + this.value.slice(ceretS), false);
            e.target.selectionStart = ceretS+1;
            e.target.selectionEnd = ceretS+1;
            if (ctrl != 'down') {
                e.preventDefault();
                return false;
            }
        }
    });
    textCap.addEventListener('paste', function() {
        setTimeout(() => {
            this.value = escapeRegExp(this.value,'',/[^a-zA-Z\s]/g).trim();
        }, 0);
    });
});

// Set True Or False Default value is true
const spellCheck = false;
if (spellCheck === false) {
    const inputSpellCheckList = document.querySelectorAll('input[type="text"]');
    inputSpellCheckList.forEach(elInput => {
        elInput.setAttribute('spellcheck', spellCheck);
    });    
}
