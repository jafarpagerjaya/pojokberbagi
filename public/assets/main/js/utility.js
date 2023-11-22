const createNewTogglerBtn = function(elParent, i) {
    if (elParent == null) {
        console.log('Object Hidden-Area Parent Cannot be Found');
        return false;
    }

    if (elParent.id == null) {
        console.log('Object Hidden-Area Parent Id Cannot be Null');
        return false;
    }

    const btnAtributes = {
        'data-toggle':'hidden-area-utility',
        'id':'toggle-' + i,
        'data-target': '#'+elParent.id
    };

    const btnContainer = document.createElement('button');
    btnContainer.classList.add('btn','btn-outline-orange','fw-bold','fs-6','rounded-box');
    setMultipleAttributesonElement(btnContainer, btnAtributes);
    const btnText = document.createTextNode('Tampilkan lebih banyak');
    btnContainer.appendChild(btnText);

    const divContainer = document.createElement('div');
    divContainer.classList.add('col-12','d-flex','justify-content-center');
    divContainer.appendChild(btnContainer);

    elParent.parentElement.appendChild(divContainer);
};

if (document.querySelectorAll('.hidden-area-utility').length) {
    let ha = 0;
    document.querySelectorAll('.hidden-area-utility').forEach(hidden => {
        createNewTogglerBtn(hidden, ha++);
    });

    document.querySelectorAll('button[data-toggle="hidden-area-utility"]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const target = document.querySelector(e.target.getAttribute('data-target'));
            if (target.classList.contains('hidden-area-utility')) {
                e.target.innerText = 'Tampilkan lebih sedikit';
            } else {
                e.target.innerText = 'Tampilkan lebih banyak';
                window.location.href = window.location.pathname + e.target.getAttribute('data-target');
            }
            e.target.classList.toggle('btn-outline-orange-reverse');
            e.target.classList.toggle('btn-outline-orange');
            target.classList.toggle('hidden-area-utility');
        });
    });
}

const reverseString = str => [...str].reverse().join('');