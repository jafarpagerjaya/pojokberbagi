let resetTab = false,
    resetNotATab = false,
    resetMob = false,
    resetCoLep = false,
    lastWidth,
    lock = false;

function wowReset() {
    const delay = 0.2,
          elTargetRowColTextImgList = document.querySelectorAll('.hero-area .container>.row'),
          elTargetRowCustomColList = document.querySelectorAll('.custom-height-setter');

    let h = 0;
    elTargetRowColTextImgList.forEach(elTargetRowColTextImg => {
        const elTargetRowColList = elTargetRowColTextImg.querySelectorAll('.wow-delay-shuffle-parent');

        let newOrder,
            firstOrder = [],
            secondOrder = [],
            start = parseFloat(elTargetRowColTextImg.getAttribute('data-start-delay-wow'));
        
        elTargetRowColList.forEach(elTargetRowCol => {
            let elmentComputedStyle = getComputedStyle(elTargetRowCol);

            if (!firstOrder.length || !secondOrder.length) {
                if (elmentComputedStyle.getPropertyValue('order') == 0) {
                    firstOrder.push(elTargetRowCol);
                } else {
                    secondOrder.push(elTargetRowCol);
                }
            }
        });

        let reset = false;
        
        if (detectTab()) {
            newOrder = firstOrder.concat(secondOrder);
            if (!resetTab) {
                resetNotATab = reset;
                reset = true;
                resetTab = reset;
                lastWidth = window.innerWidth;
                lock = false;
            }
            if (h > 0 && resetTab && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (h == elTargetRowColTextImgList.length-1) {
                lock = true;
            }
        } else {
            if (!resetNotATab) {
                resetTab = reset;
                reset = true;
                resetNotATab = reset;
                lastWidth = window.innerWidth;
                lock = false;
            }
            if (h > 0 && resetNotATab && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (h == elTargetRowColTextImgList.length-1) {
                lock = true;
            }
            newOrder = secondOrder.concat(firstOrder);
        }
        
        if (reset) {
            newOrder.forEach(elCol => {
                elCol.querySelector('.wow').setAttribute('data-wow-delay', start+'s');
                elCol.querySelector('.wow').style.animationDelay = start+'s';
                start += delay;
            }); 
        }

        h++;
    });

    let i = 0;
    elTargetRowCustomColList.forEach(elTargetRowCustomCol => {
        let start = parseFloat(elTargetRowCustomCol.getAttribute('data-start-delay-wow')),
            newOrder,
            firstOrder = [],
            secondOrder = [];
        
        if (!firstOrder.length || !secondOrder.length) {
            let colBox = elTargetRowCustomCol.querySelectorAll('.box.wow');
    
            colBox.forEach(elCol => {
                if (getComputedStyle(elCol).order == 0) {
                    firstOrder.push(elCol);
                } else {
                    secondOrder.push(elCol);
                }
            });
        }

        let reset = false;

        if (detectTab() && !detectMob()) {
            newOrder = firstOrder.concat(secondOrder);
            resetCoLep = reset;
            reset = true;
            resetMob = false;
            lastWidth = window.innerWidth;
            lock = false;

            if (i > 0 && resetMob && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (i == elTargetRowCustomColList.length-1) {
                lock = true;
            }
        } else if (detectMob()) {
            newOrder = firstOrder.concat(secondOrder);
            if (!resetMob) {
                resetCoLep = reset;
                reset = true;
                resetMob = reset;
                lastWidth = window.innerWidth;
                lock = false;
            }
            if (i > 0 && resetMob && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (i == elTargetRowCustomColList.length-1) {
                lock = true;
            }
        } else {
            if (!resetCoLep) {
                resetMob = reset;
                reset = true;
                resetCoLep = reset;
                lastWidth = window.innerWidth;
                lock = false;
            }
            if (i > 0 && resetCoLep && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (i == elTargetRowCustomColList.length-1) {
                lock = true;
            }
            newOrder = secondOrder.concat(firstOrder);
        }

        if (reset) {
            newOrder.forEach(elCol => {
                elCol.setAttribute('data-wow-delay', start+'s');
                elCol.style.animationDelay = start+'s';
                start += delay;
            }); 
        }

        i++;
    });
};

function setBeforeAfterBgColor(el) {
    setTimeout(() => {
        const elTargetImgList = el.querySelectorAll('a>img');

        elTargetImgList.forEach(elTargetImg => {
            let canvas = document.createElement('canvas');

            canvas.width = elTargetImg.width;
            canvas.height = elTargetImg.height;
            canvas.getContext('2d').drawImage(elTargetImg, 0, 0, elTargetImg.width, elTargetImg.height);

            let rgbaBefore = canvas.getContext('2d').getImageData(3, elTargetImg.height-3, 1, 1).data.join(),
                rgbaAfter = canvas.getContext('2d').getImageData(elTargetImg.width-3, elTargetImg.height-3, 1, 1).data.join();
            
            elTargetImg.closest('.app-image').setAttribute('data-bg-color-before', rgbaBefore);
            elTargetImg.closest('.app-image').setAttribute('data-bg-color-after', rgbaAfter);

            elTargetImg.closest('.app-image').style.setProperty('--data-bg-color-before', 'rgba('+rgbaBefore+')');
            elTargetImg.closest('.app-image').style.setProperty('--data-bg-color-after', 'rgba('+rgbaAfter+')');
        });
    }, 0);
};

const heroArea = document.querySelector('.hero-area');

setBeforeAfterBgColor(heroArea);
setHeight(heroArea, detectMob());
wowReset();

let resizeTimeout;
function reportWindowWidth() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(()=> {
        setHeight(heroArea, detectMob());
        wowReset();
    }, 50);
};

window.addEventListener('resize', reportWindowWidth);

let buttonCollapse = document.querySelectorAll('.collapseButton'),
    buttonCollapseClicked;

buttonCollapse.forEach(elemButton => {
    elemButton.addEventListener('click', function (e) {
        if (!e.which || buttonCollapseClicked) {
            return false;
        }
        if (window.outerWidth < 768) {
            const elTargetList = document.querySelectorAll('.row.custom-height-setter'),
                  elTargetSetter = this.closest('.order-0');
            let i = 0,
                height;

            elTargetList.forEach(elTarget => {
                let elTargetSetterHeight;

                let span = buttonCollapse[i].children[0],
                    icon = buttonCollapse[i].children[1];

                if (!elTarget.classList.contains('toggle')) {
                    elTarget.classList.add('toggle');
                    span.innerText = "Sembunyikan detil";
                    icon.style.transform = "rotate(90deg)";
                    icon.style.transition = "transform .3s";
                    elTarget.setAttribute('style','height: '+elTarget.getAttribute('data-height'));
                } else {
                    elTarget.classList.remove('toggle');
                    span.innerText = "Lebih detil";
                    icon.style.transform = "rotate(0deg)";
                    elTargetSetterHeight = elTargetSetter.offsetHeight;
                    if (elTargetSetterHeight == 0) {
                        elTargetSetterHeight = height;
                    } else {
                        height = elTargetSetterHeight;
                    }
                    elTarget.setAttribute('style','height: '+elTargetSetterHeight+'px;');                
                }
                elTarget.querySelector('.collapseButton').classList.toggle('toggle');
                i++;
            });

            buttonCollapseClicked = e.which;
            setTimeout(() => {
                buttonCollapseClicked = false;
            }, 350);
        }
    });
});

const counterTarget = document.querySelectorAll('.carousel-item.active .box-info h6[data-count-up-value]'),
      progressBar = document.querySelectorAll('.carousel-item.active .progress-bar'),
      counterSpeed = 4000;

counterUpSup(counterTarget, counterSpeed);
counterUpProgress(progressBar, counterSpeed);

// Not Sure To Be Use Yet
// ###################################
let checkWOWJsReset = function () {
    let resetWOWJsAnimation = function (el, index) {
        let $that = el,
            brat;
        // determine if container is in viewport
        // you might pass an offset in pixel - a negative offset will trigger loading earlier, a postive value later
        // credits @ https://stackoverflow.com/a/33979503/2379196
        let isInViewport = function ($container) {
            const dataDelay = $container.closest('[data-start-delay-wow]');
            let viewportTop = document.documentElement.scrollTop,
                viewportBottom = viewportTop + window.innerHeight,
                container,
                containerTop,
                containerBottom;

            if (dataDelay != null) {
                container = dataDelay.getBoundingClientRect();
                containerTop = container.top;
                containerBottom = container.bottom;

                return containerBottom < 0 || viewportBottom < containerTop;
            }
        };

        if (isInViewport($that) != undefined) {
            brat = isInViewport($that);
        } else {
            return false;
        }

        if (brat == true && getComputedStyle($that).getPropertyValue('animation-name') != 'none' && !$that.classList.contains('animated')) {
            
            let dataBefore,
                dataAfter;

            if ($that.getAttribute('data-bg-color-before')) {
                dataBefore = $that.getAttribute('data-bg-color-before');
            }

            if ($that.getAttribute('data-bg-color-after')) {
                dataAfter = $that.getAttribute('data-bg-color-after');
            }
            $that.setAttribute(
                'style',
                'visibility: hidden; animation-name: none; animation-delay: '+$that.getAttribute('data-wow-delay')+';'+(dataBefore != undefined ? '--data-bg-color-before: rgba('+dataBefore+'); ' : '')+(dataAfter != undefined ? '--data-bg-color-after: rgba('+dataAfter+');' : '')
            );
            wow.addBox($that);
        }
    };
    let elWowList = document.querySelectorAll('.wow'),
        index = 0;
        elWowList.forEach(elWow => {
            resetWOWJsAnimation(elWow, index);
            index++;
        });
};

// window.addEventListener('scroll', checkWOWJsReset);
// window.addEventListener('resize', checkWOWJsReset);
// ################################### 

const bannerCarousel = document.getElementById('banner-carousel');
// Carousel BS 
bannerCarousel.addEventListener('slide.bs.carousel', function (e) {
    let progressBarCarouselActive = e.relatedTarget.querySelectorAll('.progress-bar'),
        counterTargetCarouselActive = e.relatedTarget.querySelectorAll('.box-info h6[data-count-up-value]'),
        counterSpeed = 2000;

    counterUpProgress(progressBarCarouselActive, counterSpeed);
    counterUpSup(counterTargetCarouselActive, counterSpeed);
    doAnimations(e.relatedTarget.querySelectorAll('.wow'));
});

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

const loadMoreBtn = document.getElementById('load-more');
if (loadMoreBtn != null) {
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

                // show recent newest list
                if (result.feedback.newest_data != null) {
                    result.feedback.newest_data.forEach(element => {  
                        if (delay_card == null) {
                            delay_card = wowDelay * 2;
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
                        let listBantuanCard = '<div class="col-lg-4 col-md-6 col-12 wow fadeInUp d-none" data-wow-delay="' + delay_card + 's' + '"><!-- Start Single Service --><a class="p-4 single-service animation d-block" href="' + '/bantuan/detil/' + element.id_bantuan + '"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="' + element.path_gambar_medium + '" alt="' + element.nama_gambar_medium + '" class="img-fluid opacity" data-animation-name="zoom-in"><div class="service-logo shadow bg-white d-flex justify-content-center align-items-center p-2 position-absolute top left margin-left" data-animation-name="slide"><img src="' + element.path_gambar_pengaju + '" alt="' + element.pengaju_bantuan + '"></div><div class="service-icon shadow d-flex justify-content-center align-items-center position-absolute bottom left" data-animation-name="slide"' + ((element.warna) ? ' style="background-color:' + element.warna + '"' : '') + '>' + iconSektor(element.layanan) + '</div></div><h6 class="my-3 overflow-hidden title">' + element.nama_bantuan + '</h6><div class="row mb-2 align-items-end"><div class="col"><h6>Terkumpul</h6><p class="text-muted">Rp. ' + element.total_donasi + '</p></div><div class="col-auto text-end"><small>' + element.sisa_waktu + '</small></div></div><div class="progress"><div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + element.persentase_donasi_dilaksanakan + '" ' + 'style="width: ' + element.persentase_donasi_dilaksanakan + '%"' + '></div></div></a><!-- End Single Service --></div>';
                        targetRow.insertAdjacentHTML('afterbegin', listBantuanCard);
                    });
                }

                // remove recent showed list
                if (result.feedback.removed_id != null) {
                    result.feedback.removed_id = Object.values(result.feedback.removed_id);
                    result.feedback.removed_id.forEach(element => {
                        targetRow.querySelector('[data-id-bantuan="' + element + '"]').remove();
                    });
                }

                delay_card = null;
                // show other old list
                result.feedback.data.forEach(element => {  
                    if (delay_card == null) {
                        delay_card = wowDelay * 2;
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
                    let listBantuanCard = '<div class="col-lg-4 col-md-6 col-12 wow fadeInUp d-none" data-wow-delay="' + delay_card + 's' + '"><!-- Start Single Service --><a class="p-4 single-service animation d-block" href="' + '/bantuan/detil/' + element.id_bantuan + '"><div class="app-image img-wrapper rounded-box top overflow-hidden d-flex justify-content-center align-items-center position-relative mb-3"><img src="' + element.path_gambar_medium + '" alt="' + element.nama_gambar_medium + '" class="img-fluid opacity" data-animation-name="zoom-in"><div class="service-logo shadow bg-white d-flex justify-content-center align-items-center p-2 position-absolute top left margin-left" data-animation-name="slide"><img src="' + element.path_gambar_pengaju + '" alt="' + element.pengaju_bantuan + '"></div><div class="service-icon shadow d-flex justify-content-center align-items-center position-absolute bottom left" data-animation-name="slide"' + ((element.warna) ? ' style="background-color:' + element.warna + '"' : '') + '>' + iconSektor(element.layanan) + '</div></div><h6 class="my-3 overflow-hidden title">' + element.nama_bantuan + '</h6><div class="row mb-2 align-items-end"><div class="col"><h6>Terkumpul</h6><p class="text-muted">Rp. ' + element.total_donasi + '</p></div><div class="col-auto text-end"><small>' + element.sisa_waktu + '</small></div></div><div class="progress"><div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="' + element.persentase_donasi_dilaksanakan + '" ' + 'style="width: ' + element.persentase_donasi_dilaksanakan + '%"' + '></div></div></a><!-- End Single Service --></div>';
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

    const limit = loadMoreBtn.getAttribute('data-limit'),
          offset = loadMoreBtn.getAttribute('data-offset'),
          urlLoadMoreBtn = '/fetch/read/bantuan/list',
          targetRow = document.getElementById('list-bantuan-row');

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