const html = document.documentElement;
const globalLoader = document.getElementById('global-loader');

let loaderCompletionTimer = null;
let pendingFetchCount = 0;
const originalFetch = window.fetch.bind(window);

function showGlobalLoader() {
    if (!globalLoader) return;

    window.clearTimeout(loaderCompletionTimer);
    html.classList.add('is-loading');
    html.classList.remove('is-completing');
}

function completeGlobalLoader() {
    if (!globalLoader || !html.classList.contains('is-loading')) return;

    html.classList.add('is-completing');
    window.clearTimeout(loaderCompletionTimer);
    loaderCompletionTimer = window.setTimeout(() => {
        html.classList.remove('is-loading', 'is-completing');
    }, 320);
}

window.VillaLoader = {
    show: showGlobalLoader,
    complete: completeGlobalLoader,
    fetch: (...args) => originalFetch(...args),
};

// Provide a helper to fetch without triggering the global loader
window.fetchWithoutLoader = (...args) => originalFetch(...args);

let calendarTouchStartX = null;
let calendarTouchStartY = null;
let calendarTouchFromScrollable = false;
let selectedCalendarDate = null;
const ENABLE_CALENDAR_SWIPE = false;

function updateSelectedDateBar(dateString) {
    const selectedBar = document.getElementById('calendar-selected-bar');
    const selectedDateLabel = document.getElementById('calendar-selected-date');
    if (!selectedBar || !selectedDateLabel) return;

    if (!dateString) {
        selectedDateLabel.textContent = 'None';
        selectedBar.classList.add('hidden');
        return;
    }

    const parsed = new Date(`${dateString}T00:00:00`);
    const formatted = Number.isNaN(parsed.getTime())
        ? dateString
        : parsed.toLocaleDateString(undefined, {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });

    selectedDateLabel.textContent = formatted;
    selectedBar.classList.remove('hidden');
}

function applyCalendarSelection() {
    document.querySelectorAll('[data-calendar-day]').forEach((dayEl) => {
        const isSelected = selectedCalendarDate && dayEl.getAttribute('data-date') === selectedCalendarDate;
        dayEl.classList.toggle('is-selected', Boolean(isSelected));
        dayEl.setAttribute('aria-selected', isSelected ? 'true' : 'false');
    });

    updateSelectedDateBar(selectedCalendarDate);
}

function replaceCalendarFromHtml(html) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newCal = doc.querySelector('#room-calendar');
    const curCal = document.querySelector('#room-calendar');
    if (!newCal || !curCal) return false;

    curCal.replaceWith(newCal);
    newCal.classList.add('calendar-anim-enter');
    window.setTimeout(() => newCal.classList.remove('calendar-anim-enter'), 280);

    // Reset selection when month changes and keep UI states in sync.
    selectedCalendarDate = null;
    applyCalendarSelection();
    return true;
}

function loadCalendar(url, pushHistory = true) {
    const transport = window.fetchWithoutLoader || originalFetch;
    return transport(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(resp => resp.text())
        .then(html => {
            const replaced = replaceCalendarFromHtml(html);
            if (replaced && pushHistory) {
                window.history.pushState({}, '', url);
            }
            return replaced;
        })
        .catch(() => false);
}

// AJAX calendar navigation: intercept clicks and replace full calendar shell.
document.addEventListener('click', (event) => {
    const ajaxLink = event.target.closest('a.ajax-calendar-nav');
    if (!ajaxLink) return;
    event.preventDefault();
    loadCalendar(ajaxLink.href, true);
});

// Select open dates with clear, persistent highlighting.
document.addEventListener('click', (event) => {
    const dayEl = event.target.closest('[data-calendar-day]');
    if (!dayEl) return;

    if (dayEl.getAttribute('data-selectable') !== '1') {
        return;
    }

    selectedCalendarDate = dayEl.getAttribute('data-date');
    applyCalendarSelection();

    // Populate and open booking modal after selecting an open day.
    if (window.bookingModal && selectedCalendarDate) {
        const start = new Date(`${selectedCalendarDate}T00:00:00`);
        const end = new Date(start);
        end.setDate(end.getDate() + 1);
        const checkOut = end.toISOString().slice(0, 10);
        window.bookingModal.setDates(selectedCalendarDate, checkOut);
        window.bookingModal.open();
    }
});

// Swipe gesture support for mobile month navigation.
document.addEventListener('touchstart', (event) => {
    if (!ENABLE_CALENDAR_SWIPE) return;
    const shell = event.target.closest('[data-calendar-swipe]');
    if (!shell || !event.touches[0]) return;
    calendarTouchStartX = event.touches[0].clientX;
    calendarTouchStartY = event.touches[0].clientY;
    calendarTouchFromScrollable = Boolean(event.target.closest('.room-calendar-scroll'));
}, { passive: true });

document.addEventListener('touchend', (event) => {
    if (!ENABLE_CALENDAR_SWIPE) return;
    const shell = event.target.closest('[data-calendar-swipe]');
    if (!shell || calendarTouchStartX === null || calendarTouchStartY === null || !event.changedTouches[0]) {
        calendarTouchStartX = null;
        calendarTouchStartY = null;
        calendarTouchFromScrollable = false;
        return;
    }

    const dx = event.changedTouches[0].clientX - calendarTouchStartX;
    const dy = event.changedTouches[0].clientY - calendarTouchStartY;
    calendarTouchStartX = null;
    calendarTouchStartY = null;

    // Do not trigger month navigation when user is panning the scrollable calendar track.
    if (calendarTouchFromScrollable) {
        calendarTouchFromScrollable = false;
        return;
    }
    calendarTouchFromScrollable = false;

    if (Math.abs(dx) < 90 || Math.abs(dx) < Math.abs(dy) * 1.2) return;

    const targetUrl = dx > 0
        ? shell.getAttribute('data-calendar-prev-url')
        : shell.getAttribute('data-calendar-next-url');

    if (targetUrl) {
        loadCalendar(targetUrl, true);
    }
}, { passive: true });

window.addEventListener('popstate', () => {
    const path = `${window.location.pathname}${window.location.search}`;
    loadCalendar(path, false);
});

window.addEventListener('load', () => {
    if (pendingFetchCount === 0) {
        completeGlobalLoader();
    }
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted && pendingFetchCount === 0) {
        completeGlobalLoader();
    }
});

window.fetch = async (...args) => {
    pendingFetchCount += 1;
    showGlobalLoader();

    try {
        return await originalFetch(...args);
    } finally {
        pendingFetchCount = Math.max(0, pendingFetchCount - 1);

        if (pendingFetchCount === 0) {
            completeGlobalLoader();
        }
    }
};

document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href]');
    if (!link) return;

    if (
        link.hasAttribute('download') ||
        link.target && link.target !== '_self' ||
        link.hasAttribute('data-no-loader')
    ) {
        return;
    }

    const href = link.getAttribute('href') || '';
    if (
        href.startsWith('#') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:') ||
        href.startsWith('javascript:')
    ) {
        return;
    }

    try {
        const url = new URL(link.href, window.location.href);
        if (url.origin !== window.location.origin) return;
    } catch {
        return;
    }

    showGlobalLoader();
});

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement)) return;
    if (form.hasAttribute('data-no-loader') || form.target && form.target !== '_self') return;

    showGlobalLoader();
});

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
        // If a generic action form exists, populate its action and method from data attributes
        const genericForm = document.getElementById('generic-action-form');
        if (genericForm) {
            const actionUrl = btn.getAttribute('data-action-url') || btn.getAttribute('href') || genericForm.getAttribute('action') || '';
            const actionMethod = (btn.getAttribute('data-action-method') || btn.getAttribute('data-method') || 'POST').toUpperCase();

            // Set form action
            try { genericForm.action = actionUrl; } catch (err) { /* ignore */ }

            // Ensure method override input exists when method is not POST
            let methodInput = genericForm.querySelector('input[name="_method"]');
            if (actionMethod !== 'POST') {
                if (!methodInput) {
                    methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    genericForm.appendChild(methodInput);
                }
                methodInput.value = actionMethod;
                genericForm.method = 'POST';
            } else {
                if (methodInput) {
                    methodInput.remove();
                }
                genericForm.method = 'POST';
            }
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

// Global image fallback: replace broken images with a sensible default
(() => {
    const FALLBACK_IMAGE = 'https://images.unsplash.com/photo-1505691723518-36a4cdbb1f2a?auto=format&fit=crop&w=1400&q=80';

    // Use capture phase so we catch load errors from delegated images
    document.addEventListener('error', (ev) => {
        const el = ev.target;
        if (!(el instanceof HTMLImageElement)) return;
        if (el.dataset.fallbackApplied === '1') return;

        el.dataset.fallbackApplied = '1';
        try { el.src = FALLBACK_IMAGE; } catch (e) { /* ignore */ }
    }, true);
})();

// Simple carousel auto-advance + controls for any element with `data-room-carousel`
(() => {
    const CAROUSEL_INTERVAL = 4000; // ms

    document.querySelectorAll('[data-room-carousel]').forEach((carouselEl) => {
        const slides = Array.from(carouselEl.querySelectorAll('[data-carousel-slide]'));
        if (!slides.length) return;

        const thumbs = Array.from(carouselEl.querySelectorAll('[data-carousel-thumb]'));
        const prevBtn = carouselEl.querySelector('[data-carousel-prev]');
        const nextBtn = carouselEl.querySelector('[data-carousel-next]');

        let current = 0;
        let timerId = null;

        const show = (index) => {
            index = (index + slides.length) % slides.length;
            current = index;

            slides.forEach((s, i) => {
                if (i === index) {
                    s.classList.remove('opacity-0', 'pointer-events-none');
                    s.classList.add('opacity-100');
                } else {
                    s.classList.remove('opacity-100');
                    s.classList.add('opacity-0', 'pointer-events-none');
                }
            });

            thumbs.forEach((t, i) => {
                if (i === index) {
                    t.classList.add('ring-2', 'ring-white');
                    t.classList.remove('opacity-70');
                } else {
                    t.classList.remove('ring-2', 'ring-white');
                    t.classList.add('opacity-70');
                }
            });
        };

        const next = () => show(current + 1);
        const prev = () => show(current - 1);

        const start = () => {
            stop();
            timerId = window.setInterval(next, CAROUSEL_INTERVAL);
        };

        const stop = () => {
            if (timerId) {
                window.clearInterval(timerId);
                timerId = null;
            }
        };

        // Wire controls
        if (nextBtn) nextBtn.addEventListener('click', (e) => { e.preventDefault(); next(); start(); });
        if (prevBtn) prevBtn.addEventListener('click', (e) => { e.preventDefault(); prev(); start(); });

        thumbs.forEach((t, i) => {
            t.addEventListener('click', (e) => { e.preventDefault(); show(i); start(); });
        });

        // Pause on hover/focus
        carouselEl.addEventListener('mouseenter', stop);
        carouselEl.addEventListener('mouseleave', start);
        carouselEl.addEventListener('focusin', stop);
        carouselEl.addEventListener('focusout', start);

        // Initialize
        show(0);
        if (slides.length > 1) start();
    });
})();

// Location tab toggle for landing page (Map / Details)
(() => {
    const tabButtons = Array.from(document.querySelectorAll('[data-location-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-location-panel]'));

    if (!tabButtons.length || !panels.length) return;

    const isDesktop = () => window.matchMedia('(min-width: 768px)').matches;

    const showPanel = (name) => {
        panels.forEach(p => {
            const pName = p.getAttribute('data-location-panel');
            if (isDesktop()) {
                // on desktop show both panels side-by-side
                p.classList.remove('hidden');
            } else {
                if (pName === name) {
                    p.classList.remove('hidden');
                } else {
                    p.classList.add('hidden');
                }
            }
        });

        tabButtons.forEach(b => {
            const target = b.getAttribute('data-location-target');
            const pressed = target === name ? 'true' : 'false';
            b.setAttribute('aria-pressed', pressed);
            if (pressed === 'true') {
                b.classList.remove('text-stone-600');
                b.classList.add('text-stone-800');
            } else {
                b.classList.remove('text-stone-800');
                b.classList.add('text-stone-600');
            }
        });
    };

    tabButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const target = btn.getAttribute('data-location-target');
            showPanel(target);
        });
    });

    // ensure panels update on resize
    let resizeTimer = null;
    window.addEventListener('resize', () => {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(() => showPanel(document.querySelector('[data-location-tab][aria-pressed="true"]')?.getAttribute('data-location-target') || 'map'), 120);
    });

    // initial state
    showPanel('map');
})();

