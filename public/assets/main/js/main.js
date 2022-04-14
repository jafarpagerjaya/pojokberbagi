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