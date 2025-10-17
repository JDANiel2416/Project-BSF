document.addEventListener('DOMContentLoaded', function () {

    const header = document.querySelector('.main-header');
    let lastScrollTop = 0;

    window.addEventListener('scroll', function () {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        if (scrollTop > lastScrollTop && scrollTop > header.offsetHeight) {
            header.classList.add('header-hidden');
        } else {
            header.classList.remove('header-hidden');
        }
        if (scrollTop > 50) { header.classList.add('header-shadow'); } 
        else { header.classList.remove('header-shadow'); }
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, false);


    const navToggle = document.querySelector('.nav-toggle');
    const mainNav = document.querySelector('.main-nav');
    if (navToggle && mainNav) {
        navToggle.addEventListener('click', () => {
            mainNav.classList.toggle('active');
        });
    }

    const scrollElements = document.querySelectorAll('.scroll-animated');

    const elementObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    scrollElements.forEach(el => {
        elementObserver.observe(el);
    });

});