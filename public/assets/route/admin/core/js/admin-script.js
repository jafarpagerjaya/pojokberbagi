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