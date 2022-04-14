function restrictNumber () {  
    var newValue = this.value.replace(new RegExp(/[^\d]/,'ig'), "");
    this.value = newValue;
};

function preventNonNumbersInInput(event){
    let characters = String.fromCharCode(event.which);
    if(!(/[0-9]/.test(characters))){
        event.preventDefault();
    }
};

function numberToPrice(angka, prefix) {
    var number_string = angka.replace(/[^,\d]/g, '').toString(),
        split = number_string.split(','),
        sisa = split[0].length % 3,
        rupiah = split[0].substr(0, sisa),
        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

    // tambahkan titik jika yang di input sudah menjadi angka ribuan
    if (ribuan) {
        separator = sisa ? '.' : '';
        rupiah += separator + ribuan.join('.');
    }

    rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
    return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
}

function priceToNumber(v){
    if(!v){return 0;}
    v=v.split('.').join('');
    v=v.split(',').join('.');
    return Number(v.replace(/[^0-9.]/g, ""));
}

function autoResize() { 
    this.style.height = 'auto'; 
    this.style.height = this.scrollHeight + 'px'; 
}

function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function escapeRegExp(str) {
    return str.replace(/[<>]/g, ' ');
}

function ucwords(string) {
    if (string == null) {
        return '';
    }
    str = string.trim();
    return str.replace(/(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g, function(s){
        return s.toUpperCase();
    });
};