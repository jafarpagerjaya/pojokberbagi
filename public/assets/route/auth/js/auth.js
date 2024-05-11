function setCookie(key, value, expiry, path = null) {
    let expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + (path != null ? ';path=' + path : '') + ';expires=' + expires.toUTCString();
}

function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

function eraseCookie(key, path = null) {
    var keyValue = getCookie(key);
    if (path == null) {
        setCookie(key, keyValue, '0');
    } else {
        setCookie(key, keyValue, '0', path);
    }
}

function updateCookie(key, value, path = null, expiry) {
    document.cookie = key + '=' + value + (path != null ? ';path=' + path : '') + ';expires=' + expiry.toUTCString();
}

function removeAuthClient(cookieClient, cookieName, path = null) {
    delete cookieClient.auth;
    if (Object.keys(cookieClient).length == 1 && Object.keys(cookieClient)  == 'expiry') {
        eraseCookie(cookieName, path);
    } else {
        let cookieValue = btoa(JSON.stringify(cookieClient)),
            nDate = new Date(cookieClient.expiry * 1000);
        updateCookie(cookieName, cookieValue, path, nDate);
        // Copy new cookie client-pojokberbagi into localStorage
        let jsonClient = decodeURIComponent(getCookie('client-pojokberbagi'));
        localStorage.setItem("client-pojokberbagi", jsonClient);
    }
}

let authChannel = new BroadcastChannel('auth');

const isBannedRoute = (bannedRoute, logText = null) => {
    document.addEventListener( 'visibilitychange' , function() {
        if (!document.hidden) {
            setTimeout(()=>{
                let pathname = window.location.pathname,
                route = pathname.split('/').at(1);
            
                if (bannedRoute[route] != undefined) {
                    // This tab route is bannedList
                    // console.log('Banned Tab Do Route');
                    // if (logText.length) {
                    //     console.log(logText);
                    // }
        
                    if (bannedRoute[route].length == 0) {
                        bannedRoute[route] = pathname;
                    }
                    setTimeout(()=>{
                        window.location.href = bannedRoute[route];
                    }, 50);
                } else {
                    // console.log("This tab is Ok");
                }
            }, 0);
        }
    }, false );
};

let authChannelSignout = {
    banned: {
        admin: '/auth',
        pemohon: '/auth',
        akun: '/auth',
        donasi: ''
    }
}

let authChannelSignin = {
    banned: {
        auth: '/auth',
        donasi: ''
    }
}

const signoutAction = (authChannelRule)=> {
    isBannedRoute(authChannelRule.banned, 'User was sign out');
};

const signinAction = (authChannelRule)=> {
    isBannedRoute(authChannelRule.banned, 'User was sign in');
};

// If message event received
authChannel.onmessage = function (e) {
    const cookieName = 'client-pojokberbagi';
    if (window.location.pathname != '/auth/signup') {
        // action is "signout" or "signin"
        if (e.data.action === "signout") {
            // Perform signout action
            signoutAction(e.data.rule);
        } else if (e.data.action === "signin") {
            let cookieClient = JSON.parse(atob(decodeURIComponent(getCookie(cookieName))));
            // Perform signin action
            if (cookieClient.auth == true) {
                setTimeout(()=>{
                    removeAuthClient(cookieClient, cookieName, '/');
                    signinAction(e.data.rule);
                    authChannelSignout.auth = true;
                }, 0);
            } else {
                console.log('Tidak Ada AUTH');
            }
        }
    }
};

// Copy new cookie client-pojokberbagi into localStorage
let jsonClientAuth = decodeURIComponent(getCookie('client-pojokberbagi'));

if (typeof jsonClientAuth == 'string' && jsonClientAuth == 'null') {
    // If cookie kosong / null
    if (localStorage.getItem("client-pojokberbagi")) {
        // If local ada
        setCookie('client-pojokberbagi', localStorage.getItem("client-pojokberbagi"), 365, '/');
        // console.log('local storage ada isinya diset cookie => ' + localStorage.getItem("client-pojokberbagi"));
    } else {
        // Local tidak ada do setKunjungan()
        setTimeout(()=>{
            let uri = window.location.href,
                ur = document.querySelector('body[data-uri]');
            if (ur) {
                uri = uri.replace(/\/$/, '');
                let realPath = atob(ur.getAttribute('data-uri'));
                $.post(
                    '/home/kunjungan', 
                    {uri : uri, path : realPath},
                    function(data, success) {
                        if (success) {
                            localStorage.setItem("client-pojokberbagi", decodeURIComponent(getCookie('client-pojokberbagi')));
                            // console.log('kunjungan isi local storage ambil dari cookie => ' + decodeURIComponent(getCookie('client-pojokberbagi')));
                        }
                    }
                );
            }
        }, 0);
    }
} else {
    // cookie ada
    localStorage.setItem("client-pojokberbagi", jsonClientAuth);
    setTimeout(()=>{
        let uri = window.location.href,
            ur = document.querySelector('body[data-uri]');
        
        if (ur) {
            uri = uri.replace(/\/$/, '');
            let realPath = atob(ur.getAttribute('data-uri'));
            $.post(
                '/home/kunjungan',
                {uri : uri, path : realPath}
            );
        }
    }, 0);
}

if (jsonClientAuth != 'null') {
    console.log(atob(decodeURIComponent(getCookie('client-pojokberbagi'))));
    console.log(decodeURIComponent(getCookie('client-pojokberbagi')));
}

// window.addEventListener('load', () => {
//     if (!('serviceWorker' in navigator)) {
//         console.log('service workers not supported 😣');
//         return
//     }
  
//     navigator.serviceWorker.register(window.location.origin + '/service-worker.js').then(
//       (registration) => {
//         console.log('ServiceWorker registration successful with scope: ', registration.scope, ' 👍🏼');
//       },
//       err => {
//         console.error('SW registration failed! 😱', err)
//       }
//     )

//     navigator.serviceWorker.addEventListener('message',  
//     (event) => { 
//         if (event.data && event.data.type === 'updateToken') { 
//             if (window.location.pathname.indexOf('/auth/') > -1) {
//                 document.querySelector('[name="token"]').value = event.data.data;
//             }
//         }
//     })
// });