const quill = new Quill(document.querySelector('#campaign'), {
    modules: {
        toolbar: false
    },
    readOnly: true
});

// render the content
quill.setContents(JSON.parse(document.querySelector('#campaign').innerText));