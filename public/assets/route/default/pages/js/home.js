let resetTab = false,
    resetNotATab = false,
    resetTamob = false,
    resetCoLep = false,
    lastWidth,
    lock = false;

function wowReset() {
    const delay = 0.2,
          elTargetRowColTextImgList = document.querySelectorAll('.hero-area .container>.row'),
          elTargetRowCustomColList = document.querySelectorAll('.custom-for-col-md-6');

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
                if (elCol.classList.contains('order-0')) {
                    firstOrder.push(elCol);
                } else {
                    secondOrder.push(elCol);
                }
            });
        }

        let reset = false;

        if (detectMob()) {
            newOrder = firstOrder.concat(secondOrder);
            if (!resetTamob) {
                resetCoLep = reset;
                reset = true;
                resetTamob = reset;
                lastWidth = window.innerWidth;
                lock = false;
            }
            if (i > 0 && resetTamob && window.innerWidth == lastWidth && lock == false) {
                reset = true;
            }
            if (i == elTargetRowCustomColList.length-1) {
                lock = true;
            }
        } else {
            if (!resetCoLep) {
                resetTamob = reset;
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
            const elTargetList = document.querySelectorAll('.row.custom-for-col-md-6'),
                  elTargetSetter = this.closest('.order-0');
            let i = 0,
                height;

            elTargetList.forEach(elTarget => {
                let elTargetSetterHeight;

                let span = buttonCollapse[i].children[0],
                    icon = buttonCollapse[i].children[1];

                elTarget.classList.toggle('toggle');
                if (elTarget.classList.contains('toggle')) {
                    span.innerText = "Sembunyikan detil";
                    icon.style.transform = "rotate(90deg)";
                    icon.style.transition = "transform .3s";
                    elTarget.setAttribute('style','height: '+elTarget.getAttribute('data-height'));
                } else {
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
        counterSpeed = 1600;

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