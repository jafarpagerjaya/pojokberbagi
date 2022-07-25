const stepParent = document.querySelector('.c-stepper');
let stepList = document.querySelectorAll('.c-stepper > .c-stepper__item'),
    wrapperWidth = 0;
stepList.forEach(element => {
    wrapperWidth += element.offsetWidth;
});
stepParent.scrollLeft = wrapperWidth;