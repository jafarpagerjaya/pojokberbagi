const navbar_about = document.getElementById('navbar-about'),
      header_navbar = document.querySelector(".navbar-area");

let navbar_about_sticky = undefined,
    navbar_about_sticky2 = undefined,
    header_navbar_height = undefined;

setTimeout(()=>{
    navbar_about_sticky = navbar_about.offsetTop;
},50);

let resizeTimeout;
function windowrReSize() {
    clearTimeout(resizeTimeout)
    resizeTimeout = setTimeout(()=>{
        navbar_about.classList.remove('position-sticky');
        navbar_about_sticky = navbar_about.offsetTop;
    }, 50);
}

function stickyNavbarAbout() {
    header_navbar_height = header_navbar.offsetHeight;
    if (window.pageYOffset == 0) {
        header_navbar.style.removeProperty('transition-duration');
        header_navbar.classList.remove("sticky");
    } else if (window.pageYOffset < navbar_about_sticky - header_navbar_height + 1) {
        header_navbar.classList.add("sticky");
        header_navbar.classList.remove("position-absolute");
        header_navbar.style.removeProperty('top');

        navbar_about.classList.remove('position-sticky');
    } else {
        header_navbar.style.transitionDuration = '0s';
        header_navbar.style.top = navbar_about_sticky - header_navbar_height + 'px';
        header_navbar.classList.add("position-absolute");
        header_navbar.classList.remove("sticky");

        navbar_about.classList.add('position-sticky');
    }
    let a = navbar_about.querySelectorAll('a'),
        active = navbar_about.querySelector('a.active');
        
        a.forEach(element => {
            element.parentNode.classList.remove('active');
            element.classList.remove('active');
            let targetSpy = element.getAttribute('href'),
                spyElement = document.querySelector(targetSpy);
                // console.log(window.pageYOffset, spyElement.offsetTop, spyElement.offsetHeight);
            if ((window.pageYOffset >= spyElement.offsetTop) && (window.pageYOffset < spyElement.offsetHeight + spyElement.offsetTop)) {
                element.classList.add('active');
            }
        });

        if (active) {
            active.parentNode.classList.add('active');
        }
    var backToTo = document.querySelector(".scroll-top");
    if (document.body.scrollTop > 50 || document.documentElement.scrollTop > 50) {
        backToTo.style.display = "flex";
    } else {
        backToTo.style.display = "none";
    }
}

window.onresize = windowrReSize;

window.onscroll = stickyNavbarAbout;

const lottiePlayer = document.getElementsByTagName("dotlottie-player"),
    hoverTrigger = document.getElementById("lottie-controller").closest('.col');

hoverTrigger.addEventListener('mouseover', function(event) {
    lottiePlayer[0].play();
});

hoverTrigger.addEventListener('mouseout', function(event) {
    lottiePlayer[0].pause();
});