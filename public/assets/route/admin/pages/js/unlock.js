const inputAlamat = document.getElementById('input-alamat');
inputAlamat.addEventListener('keydown', function(e) {
    let bannedCharFound = /[^a-zA-Z0-9\s\/.-]/;
    if (bannedCharFound.test(e.key) === true) {
        e.preventDefault();
        return false;
    }
    let ceretS = e.target.selectionStart;
    if ((((e.code == 'Period' && e.key == '.') || (e.code == 'Slash' && e.key == '/') || (e.code == 'Minus' && e.key == '-')) && (
            this.value.substring(ceretS, ceretS+1) == '.' || 
            this.value.substring(ceretS, ceretS-1) == '.' || 
            this.value.substring(ceretS, ceretS+1) == '/' || 
            this.value.substring(ceretS, ceretS-1) == '/' || 
            this.value.substring(ceretS, ceretS+1) == '-' || 
            this.value.substring(ceretS, ceretS-1) == '-' ||
            (this.value.substring(ceretS, ceretS+1) == ' ' && (this.value.substring(ceretS+1, ceretS+2) == '.' || this.value.substring(ceretS+1, ceretS+2) == '/' || this.value.substring(ceretS+1, ceretS+2) == '-')) ||
            (this.value.substring(ceretS, ceretS-1) == ' ' && (this.value.substring(ceretS-1, ceretS-2) == '.' || this.value.substring(ceretS-1, ceretS-2) == '/' || this.value.substring(ceretS-1, ceretS-2) == '-'))
        ))) {
        e.preventDefault();
        return false;
    }
});

inputAlamat.addEventListener('paste', function(e) {
    setTimeout(() => {
        this.value = escapeRegExp(this.value.trim(),'',/[^a-zA-Z0-9\s\/.-]/g);
    }, 0);
});