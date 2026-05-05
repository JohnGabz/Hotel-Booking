const mobileToggle = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
const menuOpenIcon = document.getElementById('menu-open-icon');
const menuCloseIcon = document.getElementById('menu-close-icon');
const mobileMenuCloseButtons = document.querySelectorAll('[data-mobile-close]');

if (mobileToggle && mobileMenu && menuOpenIcon && menuCloseIcon) {
    mobileToggle.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
        menuOpenIcon.classList.toggle('hidden');
        menuCloseIcon.classList.toggle('hidden');
    });
}

mobileMenuCloseButtons.forEach((button) => {
    button.addEventListener('click', () => {
        if (mobileMenu) {
            mobileMenu.classList.add('hidden');
        }

        if (menuOpenIcon && menuCloseIcon) {
            menuOpenIcon.classList.remove('hidden');
            menuCloseIcon.classList.add('hidden');
        }
    });
});

// Dropdown toggles (notifications, profile, etc.)
document.querySelectorAll('[data-dropdown-toggle]').forEach(btn => {
    const targetId = btn.getAttribute('data-dropdown-toggle');
    const menu = document.getElementById(targetId);
    if (!menu) return;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        menu.classList.toggle('hidden');
    });

    // close on outside click
    document.addEventListener('click', (ev) => {
        if (!menu.classList.contains('hidden') && !menu.contains(ev.target) && ev.target !== btn) {
            menu.classList.add('hidden');
        }
    });
});

// Modal handling
function openModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal(el) {
    const modal = el.closest('[id]');
    if (!modal) return;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

document.querySelectorAll('[data-modal-open]').forEach(btn => {
    const target = btn.getAttribute('data-modal-open');
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        // support passing title/body via data attributes
        const modal = document.getElementById(target);
        if (modal) {
            const title = btn.getAttribute('data-modal-title');
            if (title) {
                const h = modal.querySelector('h3');
                if (h) h.textContent = title;
            }
        }
        openModalById(target);
    });
});

document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const modal = btn.closest('[id]');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    });
});

// Generic action buttons (Create/Edit/Delete) open modal if data-modal-open provided
document.querySelectorAll('.btn-create, .btn-edit, .btn-delete, [data-action-modal]').forEach(btn => {
    btn.addEventListener('click', (e) => {
        const target = btn.getAttribute('data-modal-open') || btn.getAttribute('data-action-modal');
        if (!target) return;
        e.preventDefault();
        const title = btn.getAttribute('data-modal-title') || btn.textContent.trim();
        const modal = document.getElementById(target);
        if (modal) {
            const h = modal.querySelector('h3');
            if (h) h.textContent = title;
        }
        openModalById(target);
    });
});

// Scroll Animations using Intersection Observer (performant)
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            // Determine which animation based on class BEFORE removing
            const hasLeftAnim = entry.target.classList.contains('scroll-animate-in-left');
            const hasRightAnim = entry.target.classList.contains('scroll-animate-in-right');
            const hasUpAnim = entry.target.classList.contains('scroll-animate-up');
            const hasStaggerAnim = entry.target.classList.contains('scroll-animate-stagger');
            
            // Remove animation classes
            entry.target.classList.remove('scroll-animate-in', 'scroll-animate-up', 'scroll-animate-in-left', 'scroll-animate-in-right', 'scroll-animate-stagger');
            
            // Apply the correct animation
            if (hasLeftAnim) {
                entry.target.style.animation = 'slideInLeft 0.6s ease-out forwards';
            } else if (hasRightAnim) {
                entry.target.style.animation = 'slideInRight 0.6s ease-out forwards';
            } else if (hasUpAnim) {
                entry.target.style.animation = 'slideUp 0.6s ease-out forwards';
            } else if (hasStaggerAnim) {
                entry.target.querySelectorAll(':scope > *').forEach((child, index) => {
                    child.style.animation = `slideUp 0.6s ease-out forwards`;
                    child.style.animationDelay = `${(index + 1) * 0.1}s`;
                });
            } else {
                entry.target.style.animation = 'fadeIn 0.6s ease-out forwards';
            }
            
            // Stop observing after animation is triggered
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all scroll-animate elements
document.querySelectorAll('[class*="scroll-animate"]').forEach(el => {
    observer.observe(el);
});

// Observe section shells for staggered animations
document.querySelectorAll('.section-shell').forEach(section => {
    // Add scroll-animate-up to section for fade-in
    section.classList.add('scroll-animate-up');
    observer.observe(section);
});

// Stagger cards in grids
document.querySelectorAll('.grid > [class*="rounded"]').forEach((card, index) => {
    if (!card.classList.contains('scroll-animate-up')) {
        card.style.opacity = '0';
        card.style.animation = `slideUp 0.6s ease-out forwards`;
        card.style.animationDelay = `${(index % 6) * 0.1}s`;
        
        // Observe parent grid
        const grid = card.closest('.grid');
        if (grid && !grid.hasAttribute('data-observed')) {
            grid.setAttribute('data-observed', 'true');
            const tempObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.querySelectorAll('[class*="rounded"]').forEach((child, i) => {
                            child.style.opacity = '0';
                            child.style.animation = `slideUp 0.6s ease-out forwards`;
                            child.style.animationDelay = `${(i % 6) * 0.1}s`;
                        });
                        tempObserver.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            tempObserver.observe(grid);
        }
    }
});

const heroBackgroundUpload = document.getElementById('hero_background_upload');
const heroBackgroundPreview = document.getElementById('hero-background-preview');
const heroBackgroundEmpty = document.getElementById('hero-background-empty');

if (heroBackgroundUpload && heroBackgroundPreview) {
    heroBackgroundUpload.addEventListener('change', () => {
        const file = heroBackgroundUpload.files && heroBackgroundUpload.files[0];

        if (!file) {
            return;
        }

        const previewUrl = URL.createObjectURL(file);
        heroBackgroundPreview.src = previewUrl;
        heroBackgroundPreview.classList.remove('hidden');

        if (heroBackgroundEmpty) {
            heroBackgroundEmpty.classList.add('hidden');
        }
    });
}

