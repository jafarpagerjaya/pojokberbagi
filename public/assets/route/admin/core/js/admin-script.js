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
                    el.removeAttribute('style');
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
        theadThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem)');
        // theadThEl.parentElement.setAttribute('style', 'height: ' + theadThEl.offsetHeight + 'px');
        table.classList.add('table-responsive');
    }

    table.querySelectorAll('tbody tr:not([data-zero="true"]) > *:first-child').forEach(el => {
        el.setAttribute('style', 'width:' + tbodyTFW + 'px');
        el.nextElementSibling.setAttribute('style', 'padding-left: calc(' + tbodyTFW + 'px + 1rem)');
        if (el.children[0] != null) {
            const computedStyle = getComputedStyle(el);
            let elementWidth = el.clientWidth;
            elementWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
            if (el.children[0].offsetWidth > elementWidth || elementWidth - el.children[0].offsetWidth <= 1) {
                el.parentElement.setAttribute('style', '');
                setTimeout(() => {
                    el.parentElement.setAttribute('style', 'height: ' + el.offsetHeight + 'px');
                }, 0)
            }
        } else if (el.children[0] == undefined) {
            const computedStyle = getComputedStyle(el);
            let elementWidth = el.clientWidth;
            elementWidth -= parseFloat(computedStyle.paddingLeft) + parseFloat(computedStyle.paddingRight);
            if (el.offsetWidth > elementWidth || elementWidth - el.offsetWidth <= 1) {
                el.parentElement.setAttribute('style', '');
                setTimeout(() => {
                    el.setAttribute('style', 'height: ' + el.nextElementSibling.offsetHeight + 'px; width: '+ tbodyTFW +'px');
                }, 0)
            }
        }
    });

    if (tfootThEl != null) {
        if (table.querySelector('tbody tr[data-zero="true"]') == null) {
            tfootThEl.setAttribute('style', 'width: ' + theadThFW + 'px');
            tfootThEl.nextElementSibling.setAttribute('style', 'padding-left: calc(' + theadThFW + 'px + 1rem)');
            // tfootThEl.parentElement.setAttribute('style', 'height: ' + tfootThEl.offsetHeight + 'px');
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
                    e.preventDefault();
                    table.scrollLeft += e.deltaY;
                });
            });
        });
    }
};

tableAblsoluteFirstScroll();