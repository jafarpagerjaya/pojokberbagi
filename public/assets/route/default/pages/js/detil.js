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
    if (!response.error) {

        const quill = new Quill('#selengkapnya', {
            modules: {
                toolbar: false
            },
            readOnly: true
        });

        // render the content
        quill.setContents(JSON.parse(response.feedback.data));
        document.querySelector('body').setAttribute('data-token', response.token);
        fetchTokenChannel.postMessage({
            token: document.querySelector('body').getAttribute('data-token')
        });
    
    }

    if (response.toast != null) {
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
         case 'lni-twitter-filled':
         shareUrl = `https://twitter.com/share?url=${encodeURIComponent(url)}`;
         break;
        //  case 'linkedin':
        //  shareUrl = `https://www.linkedin.com/shareArticle?url=${encodeURIComponent(url)}`;
        //  break;
         case 'lni-whatsapp':
         shareUrl = `https://api.whatsapp.com/send?text=${encodeURIComponent(url)}`;
         break;
      }

    //   Open a new window to share the URL
      window.open(shareUrl, '_blank');
   });
});