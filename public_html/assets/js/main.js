/**
 * OpenBorme Global Interactivity
 * Centralized logic for keyboard shortcuts and common UI behaviors.
 */

document.addEventListener('DOMContentLoaded', () => {
    const globalSearch = document.getElementById('global-search');
    const navMenu = document.getElementById('nav-menu');
    const sidebar = document.querySelector('.sidebar');

    document.addEventListener('keydown', (e) => {
        const isInput = ['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName);

        // '/' to focus search
        if (e.key === '/' && !isInput) {
            e.preventDefault();
            globalSearch?.focus();
        }

        // 'f' to toggle filters/sidebar
        if (e.key === 'f' && !isInput) {
            if (sidebar) {
                sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
            }
        }

        // 'Esc' to blur/close
        if (e.key === 'Escape') {
            document.activeElement.blur();
            if (navMenu?.classList.contains('active')) {
                navMenu.classList.remove('active');
            }
        }
    });

    // Mobile Menu Toggle logic (moved from inline onclick)
    const mobileBtn = document.querySelector('.mobile-menu-btn');
    if (mobileBtn && navMenu) {
        mobileBtn.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }
});
