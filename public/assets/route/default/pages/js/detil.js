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
        token: document.querySelector('body').getAttribute('data-token')
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

    if (!response.error) {

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
});


// Get all share buttons
const shareButtons = document.querySelectorAll('.share a.medsos-icon');

// Add click event listener to each button
shareButtons.forEach(button => {
   button.addEventListener('click', () => {
      // Get the URL of the current page
      const url = window.location.href;

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

const header_navbar = document.querySelector(".navbar-area"),
    sticky_btn_area = document.querySelector('.btn.button.donasi').parentElement,
    sticky_btn = document.querySelector('.btn.button.donasi').closest('#commit-bantuan-area');
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
},50);

let lastKnownScrollPosition = 0;
let ticking = false;

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
    });

    ticking = true;
  }
});


const shareModal = new bootstrap.Modal(document.getElementById('modalShareBtn'));

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