const bounchTarget = document.querySelectorAll('table td[data-count-up-value]');

counterUp(bounchTarget, counterSpeed);

bounchRate = document.querySelectorAll('table td[data-count-up-percent][data-bounch-rate-prev]');

bounchRate.forEach(element => {
    const bounchPrevVal = parseFloat(element.getAttribute('data-bounch-rate-prev')),
          curretBounchVal = parseFloat(element.getAttribute('data-count-up-value'));

    if (curretBounchVal > bounchPrevVal) {
        element.classList.add('bounch-rate-inc');
    } else if (bounchPrevVal > curretBounchVal) {
        element.classList.add('bounch-rate-dec');
    } else {
        element.classList.add('bounch-rate-nor');
    }
});