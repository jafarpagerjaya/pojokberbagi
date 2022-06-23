const kategoriColor = document.querySelector('section.kategori-area'),
      elResumeHeader = document.querySelector('#resume-kategori .text-kategori');

elResumeHeader.style.color = getComputedStyle(kategoriColor).getPropertyValue('--kategori-color');

const resumeElList = document.querySelectorAll('#resume-kategori [data-count-up-value]'),
      counterSpeed = 3000;

counterUpSup(resumeElList, counterSpeed);

const loadMoreBtn = document.getElementById('load-more');
if (loadMoreBtn != null) {
    let listId = [];
    let data = {};
    let fetchData = function(url, data, targetRow) {
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
        .then(function(result) {
            
            if (result.error == false) {
                // Success
                data.limit = result.feedback.limit;
                data.offset = result.feedback.offset;
                data.load_more = result.feedback.load_more;
                data.record = result.feedback.total_record;
                // List Id has been load;
                data.list_id = JSON.parse(atob(decodeURIComponent(result.feedback.list_id)));

                listId = data.list_id;

                let delay_card = null;
                
                if (result.feedback.newest_data != null) {
                    // show recent newest list
                    result.feedback.newest_data.forEach(element => {
                        if (delay_card == null) {
                            delay_card = 2 * wowDelay;
                        } else {
                            delay_card = delay_card + wowDelay;
                        }
                        if (element.path_gambar_medium == null) {
                            element.path_gambar_medium = '';
                        }
                        if (element.nama_gambar_medium == null) {
                            element.nama_gambar_medium = 'Gambar '+element.nama_bantuan;
                        }
                        if (element.path_gambar_pengaju == null) {
                            element.path_gambar_pengaju = '';
                        }
                        let listBantuanCard = '<div class="col-lg-4 col-md-6 col-12 wow fadeInUp d-none" data-wow-delay="' + delay_card + 's' + '" data-id-bantuan="' + element.id_bantuan + '"><!-- Start Single Service --><div class="p-4 single-service animation"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="' + element.path_gambar_medium + '" alt="' + element.nama_gambar_medium + '" class="img-fluid opacity" data-animation-name="zoom-in"><div class="service-logo shadow bg-white d-flex justify-content-center align-items-center p-2 position-absolute top right margin-right" data-animation-name="slide"><img src="' + element.path_gambar_pengaju + '" alt="' + element.pengaju_bantuan + '"></div><div class="service-icon shadow d-flex justify-content-center align-items-center position-absolute bottom left" data-animation-start="backward" data-animation-name="slide"' + ((element.warna) ? ' style="background-color:' + element.warna + '"' : '') + '>' + iconSektor(element.layanan) + '</div></div><h6 class="my-3 overflow-hidden title">' + element.nama_bantuan + '</h6><div class="row mb-2 align-items-end"><div class="col"><h6>Terkumpul</h6><p class="text-muted">Rp. ' + element.total_donasi + '</p></div><div class="col-auto text-end"><small>' + element.sisa_waktu + '</small></div></div><div class="progress"><div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + element.persentase_donasi_dilaksanakan + '" ' + 'style="width: ' + element.persentase_donasi_dilaksanakan + '%"' + '></div></div><a href="' + '/bantuan/detil/' + element.id_bantuan + '" class="mt-3"' + ((element.warna) ? ' style="color:' + element.warna + '"' : '') + '>Lihat lebih lanjut <i class="lni lni-arrow-right"></i></a></div><!-- End Single Service --></div>';
                        targetRow.insertAdjacentHTML('afterbegin', listBantuanCard);
                    });
                }

                if (result.feedback.removed_id != null) {
                    // remove recent showed list
                    result.feedback.removed_id = Object.values(result.feedback.removed_id);
                    result.feedback.removed_id.forEach(element => {
                        targetRow.querySelector('[data-id-bantuan="' + element + '"]').remove();
                    });
                }

                delay_card = null;
                result.feedback.data.forEach(element => {  
                    // show other old list
                    if (delay_card == null) {
                        delay_card = 2 * wowDelay;
                    } else {
                        delay_card = delay_card + wowDelay;
                    }
                    if (element.path_gambar_medium == null) {
                        element.path_gambar_medium = '';
                    }
                    if (element.nama_gambar_medium == null) {
                        element.nama_gambar_medium = 'Gambar '+element.nama_bantuan;
                    }
                    if (element.path_gambar_pengaju == null) {
                        element.path_gambar_pengaju = '';
                    }
                    let listBantuanCard = '<div class="col-lg-4 col-md-6 col-12 wow fadeInUp d-none" data-wow-delay="' + delay_card + 's' + '" data-id-bantuan="' + element.id_bantuan + '"><!-- Start Single Service --><div class="p-4 single-service animation"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="' + element.path_gambar_medium + '" alt="' + element.nama_gambar_medium + '" class="img-fluid opacity" data-animation-name="zoom-in"><div class="service-logo shadow bg-white d-flex justify-content-center align-items-center p-2 position-absolute top right margin-right" data-animation-name="slide"><img src="' + element.path_gambar_pengaju + '" alt="' + element.pengaju_bantuan + '"></div><div class="service-icon shadow d-flex justify-content-center align-items-center position-absolute bottom left" data-animation-start="backward" data-animation-name="slide"' + ((element.warna) ? ' style="background-color:' + element.warna + '"' : '') + '>' + iconSektor(element.layanan) + '</div></div><h6 class="my-3 overflow-hidden title">' + element.nama_bantuan + '</h6><div class="row mb-2 align-items-end"><div class="col"><h6>Terkumpul</h6><p class="text-muted">Rp. ' + element.total_donasi + '</p></div><div class="col-auto text-end"><small>' + element.sisa_waktu + '</small></div></div><div class="progress"><div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + element.persentase_donasi_dilaksanakan + '" ' + 'style="width: ' + element.persentase_donasi_dilaksanakan + '%"' + '></div></div><a href="' + '/bantuan/detil/' + element.id_bantuan + '" class="mt-3"' + ((element.warna) ? ' style="color:' + element.warna + '"' : '') + '>Lihat lebih lanjut <i class="lni lni-arrow-right"></i></a></div><!-- End Single Service --></div>';
                    targetRow.insertAdjacentHTML('beforeend', listBantuanCard);
                });

                setTimeout(() => {
                    let newCols = targetRow.querySelectorAll('.d-none');
                    newCols.forEach(element => {
                        element.classList.remove('d-none');
                        wow.show(element);
                    });
                }, 0);

            } else {
                data = {};
                // Error
            }
            document.querySelector('body').setAttribute('data-token', result.token);
            fetchTokenChannel.postMessage({
                token: body.getAttribute('data-token')
            });

            if (!data.load_more) {
                loadMoreBtn.parentElement.remove();
            }
        });
    };

    let pathname = window.location.pathname;
    
    const limit = loadMoreBtn.getAttribute('data-limit'),
          offset = loadMoreBtn.getAttribute('data-offset'),
          urlLoadMoreBtn = '/fetch/read/bantuan/kategori/' + pathname.split('/').at(-1),
          targetRow = document.getElementById('list-kategori-row');

    const wowDelay = parseFloat(targetRow.getAttribute('data-delay'));
    
    loadMoreBtn.addEventListener('click', function() {
        if (data.limit != undefined && data.load_more == true && parseInt(data.record) > (parseInt(data.offset) + parseInt(data.limit))) {
            data.offset = parseInt(data.offset) + parseInt(data.limit);
        } else if (data.limit == undefined) {
            // Load More First After Load
            data.offset = parseInt(offset) + parseInt(limit);
            data.limit = limit;
        } else {
            return false;
        }
        data.list_id = btoa(JSON.stringify(listId));
        data.token = document.querySelector('body').getAttribute('data-token');
        fetchData(urlLoadMoreBtn, data, targetRow);
    });

    // set list id_bantuan first on load
    listId = JSON.parse(atob(decodeURIComponent(targetRow.getAttribute('data-list-id'))));
}