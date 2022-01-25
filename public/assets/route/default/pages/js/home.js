let counterTarget = document.querySelectorAll('.carousel-item.active .box-info h6[data-target]'),
    counterSpeed = 2000;

counterUpSup(counterTarget, counterSpeed);

let progressBar = document.querySelectorAll('.carousel-item.active .app-image .progress-bar');
counterUpProgress(progressBar, counterSpeed);

var checkWOWJsReset = function () {
    var resetWOWJsAnimation = function () {
        var $that = $(this);

        // determine if container is in viewport
        // you might pass an offset in pixel - a negative offset will trigger loading earlier, a postive value later
        // credits @ https://stackoverflow.com/a/33979503/2379196
        var isInViewport = function ($container, offset) {
            var containerTop = $container.offset().top;
            var containerBottom = containerTop + $container.outerHeight();

            var viewportTop = $(window).scrollTop();
            var viewportBottom = viewportTop + $(window).height();

            return containerBottom > viewportTop && containerTop + offset < viewportBottom;
        };

        // only reset animation when no long in viewport and already animated (but not running)
        // you might want to use a different offset for isInViewport()
        if (!isInViewport($that, 0) && $that.css('animation-name') != 'none' && !$that.hasClass('animated')) {
            $that.css({
                'visibility': 'hidden',
                'animation-name': 'none'
            }); // reset animation
            wow.addBox(this);
        }
    };
    $('.wow').each(resetWOWJsAnimation); // check if reset is necessary for any element
};

// $(window).on('resize scroll', checkWOWJsReset); // check on resize and scroll events

let myCarousel = document.getElementById('carousel');

myCarousel.addEventListener('slide.bs.carousel', function (e) {
    progressBar = e.relatedTarget.querySelectorAll('.progress-bar');
    counterTarget = e.relatedTarget.querySelectorAll('.box-info h6[data-target]');
    counterUpProgress(progressBar, counterSpeed);
    counterUpSup(counterTarget, counterSpeed);
    $(e.relatedTarget).each(function () {
        $(this).siblings('.carousel-item').find('.wow').addClass('d-invisivle');
        $(this).siblings('.carousel-item').find('.progress-bar').removeAttr('style');
        $(this).find('.wow').removeClass('d-invisivle');
    });
    var $animatingElems = $(e.relatedTarget).find(".wow");
    doAnimations($animatingElems);
});

let buttonCollapse = document.querySelectorAll('.collapseButton'),
    collapse = document.querySelectorAll('.app-image .collapse'),
    pushRow = document.querySelectorAll('.hero-content');

let defaultMTpushRow,
    resizeTimeout,
    rowHolderHeight;

let addColDescMargin = function (collapse, window) {
    collapse.forEach(elemCollapse => {
        pushRow = elemCollapse.closest('.hero-row').querySelector('.hero-content');
        if (window.outerWidth < 768) {
            if (elemCollapse.nextElementSibling.offsetHeight > 0) {
                rowHolderHeight = elemCollapse.nextElementSibling.offsetHeight;
            }
            defaultMTpushRow = pushRow.style.marginTop = rowHolderHeight + parseInt(window.getComputedStyle(elemCollapse.nextElementSibling).marginTop) + 'px';
            elemCollapse.classList.remove('show');
        } else {
            pushRow.style.removeProperty('margin-top');
            elemCollapse.classList.add('show');
        }
    });
}

addColDescMargin(collapse, window);

window.addEventListener('resize', function () {
    clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(() => {
        addColDescMargin(collapse, window);
    }, 50);
});

let collapsableRow = [];

collapse.forEach(elemCollapse => {
    objectCollapse = new bootstrap.Collapse(elemCollapse, { toggle: false })
    collapsableRow.push(objectCollapse);

    elemCollapse.addEventListener('show.bs.collapse', function (e) {
        let nextCollapseSibling = this.nextElementSibling,
            nextCollapseSiblingHeight = nextCollapseSibling.offsetHeight,
            nextCollapseSiblingMT = parseInt(window.getComputedStyle(elemCollapse.nextElementSibling).marginTop);

        pushRow = this.closest('.hero-row').querySelector('.hero-content');
        pushRow.style.marginTop = 165 + nextCollapseSiblingHeight + nextCollapseSiblingMT * 2 + "px";
        pushRow.style.transition = "margin .3s";
    });

    elemCollapse.addEventListener('hide.bs.collapse', function () {
        pushRow = this.closest('.hero-row').querySelector('.hero-content');
        pushRow.style.marginTop = defaultMTpushRow;
    });
});

let click;

buttonCollapse.forEach(elemButton => {
    elemButton.addEventListener('click', function (e) {
        if (click) {
            return false;
        }
        if (window.outerWidth < 768) {
            collapsableRow[this.getAttribute('data-target')].toggle();
            let span = this.children[0],
                icon = this.children[1];
            if (span.innerText === "Lebih detil") {
                span.innerText = "Sembunyikan detil";
                icon.style.transform = "rotate(90deg)";
                icon.style.transition = "transform .3s";
            } else {
                span.innerText = "Lebih detil";
                icon.style.transform = "rotate(0deg)";
            }
            click = true;
            setTimeout(() => {
                click = false;
            }, 350);
        }
    });
});
// Model Via JS 
const notifikasiModalEl = document.getElementById('notifikasi');
if (notifikasiModalEl != null) {
    var myModal = new bootstrap.Modal(notifikasiModalEl, {
        backdrop: 'static', 
        keyboard: false
    });
    setTimeout(()=>{
        myModal.toggle();
    }, 500);
}