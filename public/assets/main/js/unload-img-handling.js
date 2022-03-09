const imgOnErrorList = document.querySelectorAll('img[alt]');
// let defaultBrokenSrcImg = 'https://secure.gravatar.com/avatar?d=wavatar';

imgOnErrorList.forEach(errorImg => {
    setTimeout(() => {
        let isLoaded = errorImg.naturalHeight !== 0;

        if (isLoaded) {
            return;
        }

        if (typeof defaultBrokenSrcImg == 'string') {
            errorImg.src = defaultBrokenSrcImg;
            return;
        }

        let altValue = '(Gambar Rusak) '+errorImg.getAttribute('alt');

        alt = document.createTextNode(altValue);
        errorImg.parentNode.insertBefore(alt,errorImg);
        errorImg.parentNode.removeChild(errorImg);
    });
});