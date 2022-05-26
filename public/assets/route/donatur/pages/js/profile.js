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

const inputSamaran = document.getElementById('input-samaran');
let ctrl;
inputSamaran.addEventListener('keyup', function(e) {
    if (e.key == 'Control') {
        ctrl = 'up';
    }
});

inputSamaran.addEventListener('keydown', function(e) {
    let pattern = /^[^a-zA-Z0-9\s]+$/;
    let ceretS = e.target.selectionStart;
    
    if (pattern.test(e.key) === true) {
        e.preventDefault();
        return false;
    }

    if (e.code == 'Space' && e.key == ' ' && (this.value.substring(ceretS, ceretS+1) == ' ' || this.value.substring(ceretS, ceretS-1) == ' ')) {
        e.preventDefault();
        return false;
    }

    if (e.key == 'Control' || ctrl == 'down' && e.code.indexOf('Key') >= 0) {
        ctrl = 'down';
        return false;
    }

    if (e.code.indexOf('Key') >= 0 || e.code == 'Space') {
        if (this.value.length > 0 && ceretS == 0 && e.target.selectionEnd == this.value.length) {
            return false;
        }
        this.value = this.value.slice(0,ceretS) + e.key + this.value.slice(ceretS);
        e.target.selectionStart = ceretS+1;
        e.target.selectionEnd = ceretS+1;
        if (ctrl != 'down') {
            e.preventDefault();
            return false;
        }
    }
});

inputSamaran.addEventListener('paste', function() {
    setTimeout(() => {
        this.value = escapeRegExp(this.value,'',/[^a-zA-Z0-9\s]/g).trim();
    }, 0);
});