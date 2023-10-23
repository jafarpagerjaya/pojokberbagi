let qEditor = editor('#editor');
let clickFunction = function(e) {
    // e.target.classList.add('disabled');
    let Delta = qEditor.getContents(),
    root = e.target.closest('.modal'),
    data = {
        deskripsi: {
            id_bantuan: root.querySelector('[name="id_bantuan"]').value,
            judul: root.querySelector('[name="judul"]').value,
            isi: Delta
        },
        token: document.querySelector('body').getAttribute('data-token')
    };

    console.log(data);
    fetchData('/admin/fetch/create/bantuan/deskripsi-selengkapnya', data, root, 'create-deskripsi-selengkapnya');
};

document.querySelector('#buat-deskripsi-selengkapnya[type="submit"]').addEventListener('click', debounceIgnoreLast(clickFunction, 1000, clickFunction));

let fetchData = function (url, data, root, f) {
    
    // Fetch with token
    fetch(url, {
        method: "POST",
        cache: "no-cache",
        mode: "same-origin",
        credentials: "same-origin",
        headers: {
            "Content-Type": "application/json",
        },
        referrer: "no-referrer",
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(function (response) {
        document.querySelector('body').setAttribute('data-token', response.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });

        switch (f) {
            case 'create-deskripsi-selengkapnya':
                fetchCreateDeskripsiSelengkapnya(root, response);
                break;
            case 'read-deskripsi-selengkapnya':
                fetchReadDeskripsiSelengkapnya(root, response);
                break;
            default:
                break;
        }

        console.log('ini');
    })
};

let fetchCreateDeskripsiSelengkapnya = function(root, response) {
    
    // root.querySelector('.disabled').classList.remove('disabled');
};

let fetchReadDeskripsiSelengkapnya = function(root, response) {
    if (response.error) {
        return false;
    }
}

let fetchRead = function() {
    let data = {
        token: body.getAttribute('data-token')
    };

    if (getCookie('deskripsi-selengkapnya') != null) {
        const thisCookie = getCookie('deskripsi-selengkapnya');
        data.fields = {
            page: thisCookie.page
        };
        if (thisCookie.search != null) {
            data.fields.search = thisCookie.search;
        }
    }

    fetchData('/admin/fetch/read/bantuan/deskripsi-selengkapnya', data);
};

fetchRead();