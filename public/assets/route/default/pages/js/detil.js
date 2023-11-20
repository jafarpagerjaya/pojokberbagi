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

let data = {
        id_bantuan: window.location.href.split('/')[5],
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
    console.log(windowScrollNavbarBottom, stickyBtnOffsetTopY, windowScrollY);
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


const myModal = new bootstrap.Modal(document.getElementById('modalShareBtn'));
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

        if (response.feedback != null) {
            if (response.feedback.lsc) {
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
                fetchData(url, data, root, f);
            }
        }

        if (response.error) {
            createNewToast(document.querySelector('[aria-live="polite"]'), response.toast.id, response.toast.data_toast, response.toast);
            $('#'+ response.toast.id +'.toast[data-toast="'+ response.toast.data_toast +'"]').toast({
                'autohide': false
            }).toast('show');
            return false;
        }

        switch (f) {
            case 'liked-click':
                fetchLikeClicked(root, response.feedback.data.liked);
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
            'id_bantuan': window.location.pathname.split('/').at(3)
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

    // fetchLikeClicked
    fetchData('/default/fetch/' + data.mode + '/amin', data, e, 'liked-click');
    // console.log('/default/fetch/' + data.mode + '/amin');
};

const likedList = document.querySelectorAll('.donatur .heart-animation');

likedList.forEach(icon => {
    icon.addEventListener('click', debounceIgnoreLast(clickLiked, 500));
});

let fetchLikeClicked = function(e, liked) {
    e.target.classList.toggle('animate');
    if (e.target.getAttribute('checked') != null) {
        // unckeck now
        e.target.removeAttribute('checked');
    } else {
        // check now
        e.target.setAttribute('checked', true);
    }
    e.target.nextElementSibling.innerText = liked + ' Disukai';
};