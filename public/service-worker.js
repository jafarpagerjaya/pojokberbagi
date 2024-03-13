const staticCacheName = 'shell-cache-v1';
const dynamicCache = 'dynamic-cache';
const staticAssets = [
        '/',
        '/assets/images/brand/pojok-berbagi-transparent.png?nw=1',
        '/manifest.json',
        '/fallback/offline',
        '/assets/route/default/core/css/LineIcons.3.0.css?nw=1',
        '/assets/route/default/core/fonts/LineIcons.woff?nw=1',
        '/assets/route/default/core/fonts/LineIcons.woff2?nw=1',
        '/assets/route/default/core/css/animate.css?nw=1',
        '/assets/route/default/core/css/main.css?v=171123',
        '/assets/route/default/core/css/default.css?v=220124',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css',
        '/vendors/jquery/dist/jquery.min.js',
        '/assets/route/default/core/js/wow.min.js?nw=1',
        '/assets/route/default/core/js/main.js?nw=1',
        '/assets/route/default/core/js/default.js?v=171123',
        '/assets/route/auth/js/auth.js?v=080324',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js',
        'https://lottie.host/994d72ff-5477-41a0-8efa-a126f1ba34e9/xsTdPGGTsY.lottie',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-ODPU3M3Z.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/lottie_svg-MJGYILXD-NRTSROOT.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-GVESGNEB.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-HDDX7F4A.mjs',
        'https://unpkg.com/@dotlottie/player-component@2.7.11/dist/chunk-ZWH2ESXT.mjs',
        'https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs',
    ];

let shellCache = staticCacheName;

const zeroPad = (num, places) => String(num).padStart(places, '0')

async function fetchLastModified(url) {
    return await fetch(url, { 
        method: "HEAD" 
    })
    .then(r => { 
        const data = new Date(r.headers.get('Last-Modified'))
        date = url+'?v='+zeroPad(data.getDate(),2)+''+zeroPad(data.getMonth()+1,2)+''+zeroPad(data.getFullYear().toString().substring(2),2);
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

                if (shellCache === staticCacheName) {
                    const cur_date = new Date();
                    shellCache += '-v-'+zeroPad(cur_date.getDate(),2)+''+zeroPad(cur_date.getMonth()+1,2)+''+zeroPad(cur_date.getFullYear().toString().substring(2),2); 
                    // console.log(shellCache);
                }

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
                // .filter(key => key !== staticCacheName && key !== dynamicCache)
                .map(key => caches.delete(key))
            )
        })
    )
});

// fetch event
self.addEventListener('fetch', e => {
    // console.log('fetch event: ', e.request.url);
    e.respondWith(
        caches.match(e.request).then(cachesResponse => {
            return cachesResponse || fetch(e.request).then(fetchRes => {
                return caches.open(dynamicCache).then(cache => {
                    cache.put(e.request.url, fetchRes.clone())
                    limitCacheSize(dynamicCache, 50);
                    return fetchRes;
                })
            });
        })
        // .catch(() => caches.match('/fallback/offline'))
        .catch(() => {
            // console.log(e.request.url);
            if (e.request.url.split(self.location.origin)[1].indexOf('.') == -1 && self.location.pathname !== '/home/kunjungan' || e.request.url.indexOf('.html') > -1) {
                return caches.match('/fallback/offline')
            }
        })
    );
});