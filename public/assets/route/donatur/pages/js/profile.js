let pathname = window.location.pathname,
    url = window.location.href;

if (url.split('/').at(-1) == '#kelola-password') {
    $('.collapse').collapse();
}

let inputNoSpace = document.querySelectorAll('.no-space');

inputNoSpace.forEach(el => {
    el.addEventListener('keypress', function(e) {
        noSpace(e);
    });
});

let inputNoDoubleSpace = document.querySelectorAll('.no-double-space');
inputNoDoubleSpace.forEach(el => {
    el.addEventListener('keydown', function(e) {
        setTimeout(()=>{
            if (e.target.value.indexOf('  ') >= 0) {
                let ceret = e.target.selectionStart;
                e.target.value = e.target.value.trim().replace(/\s+/g, " ");
                e.target.selectionEnd = ceret-1;
            }
        }, 0);
    });
});