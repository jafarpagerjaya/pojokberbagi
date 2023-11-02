let toolbarOptions = [
    ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
    ['blockquote', 'code-block'],

    [{ 'header': 1 }, { 'header': 2 }],               // custom button values
    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
    // [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
    [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
    // [{ 'direction': 'rtl' }],                         // text direction

    // [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
    // [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
    // [ 'link', 'image', 'video', 'formula' ],          // add's image support
    [ 'link', 'image'],
    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    // [{ 'font': [] }],
    [{ 'align': [] }],

    ['clean']                                         // remove formatting button
];

let defaultOptions = {
    theme: 'snow',
    placeholder: 'Isi lengkap deskripsi...',
    modules: { 
        toolbar: toolbarOptions, 
        imageDrop: true,
        imageResize: {
            handleStyles: {
                borderRadius: '50%',
                backgroundColor: 'var(--orange)',
                border: 'none'
            },
            displayStyles: {
                backgroundColor: 'black',
                border: 'none',
                color: 'white',
                borderRadius: '.375rem'
            }
        }
    }
};

let editor = function(el, options = defaultOptions) {
    let quill = new Quill(el, options);
    
    // console.log(quill);

    quill.container.querySelector('.ql-editor').addEventListener('focus', function(e) {
        e.target.closest('.ql').classList.add('focused');
    });
    
    quill.container.querySelector('.ql-editor').addEventListener('focusout', function(e) {
        e.target.closest('.ql').classList.remove('focused');
    });

    return quill;
};