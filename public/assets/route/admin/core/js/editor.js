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
    [ 'link', 'image','video'],
    [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
    // [{ 'font': [] }],
    [{ 'align': [] }],

    ['clean'],                                         // remove formatting button
];

let defaultOptions = {
    theme: 'snow',
    placeholder: 'Isi lengkap deskripsi...',
    modules: { 
        toolbar: {
            container: toolbarOptions, 
            // handlers: {
            //     'youtube': () => {}
            // }
        },
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
        },
        imageCompressor: {
            quality: 0.9,
            maxWidth: 1000, // default
            maxHeight: 1000, // default
            imageType: 'image/jpeg' // default
        },
    }
};

function getVideoUrl(url) {
    if (url == null) {
        return null;
    }
    
    let match = url.match(/^(?:(https?):\/\/)?(?:(?:www|m)\.)?youtube\.com\/watch.*v=([a-zA-Z0-9_-]+)/) ||
        url.match(/^(?:(https?):\/\/)?(?:(?:www|m)\.)?youtu\.be\/([a-zA-Z0-9_-]+)/) ||
        url.match(/^.*(youtu.be\/|v\/|e\/|u\/\w+\/|embed\/|v=)([^#\&\?]*).*/);

    // console.log(match[2]);

    if (match && match[2].length === 11) {
        return ('https') + '://www.youtube-nocookie.com/embed/' + match[2] + '?showinfo=0';
    }
    if (match = url.match(/^(?:(https?):\/\/)?(?:www\.)?vimeo\.com\/(\d+)/)) { // eslint-disable-line no-cond-assign
        return (match[1] || 'https') + '://player.vimeo.com/video/' + match[2] + '/';
    }
}

let editor = function(el, options = defaultOptions) {
    const icons = Quill.import('ui/icons');
    icons['video'] = '<i class="fab fa-youtube" aria-hidden="true"></i>';

    Quill.register("modules/imageCompressor", imageCompressor);

    let quill = new Quill(el, options);

    let youtubeHandlerFunction = function() {
        let url = prompt("Enter Video URL: ");
        url = url.replace('youtube.com','youtube-nocookie.com');
        url = getVideoUrl(url);
        let range = quill.getSelection();

        if (url != null) {
            quill.insertEmbed(range, 'video', url);
            quill.insertText(range + 2, '');
        }

        if (quill.root.closest('.ql.is-invalid') != null) {
            quill.root.closest('.ql.is-invalid').classList.remove('is-invalid');
        }
    }

    quill.getModule("toolbar").addHandler("video", youtubeHandlerFunction);

    // console.log(quill);

    quill.container.querySelector('.ql-editor').addEventListener('focus', function(e) {
        e.target.closest('.ql').classList.add('focused');
    });
    
    quill.container.querySelector('.ql-editor').addEventListener('focusout', function(e) {
        e.target.closest('.ql').classList.remove('focused');
    });

    quill.container.querySelector('.ql-editor').addEventListener('scroll', function(e) {
        let imgSizer = getSiblings(e.target).filter(e => e.classList == 0);
        if (imgSizer.length) {
            quill.container.querySelector('.ql-editor').click();
        }
    });

    const regEx = /^[0-9a-zA-Z]+$/;
    let ctlPress = false;
    quill.container.querySelector('.ql-editor').addEventListener('keydown', function(e) {
        if (e.key == 'Control') {
            ctlPress = true;
        }
        if (this.classList.contains('ql-blank') && this.closest('.ql.is-invalid') != null) {
            if(e.key.match(regEx) && e.key.length == 1 && !ctlPress) {
                this.closest('.ql.is-invalid').classList.remove('is-invalid');
            }
        }
    }); 

    quill.container.querySelector('.ql-editor').addEventListener('keyup', function(e) {
        if (e.key == 'Control') {
            ctlPress = false;
        } 
    });

    quill.container.querySelector('.ql-editor').addEventListener('paste', function(e) {
        const paste = (e.clipboardData || window.clipboardData).getData("text");
        // console.log(!paste.trim().length);
        if (!paste.trim().length) {
            e.preventDefault();
            return false;
        }

        if (this.classList.contains('ql-blank') && this.closest('.ql.is-invalid') != null) {
            this.closest('.ql.is-invalid').classList.remove('is-invalid');
        }
    });

    return quill;
};