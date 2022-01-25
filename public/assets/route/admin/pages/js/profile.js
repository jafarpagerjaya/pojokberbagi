let pathname = window.location.pathname,
    url = window.location.href;

if (url.split('/').at(-1) == '#kelola-password') {
    $('.collapse').collapse();
}