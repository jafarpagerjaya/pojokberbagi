const aDisabledList = document.querySelectorAll('a.disabled');
aDisabledList.forEach(aDisabled => {
    aDisabled.addEventListener('click', function(e) {
        e.preventDefault();
    });
});

const counterTarget = document.querySelectorAll('.counter-card'),
      counterSpeed = 2000;

counterUpSup(counterTarget, counterSpeed);

let toastRun = document.querySelector('.toast[data-toast="feedback"][data-toast-run="true"]');
if (toastRun != null) {
    $('.toast[data-toast="feedback"]').toast('show');
    let elTarget = $('.toast[data-toast="feedback"] .toast-body [data-id-target]'),
        target = elTarget.data('id-target');
    
    $('table tbody>tr>th a[data-id="'+target+'"], table tbody>tr>td a[data-id="'+target+'"]').parents('tr').addClass('highlight');

    setTimeout(() => {
        $('table tbody>tr.highlight').removeClass('highlight');
    }, 3100);
}

let tableWidthSetter = function(table) {
    if (table.find('thead').width() > table.parent().width()) {
        table.addClass('table-responsive');
    } else {
        if (table.hasClass('table-responsive')) {
            table.removeClass('table-responsive');
        }
    }
};

tableWidthSetter($('table.table'));

let resizeTimeoutTable;
$(window).resize(function () {
    clearTimeout(resizeTimeoutTable)
    resizeTimeoutTable = setTimeout(function () {
        if ($('table.table').length) {
            tableWidthSetter($('table.table'));
        }
    }, 50);
});

const sideNavTogglerBtn = document.querySelector('.sidenav-toggler-inner'),
      mainContentPanel = document.getElementById('panel');
sideNavTogglerBtn.addEventListener('click', function () {
    if (!this.parentElement.classList.contains('active')) {
        setTimeout(() => {
            let bodyClick = document.querySelector('.bodyClick'),
                bodyBackdrop = bodyClick.nextSibling;
            if (bodyBackdrop) {
                bodyBackdrop.remove()
            }
            mainContentPanel.classList.add('backdrop');
            bodyClick.addEventListener('click', function () {
                document.querySelector('body').classList.remove('nav-open')
                document.querySelector('body').classList.remove('g-sidenav-show')
                document.querySelector('body').classList.remove('g-sidenav-pinned')
                document.querySelector('body').classList.add('g-sidenav-hidden')
                if (sideNavTogglerBtn.parentElement.classList.contains('active')) {
                    sideNavTogglerBtn.parentElement.classList.remove('active')
                }
                if (mainContentPanel.classList.contains('backdrop')) {
                    mainContentPanel.classList.remove('backdrop');
                    mainContentPanel.classList.add('backdrop-remove-animated');
                    setTimeout(() => {
                        mainContentPanel.classList.remove('backdrop-remove-animated');
                    }, 300);
                }
            });
        }, 0);
    }
});

// warna header for box-info row bg
const header = document.querySelector('#panel.main-content > .header');
if (header != null) {
    let rgb = window.getComputedStyle(header).backgroundColor;
    const RHeader = document.getElementById('row-header');
    if (RHeader != null) {
        RHeader.style.backgroundColor = rgb;
    }
}

function findIndex(node) {
    let i = 1;
    while ((node = node.previousSibling) != null) {
        if (node.nodeType === 1) i++;
    }
    return i;
}

// Table absolute first
function doAbsoluteFirstAdd(table) {
    let theadThEl = table.querySelector('thead tr > *:first-child'),
        theadThFW = theadThEl.offsetWidth,
        tfootThEl = table.querySelector('tfoot tr > *:first-child'),
        tableHW = table.offsetWidth / 2;

    let tbodyTFW = 0;

    if (theadThFW > tableHW) {
        theadThFW = tableHW;
        tbodyTFW = tableHW;
    }    

    if (tbodyTFW == 0) {
        let i = 0;
        table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-child').forEach(el => {
            if (tableHW <= el.offsetWidth) {
                tbodyTFW = tableHW;
                theadThFW = tableHW;
                return false;
            }            
            if (el.offsetWidth > theadThFW) {
                theadThFW = el.offsetWidth;
            } else {
                tbodyTFW = theadThFW;
                if (i == 0) {
                    theadThFW = el.offsetWidth;
                }
            }
            if (tbodyTFW < el.offsetWidth) {
                tbodyTFW = el.offsetWidth;
            }
            i++;
        });
    }

    if (table.querySelector('tbody tr[data-zero="true"]') == null) {
        theadThEl.setAttribute('style', 'width: ' + theadThFW + 'px');
        theadThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem) !important;');
        table.classList.add('table-responsive');
    }

    table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-child').forEach(el => {
        el.setAttribute('style', 'width:' + tbodyTFW + 'px;');
        el.nextElementSibling.setAttribute('style', 'padding-left: calc(' + tbodyTFW + 'px + 1rem) !important;');
    });

    if (tfootThEl != null) {
        if (table.querySelector('tbody tr[data-zero="true"]') == null) {
            tfootThEl.setAttribute('style', 'width: ' + theadThFW + 'px');
            tfootThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem)');
        }
    }

    if (!table.classList.contains('table-absolute-first')) {
        if (table.querySelector('tbody tr[data-zero="true"]') == null) {
            table.classList.add('table-absolute-first');
        }
    }
}

function doAbsoluteFirstRemove(table) {
    let theadThEl = table.querySelector('thead tr > *:first-child'),
        tfootThEl = table.querySelector('tfoot tr > *:first-child');

    theadThEl.removeAttribute('style');
    theadThEl.nextElementSibling.removeAttribute('style');
    theadThEl.parentElement.removeAttribute('style');

    table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-child').forEach(el => {
        el.removeAttribute('style');
        el.nextElementSibling.removeAttribute('style');
        el.parentElement.removeAttribute('style');
    });

    if (tfootThEl != null) {
        tfootThEl.removeAttribute('style');
        tfootThEl.nextElementSibling.removeAttribute('style');
        tfootThEl.parentElement.removeAttribute('style');
    }
}

const tableAbsoluteFirstList = document.querySelectorAll('table.table-absolute-first');
if (tableAbsoluteFirstList.length > 0) {
    tableAbsoluteFirstList.forEach(table => {
        if (table.classList.contains('table-responsive')) {
            doAbsoluteFirstAdd(table);
        }
    });
    let resizeTimeoutRab
    window.addEventListener('resize', function (e) {
        clearTimeout(resizeTimeoutRab)
        resizeTimeoutRab = setTimeout(() => {
            if (tableAbsoluteFirstList.length > 0) {
                tableAbsoluteFirstList.forEach(table => {
                    if (table.classList.contains('table-responsive')) {
                        doAbsoluteFirstAdd(table);
                    } else {
                        doAbsoluteFirstRemove(table);
                    }
                })
            }
        }, 50);
    });
}

let tableAblsoluteFirstScroll = function() {
    const tAL = document.querySelectorAll('table.table-responsive.table-absolute-first');
    if (tAL != null) {
        tAL.forEach(table => {
            table.querySelectorAll('tbody tr>*:not(:first-child').forEach(element => {
                element.addEventListener('mousewheel', function(e) {
                                         
                    if (table.scrollLeft === 0 && e.deltaY < 0) {
                        return false;
                    }                               

                    if (Math.ceil(table.offsetWidth + table.scrollLeft) >= element.parentElement.offsetWidth && e.deltaY > 0) {
                        return false;
                    }
                    
                    e.preventDefault();
                    table.scrollLeft += e.deltaY;

                    let penyesuaian = element.parentElement.offsetWidth - (table.offsetWidth + table.scrollLeft);

                    if (penyesuaian < 100 && e.deltaY > 0) {
                        table.scrollLeft += penyesuaian;
                    }

                });
            });
        });
    }
};

tableAblsoluteFirstScroll();

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
    toastHeader.classList.add('toast-header', 'd-flex', 'gap-x-2', 'justify-content-center', 'align-items-center');
    toastContainer.appendChild(toastHeader);

    const toastBox = document.createElement('div');
    toastBox.classList.add('small-box', 'rounded', 'p-2', 'ailgn-items-center', bgBox);
    toastHeader.appendChild(toastBox);

    const toastTitle = document.createElement('strong');
    toastTitle.classList.add('mr-auto');
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
        'class':'close',
        'data-dismiss':'toast',
        'aria-label':'Close'
    };

    const toastDismiss = document.createElement('button');
    setMultipleAttributesonElement(toastDismiss, toastDismissAtributes);
    toastHeader.appendChild(toastDismiss);

    const toastBtnSpanAtributes = {
        'aria-hidden':'true',
        'type':'button'
    };

    const toastSpanBtn = document.createElement('span');
    setMultipleAttributesonElement(toastSpanBtn, toastBtnSpanAtributes);
    toastSpanBtn.innerHTML = '&times;';
    toastDismiss.appendChild(toastSpanBtn);
    
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

let timeAgoRuns = function(target, targetValue, key = '', interval = 60000, targetParent = null, targetParentData = null) {
    let spanDMVList,
        dmvObj = {},
        i = 0,
        timeIdentity;

    if (typeof target == 'string') {
        spanDMVList = document.querySelectorAll(target);
    } else if (typeof target == 'object') {
        spanDMVList = target;
    }

    spanDMVList.forEach(el => {
        dmvObj[key + i] = {
            modified_at: el.getAttribute(targetValue)
        };

        if (targetParent != null && targetParentData != null) {
            dmvObj[key + i].id = el.closest(targetParent).getAttribute(targetParentData);
        }

        if (i == 0) {
            if (el.getAttribute('time-ago-identity') == null) {
                timeIdentity = Math.random().toString(36).substring(2,7) +''+dmvObj[key + i].id;
                el.setAttribute('time-ago-identity', timeIdentity);
            } else {
                timeIdentity = el.getAttribute('time-ago-identity');
                clearInterval(timeIntervalList[timeIdentity]);
            }
        } else {
            el.setAttribute('time-ago-identity', timeIdentity);
        }

        i++;
    });

    // let oTime = new Date(),
    //     secondsLeft = (60 - oTime.getSeconds()) * 1000;

    spanDMVList.forEach(ele => {
        for (const property in dmvObj) {
            const modified_at_time = new Date(dmvObj[property].modified_at);
            let secondsLeft = (((60 - new Date().getSeconds()) + modified_at_time.getSeconds()) % 60) * 1000;
            if (ele.closest(targetParent).getAttribute(targetParentData) == dmvObj[property].id) {
                ele.innerHTML = (timePassed(modified_at_time) == 'sekarang' || timePassed(modified_at_time) == '1 detik yang lalu' ? 'beberapa saat yang lalu':timePassed(modified_at_time));
                setTimeout(() => {
                    let timeInterval = setInterval(() => {
                        if (ele.closest(targetParent).getAttribute(targetParentData) == dmvObj[property].id) {
                            ele.innerHTML = timePassed(modified_at_time);
                        }
                    }, interval);
                    timeIntervalList[timeIdentity] = timeInterval;
                }, secondsLeft);
            }
        }
    });

    // setTimeout(() => {
        // clearInterval(timeIntervalList[timeIdentity]);
        // let timeInterval = setInterval(() => {
        //     spanDMVList.forEach(ele => {
        //         for (const property in dmvObj) {
        //             if (ele.closest(targetParent).getAttribute(targetParentData) == dmvObj[property].id) {
        //                 ele.innerHTML = timePassed(new Date(dmvObj[property].modified_at));
        //             }
        //         }
        //     });
        // }, interval);

        // timeIntervalList[timeIdentity] = timeInterval;
    // }, secondsLeft);
};

// Diulangn dengan fungsi resize
let footerSetter = function() {
    let wh = $( window ).height(),
    kurangHeight = wh - $('footer').offset().top - $('footer').height() - parseInt($('footer').css('padding-bottom')),
    mc = $('#main-content'),
    mcHeight = mc.height();
    
    if (kurangHeight > 0) {
        mc.css('min-height', (mcHeight + kurangHeight)+'px');
    } else {
        mc.css('min-height', wh - (mc.offset().top + $('footer').height()))
    }
};

footerSetter();

let resizeTimeout;
$(window).resize(function () {
    clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(function () {
        footerSetter();
    }, 50);
});

const navbarLinkList = document.querySelectorAll('#sidenav-collapse-main .navbar-nav .nav-item.nav-dropdown');

navbarLinkList.forEach(navlinkA => {
    if (!navlinkA.children[0].classList.contains('active')) {
        navlinkA.children[0].nextElementSibling.setAttribute('style','margin-top: -'+ navlinkA.children[0].nextElementSibling.getBoundingClientRect().height.toFixed(2) +'px;')
    }

    navlinkA.children[0].addEventListener('click', function(e) {
        let h = 0,
            t = 0;

        if (this.classList.contains('active')) {
            h = this.nextElementSibling.getBoundingClientRect().height.toFixed(2);
            t = (parseFloat(window.getComputedStyle(this.nextElementSibling).transitionDuration)) * (false ? 1000 : 1);
            this.classList.add('transition');
        }

        this.classList.toggle('active');
        
        if (!this.classList.contains('active')) {
            h = this.nextElementSibling.setAttribute('style','margin-top: -'+h+'px;');
        } else {
            this.nextElementSibling.removeAttribute('style');
        }

        if (t) {
            setTimeout(() => {
                this.classList.remove('transition');
            }, t* 1000);
        }
    });
});

// New
$("#notifikasi").modal({backdrop: "static"});
// Old
$("#notif-modal").modal({backdrop: "static"});