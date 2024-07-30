let selectedId = [];
const checkboxForBtn = function(targetList, multipleSelectedList = null, singgleSelectedList = null, targetDataName = 'data-id', targetParent = 'tr', selectedId = []) {
    let MultiCloneElement = {},
        SinggleCloneELement = {},
        selectedIdOldLenght = '0';
    
    if (multipleSelectedList != null) {
        if (multipleSelectedList.length > 0) {
            multipleSelectedList.forEach(element => {
                element.show;
                MultiCloneElement[element.getAttribute('data-value')] = {
                    element: element.cloneNode(true),
                    parent: element.parentElement
                }
                element.remove();
            });
        }
    }

    if (singgleSelectedList != null) {
        if (singgleSelectedList.length > 0) {
            singgleSelectedList.forEach(element => {
                element.show;
                SinggleCloneELement[element.getAttribute('data-value')] = {
                    element: element.cloneNode(true),
                    parent: element.parentElement
                }
                element.remove();
            });
        }
    }

    const root = document.querySelector('table tbody');
    root.addEventListener('click', function(e) {
        const lebel = document.getElementById('lebel');
        // must input tag with checkbox type
        if (e.target.type == 'checkbox' && e.target.tagName == 'INPUT') {
            const target = e.target,
                  targetTr = target.closest(targetParent),
                  targetId = targetTr.getAttribute(targetDataName);
            if (targetId == null) {
                console.log(targetParent+' [data-id] is null');
                return false;
            }

            if (target.checked) {
                selectedId.push({id: targetId});
            } else {
                selectedId.splice(selectedId.findIndex(item => item.id == targetId), 1);
            }

            if (selectedId.length == 1) {
                if (selectedIdOldLenght === '>1') {
                    for (const property in MultiCloneElement) {
                        MultiCloneElement[property].parent.querySelector('[data-value="'+ property +'"]').remove();
                    }
                }
                for (const property in SinggleCloneELement) {
                    SinggleCloneELement[property].parent.appendChild(SinggleCloneELement[property].element);
                    SinggleCloneELement[property].element.classList.add('show');
                }
                selectedIdOldLenght = '=1';
            } else if (selectedId.length > 1) {
                if (selectedIdOldLenght === '=1') {
                    for (const property in SinggleCloneELement) {
                        if (SinggleCloneELement[property].parent.querySelector('[data-value="'+ property +'"]:not([data-multi])')) {
                            SinggleCloneELement[property].parent.querySelector('[data-value="'+ property +'"]').remove();
                        }
                    }
                }
                for (const property in MultiCloneElement) {
                    MultiCloneElement[property].parent.appendChild(MultiCloneElement[property].element);
                    MultiCloneElement[property].element.classList.add('show');
                }
                selectedIdOldLenght = '>1';
            } else {
                if (selectedIdOldLenght === '=1') {
                    for (const property in SinggleCloneELement) {
                        SinggleCloneELement[property].parent.querySelector('[data-value="'+ property +'"]').remove();
                    }
                }
                if (selectedIdOldLenght === '>1') {
                    for (const property in MultiCloneElement) {
                        MultiCloneElement[property].parent.querySelector('[data-value="'+ property +'"]').remove();
                    }
                }
                selectedIdOldLenght = '0';
            }

            if (lebel != null && selectedId.length > 0) {
                if (lebel.querySelector('small#selected-count') != null) {
                    lebel.querySelector('small#selected-count').setAttribute('data-selected-length',selectedId.length);
                } else {
                    const sDom = '<small id="selected-count" class="text-black-50" data-selected-length="'+ selectedId.length +'"> data sedang dipilih</small>';
                    const frag = document.createRange().createContextualFragment(sDom);
                    lebel.appendChild( frag );
                }
            } else {
                if (lebel.querySelector('small#selected-count') != null) {
                    lebel.querySelector('small#selected-count').remove();
                }
            }
        }
    });

    targetList.forEach(element => {
        if (selectedId.length) {
            if (selectedId.findIndex(item => item.id == element.closest(targetParent).getAttribute(targetDataName)) > -1) {
                element.checked = true;
            }
        }
    });
};