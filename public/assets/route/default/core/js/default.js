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