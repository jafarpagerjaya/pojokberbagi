// Place this file after token.js
// Dont'forget to set Session Token in controler method
let fetchTokenChannelHref;
if (body.getAttribute('data-token') != null) {
    fetchTokenChannelHref = new BroadcastChannel('fetch-token');
    // If recive message
    fetchTokenChannelHref.onmessage = event => { 
        // set data token
        body.setAttribute('data-token', event.data.token);
        let aTokenList = document.querySelectorAll('a.token-last-param');
        if (aTokenList.length > 0) {
            aTokenList.forEach(element => {
                let href = element.getAttribute('href');
                let arrayHref = href.split('/');
                arrayHref.pop();
                arrayHref.push(event.data.token);
                element.setAttribute('href', arrayHref.join('/'));
            });
        }
    };

    fetchTokenChannelHref.postMessage({
        token: body.getAttribute('data-token')
    });
}