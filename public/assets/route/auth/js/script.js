$("#notif-modal").modal({backdrop: "static"});

// if ( window.history.replaceState ) {
//     window.history.replaceState( null, null, window.location.href );
// }

// Form Control
const formControl = document.querySelectorAll('.form-control');
formControl.forEach(el => {
    el.addEventListener('keypress', function(e) {
        let ceret = e.target.selectionStart;
        if (ceret == 0 && e.keyCode == 32) {
            e.preventDefault();
        }
        if (e.keyCode == 32 && e.target.selectionStart != 0 && e.target.value.indexOf(' ') >= 0 && this.value.substring(e.target.selectionStart, e.target.selectionStart-1) == ' ' && e.target.value.charCodeAt(e.target.selectionStart-1) == 32) {
            e.preventDefault();
        }
    });

    el.addEventListener('paste', function(e) {
        setTimeout(()=>{
            if (el.classList.contains('no-space')) {
                if (e.target.value.indexOf(' ') >= 0) {
                    e.target.value = e.target.value.trim().replace(/\s+/g, "");
                }
            }
            if (e.target.value.indexOf('  ') >= 0) {
                let ceret = e.target.selectionStart;
                e.target.value = e.target.value.trim().replace(/\s+/g, " ");
                e.target.selectionEnd = ceret-1;
            }
        }, 0);
    });
});

let noSpace = function(event) {
    if (event.keyCode === 32) {
        event.preventDefault();
    }
}

let inputNoSpace = document.querySelectorAll('.no-space');
inputNoSpace.forEach(el => {
    el.addEventListener('keypress', function(e) {
        noSpace(e);
    });
});

let inputNoDoubleSpace = document.querySelectorAll('.no-double-space');
inputNoDoubleSpace.forEach(el => {
    el.addEventListener('keydown', function(e) {
        setTimeout(()=>{
            if (e.target.value.indexOf('  ') >= 0) {
                let ceret = e.target.selectionStart;
                e.target.value = e.target.value.trim().replace(/\s+/g, " ");
                e.target.selectionEnd = ceret-1;
            }
        }, 0);
    });
});

// fetch('/auth/signin/token/regenerate', {
//     method: "POST",
//     cache: "no-cache",
//     mode: "same-origin",
//     credentials: "same-origin",
//     headers: {
//         "Content-Type": "application/json",
//     },
//     referrer: "no-referrer",
//     body: JSON.stringify({
//         token: document.querySelector('[name="token"]').value
//     })
// })
// .then(response => response.json())
// .then(function(result) {
    
//     if (result.error == false) {
//         console.log('Failed to get Token, you dont have internet connection');
//     } else {
//         document.querySelector('[name="token"]').value = result.token;
//         fetchTokenChannel.postMessage({
//             token: body.getAttribute('data-token')
//         });
//     }
// });