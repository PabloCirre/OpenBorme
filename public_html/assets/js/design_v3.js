/**
 * OpenBorme Design V3 - Interactivity Engine
 * Focus on precision, micro-animations, and smooth transitions.
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Reveal Animations for Cards (Intersection Observer)
    const cards = document.querySelectorAll('.card, .inst-card, .act-card');
    const revealCallback = (entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('reveal-active');
                observer.unobserve(entry.target);
            }
        });
    };

    const revealObserver = new IntersectionObserver(revealCallback, {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    cards.forEach((card, index) => {
        card.style.transitionDelay = `${index * 0.05}s`;
        card.classList.add('reveal-ready');
        revealObserver.observe(card);
    });

    // 2. Smooth Scrolling for TOC Links
    document.querySelectorAll('.toc-link').forEach(link => {
        link.addEventListener('click', (e) => {
            const targetId = link.getAttribute('href');
            if (targetId.startsWith('#')) {
                e.preventDefault();
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // 3. Dynamic Search Header Focus
    const headerSearch = document.querySelector('.header-search-input');
    const headerWrap = document.querySelector('.header-search-wrap');
    if (headerSearch && headerWrap) {
        headerSearch.addEventListener('focus', () => {
            headerWrap.classList.add('header-focus');
        });
        headerSearch.addEventListener('blur', () => {
            headerWrap.classList.remove('header-focus');
        });
    }

    // 4. Parallax Hero Effect (Subtle)
    const hero = document.querySelector('.hero-v3');
    if (hero) {
        window.addEventListener('scroll', () => {
            const scrolled = window.scrollY;
            if (scrolled < 500) {
                hero.style.transform = `translateY(${scrolled * 0.15}px)`;
                hero.style.opacity = 1 - (scrolled / 500);
            }
        });
    }
});
