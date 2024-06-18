let menu = document.querySelector('#menu-icon');
let navlist = document.querySelector('.navlist');

menu.onclick = () => {
    menu.classList.toggle('bx-x');
    navlist.classList.toggle('open');
};

const sr = ScrollReveal ({
    distance: '65px',
    duration: 2600,
    delay: 450,
    reset: true,
});
sr.reveal('.hero-text',{delay:200, origin:'top'});
sr.reveal('.hero-img',{delay:450, origin:'top'});
sr.reveal('.icons',{delay:450, origin:'left'});
sr.reveal('.scroll-down',{delay:450, origin:'right'});

const nav = document.querySelector(".nav");
let lastScrollY = window.scrollY;

window.addEventListener("scroll", () => {
    if(lastScrollY < window.scrollY){
        nav.classList.add("nav--hidden");
    }else {
        nav.classList.remove("nav--hidden");
    }

    lastScrollY = window.scrollY;
});