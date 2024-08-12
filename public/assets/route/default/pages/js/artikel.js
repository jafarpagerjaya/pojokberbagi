let objectArtikel = {
    filter: {
        years: []
    }
};
const filterList = document.querySelectorAll('.dropdown-menu li>.inputGroup>input[type="checkbox"]');
filterList.forEach(input => {
    input.addEventListener('click', function(e) {
        if (!e.target.checked) {
            objectArtikel.filter.years.indexOf(e.target.value) !== -1 && objectArtikel.filter.years.splice(objectArtikel.filter.years.indexOf(e.target.value), 1)
        }

        const btns = getSiblings(e.target.closest('li')).at(-1).querySelectorAll('button');
        if (btns.length) {
            if (e.target.closest('#list-year').querySelectorAll('.inputGroup input[type="checkbox"]:checked').length) {
                btns.forEach(btn => {
                    btn.classList.add('filter-active');
                });
            } else {
                btns.forEach(btn => {
                    btn.classList.remove('filter-active');
                });
            }
        }       
    });
    setTimeout(() => {
        input.checked = false;
    }, 0);
});

const filterBtn = document.querySelector('button[data-bs-toggle="dropdown"]');
if (filterBtn != null) {
    filterBtn.addEventListener('click', function(e) {
        if (e.target.classList.contains('show')) {
            document.querySelector('body').classList.add('dimmed');
            const dimmed = document.createElement('div'),
            dimmedClass = {
                'class': 'position-fixed dimmed'
            };
            
            setMultipleAttributesonElement(dimmed, dimmedClass)
            document.querySelector('body').appendChild(dimmed)
        } else {
            document.querySelector('body').classList.remove('dimmed');
            if (document.querySelector('body>.dimmed') != null) {
                document.querySelector('body>.dimmed').remove();
            }
        }
    });
}

if (document.querySelector('[data-bs-toggle="dropdown"]') != null) {
    const dropdown = new bootstrap.Dropdown('[data-bs-toggle="dropdown"]');

    document.querySelector('body').addEventListener('click', function(e) {        
        if (e.target.tagName == 'BUTTON'
            && e.target.classList.contains('filter-active')
            && e.target.classList.contains('reset')
            && e.currentTarget.classList.contains('dimmed')) {
                filterList.forEach(el => {
                    if (el.checked) {
                        el.click();
                    }
                });
        }

        if (e.target.tagName == 'BUTTON'
            && e.target.classList.contains('filter-active')
            && e.currentTarget.classList.contains('dimmed')) {
                e.currentTarget.classList.remove('dimmed');
                e.currentTarget.querySelector('.dimmed').remove();
                dropdown.hide();
            
                e.target.closest('ul').querySelectorAll('#list-year input[type="checkbox"]:checked').forEach(el => {
                    objectArtikel.filter.years.push(el.value);
                    data.token = body.getAttribute('data-token');
                    if (Object.keys(objectArtikel.filter).length) {
                        if (objectArtikel.filter.hasOwnProperty('years')) {
                            if (objectArtikel.filter.years.length) {
                                data = Object.assign({}, data, objectArtikel);
                                data.offset = 0;
                            }
                        }
                    }

                    console.log(data);
                    // fetchGetsArtikel

                    artikel.innerHTML = '';
                    fetchData('/publikasi/fetch/gets/artikel', data, artikel, 'gets-artikel');
                });                
        }

        if (e.target.tagName == 'DIV'
            && e.currentTarget.classList.contains('dimmed')
            && e.target.classList.contains('dimmed')) {
                e.currentTarget.classList.remove('dimmed');
                e.currentTarget.querySelector('.dimmed').remove();
                dropdown.hide();

                data.token = body.getAttribute('data-token');
                data.offset = 0;
                console.log(data);
                // fetchGetsArtikel

                artikel.innerHTML = '';
                fetchData('/publikasi/fetch/gets/artikel', data, artikel, 'gets-artikel');
        }
    });
}

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

const notifikasiModalEl = document.getElementById('notifikasi');
// Modal BS
if (notifikasiModalEl != null) {
    var myModal = new bootstrap.Modal(notifikasiModalEl, {
        backdrop: 'static', 
        keyboard: false
    });
    setTimeout(()=>{
        myModal.toggle();
    }, 500);
}

const artikel = document.getElementById('artikel');

document.addEventListener('scroll', function(e) {
    const artikelBottomOffset = artikel.offsetTop + artikel.clientHeight,
          docBottomScroll = window.scrollY + document.documentElement.clientHeight;
    
    if (artikelBottomOffset > docBottomScroll) {
        return false;
    }

    if (artikel.classList.contains('loader-animation')) {
        return false;
    }

    if (!hasMoreData(objectArtikel.offset, objectArtikel.total_record) && !objectArtikel.load_more) {
        return false;
    }

    if (artikel.classList.contains('loader-animation')) {
        return false;
    }

    showLoader(artikel);

    let data = {
        offset: objectArtikel.offset,
        token: body.getAttribute('data-token')
    };    

    if (Object.keys(objectArtikel.filter).length) {
        data.filter = objectArtikel.filter;
    }

    if (objectArtikel.list_id.length) {
        data.list_id = objectArtikel.list_id;
    }
    
    const elCardLoad = '<div class="col-lg-3 col-md-4 col-6 wow fadeInUp load" data-wow-delay="0.4s" data-id-artikel="'+ 'reverseString(btoa(rD.id_artikel))' +'" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;"><a class="p-4 single-service animation d-block" href="javascript::void(0);"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="" alt="'+ 'rD.nama_gambar' +'" class="img-fluid opacity" data-animation-name="zoom-in"></div><h6 class="mt-3 mb-1 overflow-hidden title"><span class="w-100">'+ 'rD.judul' +'</span><span class="w-65" style="margin-top: 2px;">'+ 'rD.judul' +'</span></h6><div class="d-flex align-items-center gap-1 text-black-50"><i class="fas fa-clock"></i><span class="small">'+ 'rD.time_ago' +'</span></div></a></div>';
    
    for (let index = 0; index < colType; index++) {
        artikel.insertAdjacentHTML('beforeend', elCardLoad);
    }
    
    setTimeout(() => {
        // console.log(data);
        // fetchGetsArtikel        
        fetchData('/publikasi/fetch/gets/artikel', data, artikel, 'gets-artikel');
    }, 1500);
});

let resizeTimeout, colType = colPRow(window.innerWidth);

window.addEventListener('resize', function (e) {
    clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(() => {
        colType = colPRow(this.screen.width);
    }, 50);
});

function colPRow(width) {
    if (width >= 992) {
        return 4;
    } else if (width < 992 && width >= 768) {
        return 3;
    } else {
        return 2;
    } 
}

const showLoader = function(target) {
    target.classList.add('loader-animation');
};

const hideLoader = function(target) {
    target.classList.remove('loader-animation');
};

const hasMoreData = (offset, total) => {
    return offset < total;
};

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

        if (response.error) {
            createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                'autohide': false
            }).toast('show');
            // root.querySelectorAll('.load').forEach(card => {
            //     card.remove();
            // });
            // hideLoader(root);
            return false;
        }

        switch(f) {
            case 'gets-artikel':
                fetchGetsArtikel(response, root);
            break;

            default:
                let currentDate = new Date(),
                timestamp = currentDate.getTime(),
                invalid = {
                    error: true,
                    data_toast: 'invalid-show-modal-feedback',
                    feedback: {
                        message: 'Unrecognize function-alias'
                    }
                };

                invalid.id = invalid.data_toast +'-'+ timestamp;
            
                createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
                $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
                    'delay': 10000
                }).toast('show');
            break;
        }
    });
};

let fetchGetsArtikel = function(response, root) {
    if (response.error) {
        return false;
    }

    const data = response.feedback.data;
    
    if (!data.length) {
        const noData = '<div class="col-12 d-flex flex-column justify-content-center align-items-center text-center"><div class="row m-0 w-100"><div class="col-12 border rounded-box p-4 border-light"><center><dotlottie-player src="https://lottie.host/38fdb67f-1bb5-4eb6-a1e5-d058b08ee712/5NWTuM8z9R.json" background="transparent" speed="1" style="width: 300px; height: 300px;" loop autoplay></dotlottie-player></center><p class="fs-5 fw-bolder my-2">Artikel Kosong</p><div class="row justify-content-center"><div class="col-md-8 col-lg-6"><p class="desc text-black-50">Maaf, belum ada artikel yang ditambahkan. Jangan hawatir kami segera akan menambahkan artikel disini.</p></div></div><a href="/" class="btn rounded-box donasi fw-bold mt-2 mb-5">Kembali Ke beranda</a></div></div></div>';
        root.insertAdjacentHTML('beforeend', noData);
        let script = document.createElement("script");
        script.type = "module";
        script.source = "trushworty";
        script.src = "https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs";
        document.body.appendChild(script);
        return false;
    }

    root.querySelectorAll('.load').forEach(card => {
        card.remove();
    });
    hideLoader(root);

    if (response.feedback.remove_id != null) {
        response.feedback.remove_id.forEach(id => {
            root.querySelector('[data-id-artikel="'+reverseString(btoa(id))+'"]').remove();
        });
    }

    data.forEach(rD => {
        let card = '<div class="col-lg-3 col-md-4 col-6" data-id-artikel="'+ reverseString(btoa(rD.id_artikel)) +'" style="visibility: visible; animation-delay: 0.4s; animation-name: fadeInUp;"><!-- Start Single Service --><a class="p-4 single-service animation d-block" href="/publikasi/artikel/'+ rD.link +'"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="'+ rD.path_gambar +'" alt="'+ rD.nama_gambar +'" class="img-fluid opacity" data-animation-name="zoom-in"></div><h6 class="mt-3 mb-1 overflow-hidden title"><span>'+ rD.judul +'</span></h6><div class="d-flex align-items-center gap-1 text-black-50"><i class="fas fa-clock"></i><span class="small">'+ rD.time_ago +'</span></div></a><!-- End Single Service --></div>';
        root.insertAdjacentHTML('beforeend', card); 
    });
    
    objectArtikel.load_more = response.feedback.load_more;
    objectArtikel.offset = response.feedback.offset;
    objectArtikel.total_record = response.feedback.total_record;
    objectArtikel.list_id = response.feedback.list_id;
};

let data = {
    token: body.getAttribute('data-token')
};

if (Object.keys(objectArtikel.filter).length) {
    if (objectArtikel.filter.hasOwnProperty('years')) {
        if (objectArtikel.filter.years.length) {
            data = Object.assign({}, data, objectArtikel);
        }
    }
}

fetchData('/publikasi/fetch/gets/artikel', data, artikel, 'gets-artikel');