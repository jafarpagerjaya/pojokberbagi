const staticCacheName = 'shell-cache-v4a';
const dynamicCache = 'dynamic-cache-v4';
const staticAssets = [
        '/',
        '/assets/images/pwa/error/medium.png?nw=1',
        '/assets/images/brand/favicon-pojok-icon.ico?nw=1',
        '/assets/images/pwa/icons/icon-144x144.png?nw=1',
        '/assets/images/pwa/wide.png?nw=1',
        '/assets/images/pwa/small.png?nw=1',
        '/assets/images/brand/pojok-berbagi-transparent.png?nw=1',
        '/manifest.json',
        '/fallback/offline',
        '/fallback/fetch',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        '/assets/route/default/core/css/LineIcons.3.0.css?nw=1',
        '/assets/route/default/core/fonts/LineIcons.woff?nw=1',
        '/assets/route/default/core/fonts/LineIcons.woff2?nw=1',
        '/assets/route/default/core/fonts/LineIcons.ttf?nw=1',
        '/assets/route/default/core/css/animate.css?nw=1',
        '/assets/route/default/core/css/main.css?v=171123',
        'https://fonts.gstatic.com/s/nunito/v26/XRXV3I6Li01BKofINeaB.woff2',
        'https://fonts.gstatic.com/s/nunito/v26/XRXX3I6Li01BKofIMNaDRs4.woff2',
        'https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,600;0,700;0,800;0,900;1,300;1,400;1,600;1,700;1,800;1,900&display=swap',
        '/assets/pojok-berbagi-style.css?tw=1',
        '/assets/route/default/core/css/services.css?tw=1',
        '/assets/route/default/core/css/default.css?v=110824',
        '/assets/route/default/pages/css/home.css?tw=1',
        '/vendors/jquery/dist/jquery.min.js',
        '/assets/route/default/core/js/wow.min.js?nw=1',
        '/assets/route/default/core/js/main.js?nw=1',
        '/assets/pojok-berbagi-script.js?tw=1',
        '/assets/route/default/core/js/default.js?v=110824',
        '/assets/main/js/token.js?tw=1',
        '/assets/route/auth/js/auth.js?v=071224',
        '/assets/route/default/pages/js/home.js?tw=1',
        '/assets/main/js/fallback.js?tw=1',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
        'https://lottie.host/994d72ff-5477-41a0-8efa-a126f1ba34e9/xsTdPGGTsY.lottie',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-ODPU3M3Z.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/lottie_svg-MJGYILXD-NRTSROOT.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-GVESGNEB.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-HDDX7F4A.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-ZWH2ESXT.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/dotlottie-player.mjs',
    ];

let shellCache = staticCacheName;

const zeroPad = (num, places) => String(num).padStart(places, '0')

async function fetchLastModified(url) {
    return await fetch(url, { 
        method: "HEAD" 
    })
    .then(r => {         
        const data = new Date(r.headers.get('Last-Modified')),
              timestampVersioning = r.url.split('?tw=1');
        if (timestampVersioning.length > 1) {
            date = timestampVersioning[0]+'?v='+Math.floor(data.getTime() / 1000).toFixed(0);
        } else {
            date = url+'?v='+zeroPad(data.getDate(),2)+''+zeroPad(data.getMonth()+1,2)+''+zeroPad(data.getFullYear().toString().substring(2),2);
        }
        return date;
    })
}

const limitCacheSize = (name, size) => {
    caches.open(name).then(cache => {
        cache.keys().then(keys => {
            if (keys.length > size) {
                cache.delete(keys[0]).then(limitCacheSize(name, size));
            }
        })
    })
};
    
// install service worker
self.addEventListener('install', e => {
    console.log('ServiceWorker install successful');
    
    // PR Dynamic
    let promises = staticAssets.map(function(asset){
        let splitRequest = asset.split('/');
        if (splitRequest[1] === 'assets') {
            const splitVersion = splitRequest.pop().split('?v=');
            if (splitVersion.length === 1) {
                if (splitVersion[0].split('?nw=1').length > 1) {
                    return asset.replace('?nw=1','');
                }

                // if (shellCache === staticCacheName) {
                //     const cur_date = new Date();
                //     shellCache += '-v-'+zeroPad(cur_date.getDate(),2)+''+zeroPad(cur_date.getMonth()+1,2)+''+zeroPad(cur_date.getFullYear().toString().substring(2),2)+''+zeroPad(cur_date.getHours(),2)+''+cur_date.getMilliseconds(); 
                //     // console.log(shellCache);
                // }

                return fetchLastModified(asset).then(function(r) {
                    return r
                });
            }
        }

        return asset;
    })


    e.waitUntil(
        // console.log(shellCache)
        // caches.open(staticCacheName).then(cache => {
        //     console.log('catch shell assets/the static assets');
        //     cache.addAll(staticAssets);
        // })

        // PR Dynamic
        caches.open(shellCache).then(cache => {
            console.log('catch shell assets/the static assets');

            return Promise.all(promises).then(function(results) {
                cache.addAll(results);
            })
        })
    );
});

// activate event
self.addEventListener('activate', e => {
    console.log('ServiceWorker activate successful');
    e.waitUntil(
        caches.keys().then(keys => {
            // console.log(keys);
            return Promise.all(keys
                .filter(key => key !== shellCache && key !== dynamicCache)
                .map(key => caches.delete(key))
            )
        })
    )
});

// fetch event
self.addEventListener('fetch', e => {
    // console.log('fetch event: ', e.request.url);
    e.respondWith(
        caches.match(e.request.url).then(cachesResponse => {
            const ownAsset = e.request.url.split(self.location.origin);
            if (cachesResponse && ownAsset.length > 1) {                            
                if ((ownAsset[1].indexOf('.') === -1) || (ownAsset.indexOf('.html') > -1)) {
                    if (ownAsset[1].indexOf('token/regenerate') > 0) {
                        fetch('/default/home/token/regenerate', {
                            method: "POST",
                            cache: "no-cache",
                            mode: "same-origin",
                            credentials: "same-origin",
                            headers: {
                                "Content-Type": "application/json",
                            },
                            referrer: "no-referrer"
                        })
                        .then(response => response.json())
                        .then(function(result) {
                            self.clients.matchAll().then((clients) => { 
                                clients.forEach((client) => { 
                                    client.postMessage({  
                                        type: 'updateToken',  
                                        data: result.token 
                                    }) 
                                }) 
                            })
                        });
                    } else {
                        if (navigator.onLine) {
                            return fetch(e.request);
                        }
                    }
                }
            }
            // return cachesResponse || fetch(e.request);
            return cachesResponse || fetch(e.request).then(fetchRes => {
                if (ownAsset.length > 1) {
                    switch(ownAsset[1]) {
                        case '/home/kunjungan':
                            return fetchRes;
                        break;
                        default:
                            if (ownAsset[1].indexOf('fetch') > 0) {
                                return fetchRes;
                            }

                            if (ownAsset[1].indexOf('token/regenerate') > 0) {
                                return fetchRes;
                            }
                        break;
                    }
                }
                return caches.open(dynamicCache).then(cache => {
                    cache.put(e.request.url, fetchRes.clone())
                    limitCacheSize(dynamicCache, 30);
                    return fetchRes;
                })
            });
        })
        .catch(() => {
            // console.log(e.request.url);
            const serverAsset = e.request.url.split(self.location.origin);
            const htmlOrUriPass = ((serverAsset.length > 1) && ((serverAsset[1].indexOf('.') === -1) || (e.request.url.indexOf('.html') > -1)));

            if (htmlOrUriPass) { 
                if (serverAsset[1].indexOf('fetch') > 0) {
                    console.log('Disconnect from internet');
                    return caches.match('/fallback/fetch');
                }      
                return caches.match('/fallback/offline');
            }

            if (e.request.destination == 'image') {
                return caches.match('/assets/images/pwa/error/medium.png');
            }
        })
    );
});