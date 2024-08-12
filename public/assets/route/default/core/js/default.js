const elNavbarMobToggler = document.querySelector('.header .navbar-toggler.mobile-menu-btn');

elNavbarMobToggler.addEventListener('click', ()=> {
    document.querySelector('body').classList.toggle('overflow-hidden');
});

function calculaterowPerGridHeight(item) {
    let totalHeight = item[0].offsetHeight,
        row = 0;
    if (item.length > 1) {
        let i = 0;
        while (i < item.length) {
            if (i > 0) {
                if (item[i].offsetTop != item[i-1].offsetTop) {
                    totalHeight += item[i].offsetHeight;
                    row++;
                }
            }
            i++;
        }
    }
    return [totalHeight,row];
};

function setHeight(el, deviceChangeOn, reverse = undefined) {
    const elTargetList = el.querySelectorAll('.row.custom-height-setter');
    let dataHeight,
        setterHeight,
        elResetList = [];

    let loop = 0;
    elTargetList.forEach(elTarget => {
        let elCollapseButton = elTarget.querySelector('.collapseButton');
        if (deviceChangeOn) {
            if (!elCollapseButton.classList.contains('toggle') && reverse == undefined) {
                const gap = parseFloat(getComputedStyle(elTarget).gap);

                let arrayCalculate = calculaterowPerGridHeight(elTarget.children),
                    height = arrayCalculate[0] + arrayCalculate[1]*gap;

                if (height == 0) {
                    height = dataHeight;
                } else {
                    dataHeight = height;
                }

                if (dataHeight == undefined) {
                    elResetList.push(elTarget);
                    return;
                }

                elTarget.setAttribute('data-height', height+'px');

                let elTargetSetter = elTarget.querySelector('.order-0');

                setTimeout(()=> {
                    let elTargetSetterHeight = elTargetSetter.offsetHeight;
                    if (elTargetSetterHeight == 0) {
                        elTargetSetterHeight = setterHeight;
                    } else {
                        setterHeight = elTargetSetterHeight;
                    }
                    elTarget.setAttribute('style','height: '+elTargetSetterHeight+'px;');
                    if (loop == elTargetList.length-1 && elResetList.length > 0) {
                        elResetList.forEach(elToSet => {
                            elToSet.setAttribute('style','height: '+setterHeight+'px;');
                        });
                        elResetList = [];
                    }
                }, 0);
            } else {
                if (reverse) {
                    let height;
                    if (elCollapseButton.classList.contains('toggle')) {
                        elTarget.style.removeProperty('height');
                        elTarget.setAttribute('data-height', elTarget.offsetHeight+'px');
                        height = elTarget.offsetHeight;
                    } else {
                        height = elCollapseButton.closest('.order-0.col-6').offsetHeight;
                        height += parseInt(getComputedStyle(elTarget).paddingBottom);

                        const gap = parseFloat(getComputedStyle(elTarget).gap);

                        let arrayCalculate = calculaterowPerGridHeight(elTarget.children),
                            dataHeight = arrayCalculate[0] + arrayCalculate[1]*gap + parseInt(getComputedStyle(elTarget).paddingBottom);
                        elTarget.setAttribute('data-height', dataHeight+'px');
                    }
                    elTarget.setAttribute('style','height: '+height+'px');
                } else {
                    elTarget.setAttribute('style','height: '+elTarget.getAttribute('data-height'));
                }
            }
        } else {
            elTarget.style.removeProperty('height');
        }
        loop++;
    });
};

WOW.prototype.addBox = function (element) {
    this.boxes.push(element);
};

wow = new WOW();
wow.init();

// Toast Maker
let createNewToast = function(toastParentEl, toastId = null, dataToast = 'feedback', toast = null) {
    if (toastParentEl == null) {
        console.log('Object Toast Parent Cannot be Found');
        return false;
    }

    let bgBox = 'bg-default',
        titleBox = 'Pemberitahuan',
        message = 'data.feedback.message';
        
    if (toast != null) {
        if (toast.error) {
            bgBox = 'bg-danger';
            titleBox = 'Peringatan!';
        } else {
            bgBox = 'bg-success';
            titleBox = 'Informasi';
        }

        message = toast.feedback.message;
    };

    const toastAtributes = {
        'role':'alert',
        'aria-live':'assertive',
        'aria-atomic':'true',
        'data-toast':dataToast,
        'data-delay':5000
    };

    if (toastId != null) {
        toastAtributes.id = toastId;
    }

    const toastContainer = document.createElement('div');
    toastContainer.classList.add('toast', 'w-100', 'fade', 'hide' , 'bg-white');
    setMultipleAttributesonElement(toastContainer, toastAtributes);

    const toastHeader = document.createElement('div');
    toastHeader.classList.add('toast-header', 'd-flex', 'gap-2', 'justify-content-between', 'align-items-center');
    toastContainer.appendChild(toastHeader);

    const toastBox = document.createElement('div');
    toastBox.classList.add('small-box', 'rounded', 'p-2', 'ailgn-items-center', bgBox);
    toastHeader.appendChild(toastBox);

    const toastTitle = document.createElement('strong');
    toastTitle.classList.add('me-auto');
    const toastTitleText = document.createTextNode(titleBox);
    toastTitle.appendChild(toastTitleText);
    toastHeader.appendChild(toastTitle);

    const toastTimePassed = document.createElement('small');
    toastTimePassed.classList.add('text-muted', 'time-passed');
    const toastTimePassedText = document.createTextNode('Baru saja');
    toastTimePassed.appendChild(toastTimePassedText);
    toastHeader.appendChild(toastTimePassed);

    const toastDismissAtributes = {
        'type':'button',
        'class':'btn-close',
        'data-bs-dismiss':'toast',
        'aria-label':'Close'
    };

    const toastDismiss = document.createElement('button');
    setMultipleAttributesonElement(toastDismiss, toastDismissAtributes);
    toastHeader.appendChild(toastDismiss);
    
    const toastBody = document.createElement('div');
    toastBody.classList.add('toast-body');
    toastBody.innerHTML = message;
    toastContainer.appendChild(toastBody);

    toastParentEl.appendChild(toastContainer);

    setTimeout(() => {
        $((toast.id == null ? '':'#'+ toast.id) +'.toast[data-toast="'+ toast.data_toast +'"]').on('hidden.bs.toast', function () {
            let dataToast;
            if (this.getAttribute('id') == null) {
                dataToast = toast.data_toast;
            } else {
                dataToast = toast.id;
            }

            stopPassed(dataToast);
            $(this).remove();
        }).on('shown.bs.toast', function() {
            setTimeout(()=> {
                let toastEl = document.getElementById(toast.id);
                toastPassed(toastEl.querySelector('.time-passed'));
            }, 0);
        });
    },0);
};

function round(v) {
    return (v >= 0 || -1) * Math.floor(Math.abs(v));
}

function timePassed(startTime) {
    const units = {
        year  : 24 * 60 * 60 * 1000 * 365,
        month : 24 * 60 * 60 * 1000 * 365/12,
        day   : 24 * 60 * 60 * 1000,
        hour  : 60 * 60 * 1000,
        minute: 60 * 1000,
        second: 1000
    };

    let rtf = new Intl.RelativeTimeFormat('id', { numeric: 'auto' });

    let getRelativeTime = (d1, d2 = new Date()) => {
        let elapsed = d1 - d2;

        for (let u in units) {
            if (Math.abs(elapsed) > units[u] || u == 'second') {
                return rtf.format(round(elapsed/units[u]), u);
            }
        }
    }
    return getRelativeTime(startTime);
};