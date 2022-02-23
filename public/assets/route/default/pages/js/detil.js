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