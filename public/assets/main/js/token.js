// BroadcastChannel for data-token
const body = document.querySelector('body');
let fetchTokenChannel;
if (body.getAttribute('data-token') != null) {
    fetchTokenChannel = new BroadcastChannel('fetch-token');
    // If recive message
    fetchTokenChannel.onmessage = event => { 
        // set data token
        if (window.navigator.onLine) {
            body.setAttribute('data-token', event.data.token);
        }
    };

    if (window.navigator.onLine) {
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });
    }
}