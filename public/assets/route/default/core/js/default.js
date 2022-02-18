const elNavbarMobToggler = document.querySelector('.header .navbar-toggler.mobile-menu-btn'),
      body = document.querySelector('body');

elNavbarMobToggler.addEventListener('click', ()=> {
    body.classList.toggle('overflow-hidden');
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

function setHeight(el, tablet, reverse = undefined) {
    const elTargetList = el.querySelectorAll('.row.custom-for-col-md-6');
    let dataHeight,
        setterHeight;

    elTargetList.forEach(elTarget => {
        let elCollapseButton = elTarget.querySelector('.collapseButton');
        if (tablet) {
            if (!elCollapseButton.classList.contains('toggle') && reverse == undefined) {
                const gap = parseFloat(getComputedStyle(elTarget).gap);

                let arrayCalculate = calculaterowPerGridHeight(elTarget.children),
                    height = arrayCalculate[0] + arrayCalculate[1]*gap;

                if (height == 0) {
                    height = dataHeight;
                } else {
                    dataHeight = height;
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
                }, 0);
            } else {
                if (reverse) {
                    elTarget.setAttribute('data-height', elTarget.offsetHeight+'px');
                }
                elTarget.setAttribute('style','height: '+elTarget.getAttribute('data-height'));
            }
        } else {
            elTarget.style.removeProperty('height');
        }
    });
};