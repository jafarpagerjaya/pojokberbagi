let buttonCollapse = document.querySelectorAll('.collapseButton');

buttonCollapse.forEach(elemButton => {
    elemButton.addEventListener('click', function (e) {
        if (!e.which) {
            return false;
        }
        if (window.outerWidth < 768) {
            const elTargetList = document.querySelectorAll('.row.custom-height-setter'),
                  elTargetSetter = this.closest('.order-0');
            let i = 0,
                height;

            elTargetList.forEach(elTarget => {
                let elTargetSetterHeight,
                    elTargetSetterHeightPTNext = elTarget.nextElementSibling;

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
                    elTargetSetterHeight = elTargetSetter.offsetHeight + parseFloat(getComputedStyle(elTargetSetterHeightPTNext).paddingTop);
                    if (elTargetSetterHeight == 0) {
                        elTargetSetterHeight = height;
                    } else {
                        height = elTargetSetterHeight;
                    }
                    elTarget.setAttribute('style','height: '+elTargetSetterHeight+'px;');                
                }
                this.classList.toggle('toggle');
                i++;
            });

            click = e.which;
            setTimeout(() => {
                click = false;
                stickyBtnOffsetTopYStart = sticky_btn.offsetTop + sticky_btn_height + sticky_btn_area.offsetTop;
            }, 350);
        }
    });
});

let resetTab = false,
    resetNotATab = false,
    resetMob = false,
    resetCoLep = false;

function wowReset() {
    const delay = 0.2,
          elTargetCBAList = document.querySelectorAll('#commit-bantuan-area>.wow'),
          elTargetRowCustomColList = document.querySelectorAll('.custom-height-setter');
    // ColBox
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
        } else if (detectMob()) {
            newOrder = firstOrder.concat(secondOrder);
            if (!resetMob) {
                resetCoLep = reset;
                reset = true;
                resetMob = reset;
            } else {
                reset = false;
            }
        } else {
            if (!resetCoLep) {
                resetMob = reset;
                reset = true;
                resetCoLep = reset;
            }
            newOrder = secondOrder.concat(firstOrder);
        }

        if (reset) {
            newOrder.forEach(elCol => {
                elCol.setAttribute('data-wow-delay', start+'s');
                start += delay;
            }); 
        }
    });
    
    let start,
        newOrder,
        firstOrder = [],
        secondOrder = [];
    // Commit Bantuan Area
    elTargetCBAList.forEach(elCba => {
        if (!firstOrder.length || !secondOrder.length) {
            start = parseFloat(elCba.closest('[data-start-delay-wow]').getAttribute('data-start-delay-wow'));
    
            if (elCba.classList.contains('order-first')) {
                firstOrder.push(elCba);
            } else {
                secondOrder.push(elCba);
            }
        }
    });

    let reset = false;
        
    if (detectMob()) {
        newOrder = firstOrder.concat(secondOrder);
        if (!resetTab) {
            resetNotATab = reset;
            reset = true;
            resetTab = reset;
        }
    } else {
        if (!resetNotATab) {
            resetTab = reset;
            reset = true;
            resetNotATab = reset;
        }
        newOrder = secondOrder.concat(firstOrder);
    }
    
    if (reset) {
        newOrder.forEach(elCol => {
            elCol.setAttribute('data-wow-delay', start+'s');
            start += delay;
        }); 
    }
    
};

const dBArea = document.getElementById('detil-banner-area'),
      reverse = true;

setHeight(dBArea, detectMob(), reverse);
wowReset();

let resizeTimeout;
function reportWindowWidth() {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(()=> {
        setHeight(dBArea, detectMob(), reverse);
        wowReset();
        if (detectMob()) {
            stickyBtn();
        } else {
            sticky_btn_area.classList.remove('sticky-btn');
        }
    }, 50);
};

window.addEventListener('resize', reportWindowWidth);

const counterTarget = document.querySelectorAll('.box-info h6[data-count-up-value]'),
      progressBar = document.querySelectorAll('.progress-bar'),
      counterSpeed = 4000;

counterUpSup(counterTarget, counterSpeed);
counterUpProgress(progressBar, counterSpeed);

const c_id_bantuan = window.location.pathname.split('/').at(3);

let data = {
    id_bantuan: c_id_bantuan,
    token: body.getAttribute('data-token')
};

fetch('/default/fetch/read/bantuan/deskripsi', {
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
    // console.log(response);
    document.querySelector('body').setAttribute('data-token', response.token);
    fetchTokenChannel.postMessage({
        token: document.querySelector('body').getAttribute('data-token')
    });

    if (!response.error && response.feedback.data.length) {
        const quill = new Quill('#selengkapnya', {
            modules: {
                toolbar: false
            },
            readOnly: true
        });

        // render the content
        quill.setContents(JSON.parse(response.feedback.data));    
    }

    if (response.toast != null && response.toast.feedback != undefined && response.toast.feedback.message != undefined) {
        createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
    
        $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
            'autohide': true
        }).toast('show');
    }

    data = {};
});

if (document.querySelector('.timeline') != null) {
    document.querySelectorAll('.timeline-item').forEach(tl => {
        const quill = new Quill(tl.querySelector('.editor-read'), {
            modules: {
                toolbar: false
            },
            readOnly: true
        });

        // render the content
        quill.setContents(JSON.parse(tl.querySelector('.editor-read').innerText));

        setTimeout(() => {
            if (tl.querySelector('.editor-read').clientHeight >= 200) {
                tl.querySelector('.editor-read').classList.add('hidden-area-utility','light');
            } else {
                if (tl.querySelector('a[data-bs-target="#modalDetilUpdate"]') != null) {
                    tl.querySelector('a[data-bs-target="#modalDetilUpdate"]').remove();
                }
            }
        }, 0);
    });
}

// Get all share buttons
const shareButtons = document.querySelectorAll('.share a.medsos-icon');

// Add click event listener to each button
shareButtons.forEach(button => {
   button.addEventListener('click', (e) => {
        let uTarget = window.location.href;
        // Get the target url by related modal
        if (relatedModal != null) {
            if (objectInformasi.id_informasi == relatedModal.getAttribute('data-id-informasi')) {
                uTarget = uTarget + '/informasi/' + objectInformasi.id_informasi;
            }
        }
        // Get the URL of the current page
        const url = uTarget;

        console.log(url);

        // Get the social media platform from the button's class name
        const platform = button.children[0].classList[1];

        // Set the URL to share based on the social media platform
        let shareUrl;
        
        switch (platform) {
            //  case 'facebook':
            //  shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
            //  break;
            case 'bi-twitter-x':
            shareUrl = `https://twitter.com/share?url=${encodeURIComponent(url)}`;
            break;
            //  case 'linkedin':
            //  shareUrl = `https://www.linkedin.com/shareArticle?url=${encodeURIComponent(url)}`;
            //  break;
            case 'bi-whatsapp':
            shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`;
            break;
        }

    //   Open a new window to share the URL
      window.open(shareUrl, '_blank');
   });
});

let sticky_ba_el;
if (document.querySelector('.btn.button.donasi') != null) {
    sticky_ba_el = document.querySelector('.btn.button.donasi').parentElement;
} else {
    sticky_ba_el = document.querySelector('#commit-bantuan-area>.col:last-child').parentElement;
}

const header_navbar = document.querySelector(".navbar-area"),
    sticky_btn_area = sticky_ba_el,
    sticky_btn = sticky_ba_el.closest('#commit-bantuan-area');
let sticky_btn_height = sticky_btn_area.offsetHeight,
    header_navbar_height = header_navbar.offsetHeight,
    stickyBtnOffsetTopY,
    stickyBtnOffsetTopYStart;

function stickyBtn(e) {
    if (window.outerWidth >= 768) {
        return false;
    }

    let windowScrollY = window.scrollY,
        windowScrollNavbarBottom = windowScrollY + header_navbar_height;
        stickyBtnOffsetTopY = sticky_btn.offsetTop + sticky_btn_height + sticky_btn_area.offsetTop;
        // console.log(windowScrollY, windowScrollNavbarBottom, stickyBtnOffsetTopY);
    if (windowScrollNavbarBottom >= stickyBtnOffsetTopY || windowScrollNavbarBottom >= stickyBtnOffsetTopYStart) {
        if (!sticky_btn_area.classList.contains('sticky-btn')) {
            sticky_btn_area.classList.add('sticky-btn');
        }
    } else {
        sticky_btn_area.classList.remove('sticky-btn');
    }
    // console.log(windowScrollNavbarBottom, stickyBtnOffsetTopY, windowScrollY);
}


setTimeout(()=>{
    stickyBtnOffsetTopYStart = sticky_btn.offsetTop + sticky_btn_height + sticky_btn_area.offsetTop;
    stickyBtn();
}, 50);

let lastKnownScrollPosition = 0;
let ticking = false;
let scrollingDoc = false;

function doStickyBtn(scrollPos) {
    // Do something with the scroll position
    if (window.outerWidth >= 768) {
        return false;
    }

    let windowScrollY = scrollPos,
        windowScrollNavbarBottom = windowScrollY + header_navbar_height;
        stickyBtnOffsetTopY = sticky_btn.offsetTop + sticky_btn_height + sticky_btn_area.offsetTop;
    if (windowScrollNavbarBottom >= stickyBtnOffsetTopY || windowScrollNavbarBottom >= stickyBtnOffsetTopYStart) {
        if (!sticky_btn_area.classList.contains('sticky-btn')) {
            sticky_btn_area.classList.add('sticky-btn');
        }
    } else {
        sticky_btn_area.classList.remove('sticky-btn');
    }
}

document.addEventListener("scroll", (event) => {
  lastKnownScrollPosition = window.scrollY;

  if (!ticking) {
    window.requestAnimationFrame(() => {
      doStickyBtn(lastKnownScrollPosition);
      ticking = false;
      if (scrollingDoc == false) {
        scrollingDoc = true;
      }
    });

    ticking = true;
  }
});

let popUpScrollLast = function(event) {
    if (scrollingDoc) {
        if (!pop.shown) {
            pop.start(event);
        }
    }
}

let popUpScrollFirst = function(event) {
    if (scrollingDoc) {
        if (!pop.shown) {
            pop.pause(event);
        }
    }
}

document.addEventListener("scroll", debounceIgnoreLast(popUpScrollFirst, 2000));

document.addEventListener("scroll", debounceIgnoreFirst(popUpScrollLast, 2000));

const modalShare = document.getElementById('modalShareBtn');
const myShareModal = new bootstrap.Modal(modalShare);

let relatedModal;

modalShare.addEventListener('hidden.bs.modal', function (e) {
    if (document.getElementById('modalDetilUpdate').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    } else if (document.getElementById('modalListDonatur').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
});

const modalDonaturList = document.getElementById('modalListDonatur');
const myModal = new bootstrap.Modal(modalDonaturList);
modalDonaturList.addEventListener('show.bs.modal', function (e) {
    e.target.classList.add('load');
    document.querySelectorAll('#donatur-area .donatur').forEach(ele => {
        e.target.querySelector('.modal-body #content').appendChild(ele.cloneNode(true));
    });

    let data = {
        'token': body.getAttribute('data-token'),
        'fields': {
            'id_bantuan': c_id_bantuan
        }
    };
    // fetchReadDonatur()
    fetchData('/default/fetch/read/detil-bantuan/donatur', data, e.target, 'read-donatur-list');
});

modalDonaturList.addEventListener('shown.bs.modal', function (e) {
    e.target.classList.remove('load');
    setTimeout(() => {
        e.target.classList.add('shown');
    }, 800);

    let box = e.target.querySelector('.modal-body');
    console.log(box.clientHeight, this.clientHeight, e.target.querySelector('.modal-header').offsetHeight, this.clientHeight - e.target.querySelector('.modal-header').offsetHeight);
    if (box.clientHeight >= this.clientHeight - e.target.querySelector('.modal-header').offsetHeight && this.clientWidth > 991) {
        // sementara nanti dihilangkan jika scroll sudah tanpa take width
        e.target.querySelector('.modal-header .row .col-lg-6').setAttribute('style', 'margin-right: 1rem !important; width: calc(50% - 1em) !important;');
    }
});

modalDonaturList.addEventListener('hidden.bs.modal', function (e) {
    e.target.querySelectorAll('.donatur').forEach(ele => {
        ele.remove();
    });
    e.target.classList.remove('shown');
    e.target.querySelector('.modal-header .row .col-lg-6').removeAttribute('style');
});

const donaturArea = document.getElementById('donatur-area');
modalDonaturList.addEventListener('hide.bs.modal', function (e) {

    let data = {
        'token': body.getAttribute('data-token'),
        'fields': {
            'id_bantuan': c_id_bantuan,
            'limit': 3,
            'offset': 0
        }
    };
    // fetchReadDonaturDefault()
    fetchData('/default/fetch/read/detil-bantuan/donatur', data, donaturArea, 'read-donatur-list-default');

    objectListDonatur = {
        offset: 0,
        limit: 10,
        total: 0
    };
});

// myModal.show();

let fetchData = function (url, data, root, f) {
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
        body.setAttribute('data-token', response.token);
        fetchTokenChannel.postMessage({
            token: body.getAttribute('data-token')
        });

        if (response.error) {
            createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                'autohide': false
            }).toast('show');
            return false;
        }

        switch (f) {
            case 'liked-click':
                fetchLikeClicked(url, data, root, f, response);
            break;
            case 'read-donatur-list':
                fetchReadDonatur(root, response);
            break;
            case 'read-donatur-list-default':
                fetchReadDonaturDefault(root, response);
            break;
            case 'get-informasi-berita':
                objectInformasi.id_informasi = data.fields.id_informasi;
                fetchGetInformasiBerita(root, response);
            break;
            case 'read-informasi':
                fetchReadInformasi(root, response);
            break;
            default:
            break;
        }
    });
};

let clickLiked = function(e) {
    let data = {
        'checked': e.target.getAttribute('checked'),
        'token': body.getAttribute('data-token'),
        'fields': {
            'id_donasi': e.target.closest('.donatur').getAttribute('data-id-donasi'),
            'id_bantuan': c_id_bantuan
        }
    };
    
    if (e.target.getAttribute('checked') != null) {
        // unckeck now
        // delete
        data.mode = 'delete';
    } else {
        // check now
        // create
        data.mode = 'create';
    }

    console.log(data);

    // fetchLikeClicked
    fetchData('/default/fetch/' + data.mode + '/amin', data, e, 'liked-click');
    // console.log('/default/fetch/' + data.mode + '/amin');
};

let delayTimer;
modalDonaturList.addEventListener('click', function(e) {
    if (e.target.classList.contains('heart-animation') && e.target.tagName == 'SPAN') {
        if (!delayTimer) {
            clickLiked(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
        }, 500);
    }
});

donaturArea.addEventListener('click', function(e) {
    if (e.target.classList.contains('heart-animation') && e.target.tagName == 'SPAN') {
        if (!delayTimer) {
            clickLiked(e);
        }
        clearTimeout(delayTimer);
        delayTimer = setTimeout(() => {
            delayTimer = undefined;
        }, 500);
    }
});

let fetchLikeClicked = function(url, data, e, f, response) {
    if (response.feedback != null) {
        if (response.feedback.local_storage_client) {
            if (localStorage.getItem("client-pojokberbagi")) {
                // If local ada
                setCookie('client-pojokberbagi', localStorage.getItem("client-pojokberbagi"), 365, '/');
                // console.log('local storage ada isinya diset cookie => ' + localStorage.getItem("client-pojokberbagi"));
            } else {
                // Local tidak ada do setKunjungan()
                setTimeout(()=>{
                    let uri = window.location.href,
                        ur = response.feedback.uri;
                    if (ur) {
                        uri = uri.replace(/\/$/, '');
                        let realPath = atob(ur);
                        $.post(
                            '/home/kunjungan', 
                            {uri : uri, path : realPath},
                            function(data, success) {
                                if (success) {
                                    localStorage.setItem("client-pojokberbagi", decodeURIComponent(getCookie('client-pojokberbagi')));
                                    // console.log('kunjungan isi local storage ambil dari cookie => ' + decodeURIComponent(getCookie('client-pojokberbagi')));
                                }
                            }
                        );
                    }
                }, 0);
            }

            data.token = response.token;
            fetchData(url, data, e, f, response);
        }
    }

    e.target.classList.toggle('animate');
    if (e.target.getAttribute('checked') != null) {
        // unckeck now
        e.target.removeAttribute('checked');
    } else {
        // check now
        e.target.setAttribute('checked', true);
    }
    e.target.nextElementSibling.innerText = response.feedback.data.liked + ' Disukai';
};

let fetchReadDonatur = function(modal, response) {
    if (objectListDonatur.offset == 0) {
        modal.querySelectorAll('.donatur').forEach(ele => {
            ele.remove();
        });
    } else {
        modal.querySelector('.donatur.load').remove();
        hideLoader(modal.querySelector('#content'));
    }

    response.feedback.data.list_donatur.forEach(donasi => {
        const elDonasi = '<div class="donatur col-12 bg-light rounded-box p-3 d-flex justify-content-between gap-2" data-id-donasi="'+ reverseString(btoa(donasi.id_donasi)) +'"><div class="media d-flex gap-3 flex-column"><div class="avatar border rounded"><img src="'+ donasi.path_avatar +'" alt="'+ donasi.nama_avatar +'" class="avatar"></div><div class="media-body"><h5 class="mt-0"><span>' + donasi.nama_donatur + '</span></h5>'+ (donasi.doa != null ? '<p class="desc">'+ donasi.doa +'</p>' : '') +'</div></div><div class="row w-100 g-0 align-items-end"><div class="col"><p class="text-black-50 text-decoration-underline">Donasi</p><span class="fw-bold">'+ donasi.jumlah_donasi +'</span></div><div class="col d-flex justify-content-end align-items-center"><div class="d-flex flex-column align-items-center gap-1">'+ (+donasi.checked ? '<span class="heart-animation animate" checked="true"></span>':'<span class="heart-animation"></span>') +'<span class="liked text-orange">'+ donasi.liked +' Disukai</span></div></div></div></div>';
        modal.querySelector('.modal-body #content').insertAdjacentHTML('beforeend', elDonasi);
    });

    let feedbackData = response.feedback.data;
    objectListDonatur = {
        limit: feedbackData.limit,
        total: feedbackData.jumlah_record,
        offset: feedbackData.offset
    }
};

let fetchReadDonaturDefault = function(target, response) {
    target.querySelectorAll('.donatur').forEach(el => {
        el.remove();
    });

    target.classList.add('shown');

    response.feedback.data.list_donatur.forEach(donasi => {
        const elDonasi = '<div class="donatur col-12 bg-light rounded-box p-3 d-flex justify-content-between gap-2" data-id-donasi="'+ reverseString(btoa(donasi.id_donasi)) +'"><div class="media d-flex gap-3 flex-column"><div class="avatar border rounded"><img src="'+ donasi.path_avatar +'" alt="'+ donasi.nama_avatar +'" class="avatar"></div><div class="media-body"><h5 class="mt-0"><span>' + donasi.nama_donatur + '</span></h5>'+ (donasi.doa != null ? '<p class="desc">'+ donasi.doa +'</p>' : '') +'</div></div><div class="row w-100 g-0 align-items-end"><div class="col"><p class="text-black-50 text-decoration-underline">Donasi</p><span class="fw-bold">'+ donasi.jumlah_donasi +'</span></div><div class="col d-flex justify-content-end align-items-center"><div class="d-flex flex-column align-items-center gap-1">'+ (+donasi.checked ? '<span class="heart-animation animate" checked="true"></span>':'<span class="heart-animation"></span>') +'<span class="liked text-orange">'+ donasi.liked +' Disukai</span></div></div></div></div>';
        target.insertAdjacentHTML('beforeend', elDonasi);
    });

    setTimeout(() => {
        target.classList.remove('shown');
    }, 800);
};

let fetchReadInformasi = function(modal, response) {
    if (modal.querySelector('#content') == null) {
        let currentDate = new Date(),
            timestamp = currentDate.getTime(); 

        let invalid = {
            error: true,
            data_toast: 'invalid-element-feedback',
            feedback: {
                message: 'Element #content tidak ditemukan'
            }
        };

        invalid.id = invalid.data_toast +'-'+ timestamp;
        
        createNewToast(document.querySelector('[aria-live="polite"]'), invalid.id, invalid.data_toast, invalid);
        $('#'+ invalid.id +'.toast[data-toast="'+ invalid.data_toast +'"]').toast({
            'delay': 10000
        }).toast('show');
        return false;
    }

    objectInformasi = response.feedback;

    if (objectInformasi.offset == 0) {
        modal.querySelectorAll('.timeline .timeline-item').forEach(ele => {
            ele.remove();
        });
    } else {
        if (modal.querySelector('.timeline .timeline-item.load') != null) {
            modal.querySelector('.timeline .timeline-item.load').remove();
        }
        if (modal.querySelector('#content.loader-animation') != null) {
            hideLoader(modal.querySelector('#content'));
        }
        if (modal.querySelector('#content .timeline-item.next') != null) {
            modal.querySelector('#content .timeline-item.next').classList.remove('next');
        }
    }

    const data = response.feedback.data;
    
    if (modal.classList.contains('modal')) {
        modal.classList.add('load');
    }

    // show recent newest list
    if (response.feedback.newest_data != null) {
        response.feedback.newest_data.forEach(row => { 
            let timelineEl = '<div class="col-12 timeline-item px-0" data-id-informasi="' + reverseString(btoa(row.id_informasi)) + '"><div class="row m-0 w-100"><div class="col-12 col-lg-2"><div class="time small fw-bold"><a role="button" href="javascript:void(0);" class="text-secondary" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="date" data-date-value="'+ row.tanggal_publikasi +'"><p><span>' + row.waktu_publikasi + '</span></p></a></div><a role="button" href="javascript:void(0);" data-label-value="'+ row.label +'" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="label" class="text-capitalize badge' + (typeof labelInformasi(row.label) == 'object' ? ' ' + labelInformasi(row.label).class : '') + '">' + (labelInformasi(row.label).text != null ? labelInformasi(row.label).text : '') + '</a></span></div><div class="col-12 col-lg content flex-column d-flex"><b><span>' + row.judul + '</span></b><div class="editor-read">' + row.isi + '</div><div><a role="button" href="javascript:void(0);" class="text-decoration-underline" data-bs-target="#modalDetilUpdate">Lebih detil <i class="lni lni-chevron-right"></i></a></div></div></div></div>';
            modal.querySelector('#content').insertAdjacentHTML('afterbegin', timelineEl);

            const quill = new Quill(modal.querySelector('[data-id-informasi="'+ reverseString(btoa(row.id_informasi)) +'"] .editor-read'), {
                modules: {
                    toolbar: false
                },
                readOnly: true
            });

            quill.setContents(JSON.parse(modal.querySelector('[data-id-informasi="'+ reverseString(btoa(row.id_informasi)) +'"] .editor-read').innerText));
        });
    }

    // remove recent showed list
    if (response.feedback.removed_id != null) {
        response.feedback.removed_id = Object.values(response.feedback.removed_id);
        response.feedback.removed_id.forEach(id => {
            modal.querySelector('[data-id-informasi="' + reverseString(btoa(id)) + '"]').remove();
        });
    }

    data.forEach(row => {
        let timelineEl = '<div class="col-12 timeline-item px-0" data-id-informasi="' + reverseString(btoa(row.id_informasi)) + '"><div class="row m-0 w-100"><div class="col-12 col-lg-2"><div class="time small fw-bold"><a role="button" href="javascript:void(0);" class="text-secondary" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="date" data-date-value="'+ row.tanggal_publikasi +'"><p><span>' + row.waktu_publikasi + '</span></p></a></div><a role="button" href="javascript:void(0);" data-label-value="'+ row.label +'" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="label" class="text-capitalize badge' + (typeof labelInformasi(row.label) == 'object' ? ' ' + labelInformasi(row.label).class : '') + '">' + (labelInformasi(row.label).text != null ? labelInformasi(row.label).text : '') + '</a></span></div><div class="col-12 col-lg content flex-column d-flex"><b><span>' + row.judul + '</span></b><div class="editor-read">' + row.isi + '</div><div><a role="button" href="javascript:void(0);" class="text-decoration-underline" data-bs-target="#modalDetilUpdate">Lebih detil <i class="lni lni-chevron-right"></i></a></div></div></div></div>';
        modal.querySelector('#content').insertAdjacentHTML('beforeend', timelineEl);

        const dataInformasi = modal.querySelector('[data-id-informasi="'+ reverseString(btoa(row.id_informasi)) +'"]');
        const quill = new Quill(dataInformasi.querySelector('.editor-read'), {
            modules: {
                toolbar: false
            },
            readOnly: true
        });

        // render the content
        quill.setContents(JSON.parse(dataInformasi.querySelector('.editor-read').innerText));

        setTimeout(() => {
            if (dataInformasi.querySelector('.editor-read').clientHeight >= 200) {
                dataInformasi.querySelector('.editor-read').classList.add('hidden-area-utility','light');
            } else {
                if (dataInformasi.querySelector('a[data-bs-target="#modalDetilUpdate"]') != null) {
                    dataInformasi.querySelector('a[data-bs-target="#modalDetilUpdate"]').remove();
                }
            }
        }, 0);
    });

    if (objectInformasi.total_record != modal.querySelectorAll('#content .timeline-item').length) {
        modal.querySelector('#content .timeline-item:last-of-type').classList.add('next');
    }

    if (modal.classList.contains('modal')) {
        setTimeout(() => {
            modal.classList.remove('load');
        }, 500);
    }
};

let objectInformasi = {};
let fetchGetInformasiBerita = function(modal, response) {
    const data = response.feedback.data;
    modal.setAttribute('data-id-informasi', objectInformasi.id_informasi);
    modal.querySelector('.modal-title').innerText = data.judul;
    if (typeof labelInformasi(data.label) != 'boolean') {
        if (modal.querySelector('.badge') == null) {
            let badge = '<div><span class="badge '+ labelInformasi(data.label).class +' pt-1 text-capitalize">'+ labelInformasi(data.label).text +'</span></div>';
            modal.querySelector('.modal-title').insertAdjacentHTML('afterend', badge);
        }
    }

    if (data.id_author != null) {
        let flexAvatar = '<div class="d-flex align-items-center gap-x-3"><div class="avatar rounded-circle bg-transparent border overflow-hidden" data-id-author="'+ data.id_author +'"><img src="'+ data.path_author +'" alt="'+ data.nama_author +'" class="img-fluid"></div><div class="media-body"><div class="name mb-0 text-black-50 font-weight-bold"><span>'+ data.tanggal_posting +'</span></div><div class="small text-black-50 font-weight-bolder"><span>'+ data.nama_author +'</span></div></div></div>';
        modal.querySelector('.modal-body').insertAdjacentHTML('afterbegin', flexAvatar);
    }

    if (modal.querySelector('.modal-body #content') == null) {
        const div = document.createElement('div');
        div.setAttribute('id','content');
        modal.querySelector('.modal-body').appendChild(div);
    }

    const quill = new Quill(modal.querySelector('.modal-body #content'), {
        modules: {
            toolbar: false
        },
        readOnly: true
    });

    // render the content
    quill.setContents(JSON.parse(new DOMParser().parseFromString(data.isi, "text/html").querySelector('body').innerText));
};

const hasMoreData = (offset, total) => {
    return offset < total;
};

// control variables
let objectListDonatur = {
    offset: 0,
    limit: 10,
    total: 0
};

const showLoader = function(target) {
    target.classList.add('loader-animation');
};

const hideLoader = function(target) {
    target.classList.remove('loader-animation');
};

modalDonaturList.querySelector('.modal-body').addEventListener('scroll', function(e) {
    const {
        scrollTop,
        scrollHeight,
        clientHeight
    } = e.target;

    if (scrollTop + clientHeight >= scrollHeight - 5 &&
        hasMoreData(objectListDonatur.offset, objectListDonatur.total)) {
        
        if (e.target.querySelector('#content').classList.contains('loader-animation')) {
            return false;
        }

        showLoader(e.target.querySelector('#content'));
        let data = {
            'token': body.getAttribute('data-token'),
            'fields': {
                'id_bantuan': c_id_bantuan,
                'offset': objectListDonatur.offset,
                'limit': objectListDonatur.limit
            }
        };

        const elDonasi = '<div class="donatur load col-12 bg-light rounded-box p-3 d-flex justify-content-between gap-2" data-id-donasi="'+ 0 +'"><div class="media d-flex gap-3 flex-column"><div class="avatar border rounded"><img src="'+ '' +'" alt="'+ '' +'" class="avatar"></div><div class="media-body"><h5 class="mt-0"><span>' + 'donasi.nama_donatur' + '</span></h5>'+ '<p class="desc"><span>'+ 'donasi.doa' +'</span></p>' +'</div></div><div class="row w-100 g-0 align-items-end"><div class="col"><p class="text-black-50 text-decoration-underline">Donasi</p><span class="fw-bold">'+ 'donasi.jumlah_donasi' +'</span></div><div class="col d-flex justify-content-end align-items-center"><div class="d-flex flex-column align-items-center gap-1">'+ '<span class="heart-animation"></span>' +'<span class="liked text-orange">'+ 'donasi.liked' +' Disukai</span></div></div></div></div>';
        e.target.querySelector('#content').insertAdjacentHTML('beforeend', elDonasi);

        console.log(data);

        setTimeout(() => {
            // fetchReadDonatur()
            e.target.closest('.modal').classList.remove('shown');
            fetchData('/default/fetch/read/detil-bantuan/donatur', data, e.target, 'read-donatur-list');
            setTimeout(() => {
                e.target.closest('.modal').classList.add('shown');
            }, 800);
        }, 500);
    }
}, {
    passive: true
});

const modalPopPenawaran = document.getElementById('modalPopUpPenawaran');
const myPenawaran = new bootstrap.Modal(modalPopPenawaran);

modalPopPenawaran.addEventListener('hidden.bs.modal', function(e) {
    if (document.getElementById('modalDetilUpdate').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    } else if (document.getElementById('modalListDonatur').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    } else if (document.getElementById('modalShareBtn').classList.contains('show')) {
        document.querySelector('body').classList.add('modal-open');
    }
});

class popUpPenawaran {
    constructor(duration_end) {
        this.i = 0;
        this.current_duration = 0;
        this.duration_end = duration_end,
        this.shown = false;
    }

    start = function(event) {
        if (this.current_duration >= this.duration_end) {
            this.pause(event);
            myPenawaran.show();
            pop.shown = true;
            return false;
        }

        this.current_duration++;
        this.i = setTimeout(() => {
            // console.log('start');
            this.start(event);
        }, 1000);
    };

    pause = function(event) {
        // console.log('pause');
        clearTimeout(this.i);
    };
};

const pop = new popUpPenawaran(20);

document.addEventListener( 'visibilitychange' , function(e) {
    if (scrollingDoc) {
        if (!document.hidden) {
            if (pop.current_duration < pop.duration_end) {
                pop.start(e);
            }
        } else {
            pop.pause(e);
        }
    }
});

const modalUpdateList = document.getElementById('modalListUpdate');
// const myModalUpdateList = new bootstrap.Modal(modalUpdateList);

modalUpdateList.addEventListener('show.bs.modal', function (e) {
    // console.log(e.relatedTarget);
    let data_filter = e.relatedTarget.getAttribute('data-filter');

    e.target.querySelector('#data-filter').innerHTML = '<p class="fw-light small">'+ e.relatedTarget.innerText +'</p>';

    let data = {
        token: body.getAttribute('data-token'),
        filter_by: data_filter,
        filter_value: e.relatedTarget.getAttribute('data-' + data_filter + '-value'),
        id_bantuan: c_id_bantuan
    };

    if (e.relatedTarget.closest('.timeline-item') != null) {
        data.id_informasi = e.relatedTarget.closest('.timeline-item').getAttribute('data-id-informasi');
    }

    // fetchReadInformasi
    fetchData('/default/fetch/read/informasi/list', data, e.target, 'read-informasi');
});

modalUpdateList.addEventListener('hide.bs.modal', function (e) {
    e.target.querySelector('#data-filter').innerHTML = '';
    e.target.querySelector('#content').innerHTML = '';
    relatedTarget = {};
    objectInformasi = {};
});

modalUpdateList.querySelector('.modal-body').addEventListener('scroll', function(e) {
    const {
        scrollTop,
        scrollHeight,
        clientHeight
    } = e.target;

    if (scrollTop + clientHeight >= scrollHeight - 5 &&
        hasMoreData(objectInformasi.offset, objectInformasi.total_record)) {
        
        if (e.target.querySelector('#content').classList.contains('loader-animation')) {
            return false;
        }

        showLoader(e.target.querySelector('#content'));
    
        let data = objectInformasi;
        data.token = body.getAttribute('data-token');
        data.id_bantuan = c_id_bantuan;

        const elTimelineItem = '<div class="load col-12 timeline-item px-0" data-id-informasi="' + 'reverseString(btoa(row.id_informasi))' + '"><div class="row m-0 w-100"><div class="col-12 col-lg-2"><div class="time small fw-bold"><a role="button" href="javascript:void(0);" class="text-secondary" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="date" data-date-value="'+ 'row.tanggal_publikasi' +'"><p><span>' + 'row.waktu_publikasi' + '</span></p></a></div><a role="button" href="javascript:void(0);" data-label-value="'+ 'row.label' +'" data-bs-toggle="modal" data-bs-target="#modalListUpdate" data-filter="label" class="text-capitalize badge' + 'labelInformasi(row.label).class' + '">' + 'labelInformasi(row.label).text' + '</a></span></div><div class="col-12 col-lg content flex-column d-flex"><b><span>' + 'row.judul' + '</span></b><span><div class="editor-read">' + 'row.isi' + '</div></span><div><a role="button" href="javascript:void(0);" class="text-decoration-underline" data-bs-target="#modalDetilUpdate">Lebih detil <i class="lni lni-chevron-right"></i></a></div></div></div></div>';
        e.target.querySelector('#content').insertAdjacentHTML('beforeend', elTimelineItem);

        // console.log(data);
        
        setTimeout(() => {
            // fetchReadInformasi()
            fetchData('/default/fetch/read/informasi/list', data, e.target, 'read-informasi');
        }, 1500);
    }
}, {
    passive: true
});

let relatedTarget = {};
modalUpdateList.addEventListener('click', function(e) {
    if (e.target.tagName != 'A') {
        return false;
    }

    if (e.target.getAttribute('data-bs-target') != '#modalDetilUpdate') {
        e.preventDefault();
        return false;
    }

    relatedTarget = e.target;
    myModalDetilUpdate.show();
});

const modalDetilUpdate = document.getElementById('modalDetilUpdate');
const myModalDetilUpdate = new bootstrap.Modal(modalDetilUpdate);

if (document.querySelector('#modalDetilUpdate #content').innerText.trim().length > 0) {

    const quill = new Quill(modalDetilUpdate.querySelector('#content'), {
        modules: {
            toolbar: false
        },
        readOnly: true
    });

    // render the content
    quill.setContents(JSON.parse(quill.container.innerText));
    myModalDetilUpdate.show();
}

modalDetilUpdate.addEventListener('show.bs.modal', function (e) {
    if (e.relatedTarget != null) {
        relatedTarget = e.relatedTarget;
    }

    const data = {
        token: body.getAttribute('data-token'),
        fields: {
            id_informasi: relatedTarget.closest('.timeline-item').getAttribute('data-id-informasi')
        }
    }
    // fetchGetInformasiBerita
    relatedModal = e.target;
    fetchData('/default/fetch/get/informasi', data, e.target, 'get-informasi-berita');
});

modalDetilUpdate.addEventListener('hide.bs.modal', function (e) {
    objectInformasi = {};
    relatedModal = null;
    e.target.removeAttribute('data-id-informasi');
    e.target.querySelector('.modal-title').innerHTML = '';
    e.target.querySelector('.modal-header .badge').remove();
    e.target.querySelector('.modal-body').innerHTML = '';
});

modalDetilUpdate.querySelector('button[data-bs-target="#modalShareBtn"]').addEventListener('click', function(e) {
    myShareModal.show();
});