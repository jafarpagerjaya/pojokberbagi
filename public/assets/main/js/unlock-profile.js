const formControlUnlock = document.querySelectorAll('.form-control');
formControlUnlock.forEach(el => {
    el.addEventListener('keypress', function(e) {
        if (e.key == '<' || e.key == '>') {
            e.preventDefault();
            return false;
        }
    });

    el.addEventListener('paste', function(e) {
        setTimeout(() => {
            let patternToEscape = /[\^~`!#$%^&*()+={}:;'"<>?\[\]|\\]/g;
            this.value = escapeRegExp(this.value.trim(),'',patternToEscape);
        }, 0);
    });
});

const inputUsername = document.getElementById('input-username');
let patternToBanned = /[^a-zA-Z0-9\s]/;
inputUsername.addEventListener('keypress', function(e) {
    if (patternToBanned.test(e.key) === true) {
        e.preventDefault();
    }
});

inputUsername.addEventListener('paste', function(e) {
    setTimeout(() => {
        this.value = escapeRegExp(this.value.trim(),'',/[^a-zA-Z0-9\s]/g);
    }, 0);
});

const inputEmail = document.getElementById('input-email');
inputEmail.addEventListener('keypress', function(e) {
    if (checkEmailChar(e.key) === false) {
        e.preventDefault();
    }
});

inputEmail.addEventListener('keyup', function() {
    if (checkEmailPattern(this.value) === true) {
        this.nextElementSibling.innerHTML = '';
        this.nextElementSibling.classList.remove('block');
        this.parentElement.classList.remove('error');
        this.classList.remove('is-invalid');
    }
});

inputEmail.addEventListener('focusout', function() {
    if (checkEmailPattern(this.value) === false) {
        this.nextElementSibling.innerHTML = 'Harus Sesuai Format';
        this.nextElementSibling.classList.add('block');
        this.parentElement.classList.add('error');
        this.classList.add('is-invalid');
    } else {
        if (this.parentElement.classList.contains('error') || this.classList.contains('is-invalid')) {
            this.nextElementSibling.innerHTML = '';
            this.nextElementSibling.classList.remove('block');
            this.parentElement.classList.remove('error');
            this.classList.remove('is-invalid');
        }   
    }
});

inputEmail.addEventListener('paste', function() {
    setTimeout(()=>{
        let patternToEscape = /[\^~`!#$%^&*()+={}:;'"<>,/?\[\]|\\\s]/g;
        this.value = escapeRegExp(this.value,'',patternToEscape);
        if (checkEmailPattern(this.value) === false) {
            this.nextElementSibling.innerHTML = 'Harus Sesuai Format';
            this.nextElementSibling.classList.add('block');
            this.parentElement.classList.add('error');
            this.classList.add('is-invalid');
        } else {
            if (this.parentElement.classList.contains('error') || this.classList.contains('is-invalid')) {
                this.nextElementSibling.innerHTML = '';
                this.nextElementSibling.classList.remove('block');
                this.parentElement.classList.remove('error');
                this.classList.remove('is-invalid');
            }   
        }
    }, 0);
});

let resetButtton = document.querySelector('[type="reset"');
resetButtton.addEventListener('click', function() {
    setTimeout(() => {
        if (checkEmailPattern(inputEmail.value) === true) {
            inputEmail.nextElementSibling.innerHTML = '';
            inputEmail.nextElementSibling.classList.remove('block');
            inputEmail.parentElement.classList.remove('error');
            inputEmail.classList.remove('is-invalid');
        }
    }, 0);
});

const inputKontak = document.getElementById('input-kontak');
inputKontak.addEventListener('keypress', preventNonNumbersInInput);
// paste also restricted
inputKontak.addEventListener('input', restrictNumber);