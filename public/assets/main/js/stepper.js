function createNewStepper(thisParrent, objectStepper) {
    const li = document.createElement('li');
    li.classList.add('c-stepper__item');
    li.setAttribute('id', objectStepper.id);

    const dFlex = document.createElement('div');
    dFlex.classList.add('d-flex','flex-column','gap-1');
    li.appendChild(dFlex);

    const h6 = document.createElement('h6');
    h6.classList.add('mb-0','c-stepper__title');

    const spanLongName = document.createElement('span');
    spanLongName.classList.add('d-none','d-md-block');
    const spanLongNameText = document.createTextNode(objectStepper.titleBox);
    spanLongName.appendChild(spanLongNameText);
    h6.appendChild(spanLongName);

    let titleShortBoxArr = objectStepper.titleBox.split(' ');
    titleShortBox = titleShortBoxArr.map(([v])=> v).join('');

    const spanShortName = document.createElement('span');
    spanShortName.classList.add('d-md-none');
    const spanShortText = document.createTextNode(titleShortBox);
    spanShortName.appendChild(spanShortText);
    h6.appendChild(spanShortName);

    dFlex.appendChild(h6);

    const pDesc = document.createElement('p');
    pDesc.classList.add('mb-0','text-sm');
    const pDescText = document.createTextNode(objectStepper.descBox);
    pDesc.appendChild(pDescText);
    dFlex.appendChild(pDesc);

    const spanDate = document.createElement('span');
    spanDate.classList.add('date');
    const spanDateText = document.createTextNode(objectStepper.dateBox);
    spanDate.appendChild(spanDateText);
    dFlex.appendChild(spanDate);

    thisParrent.appendChild(li);

    const stepParent = document.querySelector('.c-stepper');
    let stepList = document.querySelectorAll('.c-stepper > .c-stepper__item'),
        wrapperWidth = 0;
    stepList.forEach(element => {
        wrapperWidth += element.offsetWidth;
    });
    stepParent.scrollLeft = wrapperWidth;
};