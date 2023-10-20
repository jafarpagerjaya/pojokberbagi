let jsonClient = atob(decodeURIComponent(getCookie('client-pojokberbagi'))),
    client = JSON.parse(jsonClient);

if (client) {
    if (client.auth) {
        authChannelSignin.auth = client.auth;
        authChannelSignin['sender'] = window.location.pathname;
        authChannel.postMessage({
            action: "signin",
            rule: authChannelSignin
        });
    }
}