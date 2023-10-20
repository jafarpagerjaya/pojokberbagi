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
        switch (f) {
            case 'create-deskripsi-selengkapnya':
                fetchCreateDeskripsiSelengkapnya(root, response);
                break;
            default:
                break;
        }
    })
};

let fetchCreateDeskripsiSelengkapnya = function(root, response) {
    document.querySelector('body').setAttribute('data-token', response.token);

    fetchTokenChannel.postMessage({
        token: body.getAttribute('data-token')
    });
    // root.querySelector('.disabled').classList.remove('disabled');
};