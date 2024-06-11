// BroadcastChannel for data-token
const body = document.querySelector('body');
let fetchTokenChannel;
if (body.getAttribute('data-token') != null || body.querySelector('[name="token"]') != null) {
    fetchTokenChannel = new BroadcastChannel('fetch-token');
    // If recive message
    fetchTokenChannel.onmessage = event => { 
        // set data token
        if (window.navigator.onLine) {
            if (body.querySelector('[name="token"]') != null) {
                body.querySelector('[name="token"]').value = event.data.token;
            } else {
                body.setAttribute('data-token', event.data.token);
            }
        }
    };

    if (window.navigator.onLine) {
        let tokenValue;
        if (body.querySelector('[name="token"]') != null) {
            tokenValue = body.querySelector('[name="token"]').value;
        } else {
            tokenValue = body.getAttribute('data-token');
        }
        fetchTokenChannel.postMessage({
            token: tokenValue
        });
    }
}