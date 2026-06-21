import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

const html = document.documentElement;
const globalLoader = document.getElementById('global-loader');

let loaderCompletionTimer = null;
let pendingFetchCount = 0;
const originalFetch = window.fetch.bind(window);
const FETCH_TIMEOUT_MS = 20000;
const focusableSelector = [
    'a[href]',
    'area[href]',
    'button:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    'textarea:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');
const modalState = new Map();
let activeModal = null;

function getFocusableElements(container) {
    return Array.from(container.querySelectorAll(focusableSelector))
        .filter((element) => element.offsetParent !== null || element === document.activeElement);
}

function lockBodyScroll(lock) {
    document.body.classList.toggle('overflow-hidden', lock);
}

function trapFocus(event) {
    if (!activeModal || event.key !== 'Tab') return;

    const focusable = getFocusableElements(activeModal);
    if (!focusable.length) {
        event.preventDefault();
        activeModal.focus();
        return;
    }

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}

function openAccessibleModal(modal) {
    if (!modal) return;

    modalState.set(modal, { previousFocus: document.activeElement });
    activeModal = modal;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    modal.removeAttribute('hidden');
    modal.setAttribute('aria-hidden', 'false');
    lockBodyScroll(true);

    window.setTimeout(() => {
        const focusTarget = getFocusableElements(modal)[0] || modal;
        focusTarget.focus({ preventScroll: true });
    }, 0);
}

function closeAccessibleModal(modal) {
    if (!modal) return;

    const previousFocus = modalState.get(modal)?.previousFocus;
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    modal.setAttribute('hidden', '');
    modal.setAttribute('aria-hidden', 'true');
    modalState.delete(modal);
    activeModal = null;
    lockBodyScroll(false);

    if (previousFocus && typeof previousFocus.focus === 'function') {
        previousFocus.focus({ preventScroll: true });
    }
}

function modalHasUnsavedChanges(modal) {
    const form = modal?.querySelector?.('[data-landing-section-form]');

    return form?.dataset.dirty === '1' && form?.dataset.saving !== '1';
}

function resetModalFormState(modal) {
    const form = modal?.querySelector?.('[data-landing-section-form]');
    if (!form) return;

    form.reset();
    form.dataset.dirty = '0';
    form.querySelector('[data-landing-section-submit]')?.setAttribute('disabled', 'disabled');
    form.querySelectorAll('.form-input.error').forEach((input) => input.classList.remove('error'));
    form.querySelectorAll('[data-field-error]').forEach((error) => {
        error.textContent = '';
        error.classList.add('hidden');
    });
    form.querySelector('[data-form-summary]')?.classList.add('hidden');

    form.querySelectorAll('[data-image-input]').forEach((input) => {
        const preview = document.getElementById(input.dataset.previewTarget || '');
        const empty = document.getElementById(input.dataset.emptyTarget || '');
        const removeInput = document.getElementById(input.dataset.removeTarget || '');
        const initialSrc = preview?.dataset.initialSrc || '';

        input.value = '';
        if (removeInput) removeInput.value = '0';

        if (preview) {
            preview.src = initialSrc;
            preview.classList.toggle('hidden', initialSrc === '');
        }

        if (empty) {
            empty.classList.toggle('hidden', empty.dataset.initialEmpty !== '1');
        }
    });
}

function requestCloseAccessibleModal(modal) {
    if (!modal) return false;

    if (modalHasUnsavedChanges(modal)) {
        if (!window.confirm('Discard unsaved changes?')) {
            return false;
        }

        resetModalFormState(modal);
    }

    closeAccessibleModal(modal);
    return true;
}

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && activeModal) {
        event.preventDefault();
        requestCloseAccessibleModal(activeModal);
        return;
    }

    trapFocus(event);
});

function showErrorModal(title, message, errors = null) {
    const modal = document.getElementById('error-modal');
    const titleEl = document.getElementById('error-modal-title');
    const messageEl = document.getElementById('error-modal-message');
    const listEl = document.getElementById('error-modal-list');

    if (!modal || !titleEl || !messageEl || !listEl) {
        window.alert(message || title || 'Something went wrong.');
        return;
    }

    titleEl.textContent = title || 'Something went wrong';
    messageEl.textContent = message || 'Please try again in a moment.';
    listEl.innerHTML = '';

    const flattenedErrors = errors ? Object.values(errors).flat().filter(Boolean) : [];

    if (flattenedErrors.length) {
        flattenedErrors.forEach((error) => {
            const item = document.createElement('li');
            item.textContent = error;
            listEl.appendChild(item);
        });
        listEl.classList.remove('hidden');
    } else {
        listEl.classList.add('hidden');
    }

    openAccessibleModal(modal);
}

function hideErrorModal() {
    const modal = document.getElementById('error-modal');
    if (!modal) return;

    closeAccessibleModal(modal);
}

function friendlyHttpMessage(status) {
    if (status === 401) return ['Sign in required', 'Please sign in again before continuing.'];
    if (status === 403) return ['This area is restricted', 'You do not have permission to perform this action.'];
    if (status === 404) return ['We could not find that', 'The requested item may have been moved or deleted.'];
    if (status === 419) return ['Your session expired', 'Please refresh the page and try again.'];
    if (status === 422) return ['Some details need attention', 'Please fix the highlighted fields and try again.'];
    if (status >= 500) return ['Server error', 'Something went wrong on our side. Please try again in a moment.'];
    return ['Request failed', 'We could not complete that request. Please try again.'];
}

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

window.VillaError = {
    show: showErrorModal,
    hide: hideErrorModal,
};

window.VillaModal = {
    open: openAccessibleModal,
    close: closeAccessibleModal,
    requestClose: requestCloseAccessibleModal,
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

function addDaysToDateString(dateString, days) {
    const [year, month, day] = dateString.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    date.setDate(date.getDate() + days);

    const yyyy = date.getFullYear();
    const mm = String(date.getMonth() + 1).padStart(2, '0');
    const dd = String(date.getDate()).padStart(2, '0');

    return `${yyyy}-${mm}-${dd}`;
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
        const checkOut = addDaysToDateString(selectedCalendarDate, 1);
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

// Availability check helper for landing page quick form
function debounce(fn, wait = 350) {
    let t;
    return (...args) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
    };
}

async function checkAvailability(payload) {
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        const resp = await fetch('/availability/check', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        });

        if (!resp.ok) {
            return { error: true };
        }

        return await resp.json();
    } catch (err) {
        return { error: true };
    }
}

function initLandingAvailability() {
    const form = document.querySelector('form[action="/bookings/search"]');
    if (!form) return;

    const checkIn = form.querySelector('input[name="check_in"]');
    const checkOut = form.querySelector('input[name="check_out"]');
    const submit = form.querySelector('button[type="submit"]');

    const statusEl = document.createElement('div');
    statusEl.className = 'mt-3 text-sm';
    submit.parentNode.insertBefore(statusEl, submit);

    const doCheck = debounce(async () => {
        if (!checkIn.value || !checkOut.value) {
            statusEl.textContent = '';
            submit.disabled = false;
            return;
        }

        statusEl.textContent = 'Checking availability...';
        submit.disabled = true;

        const result = await checkAvailability({ check_in: checkIn.value, check_out: checkOut.value });

        if (result.error) {
            statusEl.textContent = 'Unable to check availability right now.';
            submit.disabled = false;
            return;
        }

        if (result.available) {
            statusEl.innerHTML = '<span class="text-emerald-600">Available — you can proceed to search or book.</span>';
            submit.disabled = false;
        } else {
            const suggestion = result.suggestions && result.suggestions[0];
            if (suggestion) {
                statusEl.innerHTML = `<span class="text-amber-700">No rooms available for selected dates. Try ${suggestion.start} to ${suggestion.end}.</span>`;
            } else {
                statusEl.innerHTML = '<span class="text-amber-700">No rooms available for selected dates.</span>';
            }
            submit.disabled = false;
        }
    }, 400);

    checkIn.addEventListener('change', doCheck);
    checkOut.addEventListener('change', doCheck);
}

document.addEventListener('DOMContentLoaded', () => initLandingAvailability());

window.addEventListener('pageshow', (event) => {
    if (event.persisted && pendingFetchCount === 0) {
        completeGlobalLoader();
    }
});

window.fetch = async (...args) => {
    pendingFetchCount += 1;
    showGlobalLoader();

    const [input, init = {}] = args;
    const controller = init.signal ? null : new AbortController();
    const timeout = controller ? window.setTimeout(() => controller.abort(), FETCH_TIMEOUT_MS) : null;

    try {
        const response = await originalFetch(input, controller ? { ...init, signal: controller.signal } : init);

        if (!response.ok) {
            const [fallbackTitle, fallbackMessage] = friendlyHttpMessage(response.status);
            let payload = null;

            try {
                const contentType = response.headers.get('content-type') || '';
                if (contentType.includes('application/json')) {
                    payload = await response.clone().json();
                }
            } catch {
                payload = null;
            }

            showErrorModal(
                payload?.title || fallbackTitle,
                payload?.message || fallbackMessage,
                payload?.errors || null
            );
        }

        return response;
    } catch (error) {
        const timedOut = error?.name === 'AbortError';
        showErrorModal(
            timedOut ? 'Request timed out' : 'Network problem',
            timedOut
                ? 'The request took too long. Please check your connection and try again.'
                : 'We could not reach the server. Please check your connection and try again.'
        );
        throw error;
    } finally {
        if (timeout) {
            window.clearTimeout(timeout);
        }

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

document.addEventListener('click', (event) => {
    if (event.target.closest('[data-error-modal-close]')) {
        event.preventDefault();
        hideErrorModal();
    }
});

window.addEventListener('error', (event) => {
    if (event.target instanceof HTMLImageElement) return;
    showErrorModal('Page error', 'Something on this page did not load correctly. Please refresh and try again.');
});

window.addEventListener('unhandledrejection', () => {
    showErrorModal('Request interrupted', 'A background request did not finish correctly. Please try again.');
});

const mobileToggle = document.getElementById('mobile-menu-btn');
const mobileMenu = document.getElementById('mobile-menu');
const menuOpenIcon = document.getElementById('menu-open-icon');
const menuCloseIcon = document.getElementById('menu-close-icon');
const mobileMenuCloseButtons = document.querySelectorAll('[data-mobile-close]');

if (mobileToggle && mobileMenu && menuOpenIcon && menuCloseIcon) {
    mobileToggle.addEventListener('click', () => {
        const willOpen = mobileMenu.classList.contains('hidden');
        mobileMenu.classList.toggle('hidden', !willOpen);
        menuOpenIcon.classList.toggle('hidden', willOpen);
        menuCloseIcon.classList.toggle('hidden', !willOpen);
        mobileToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        lockBodyScroll(willOpen && mobileMenu.classList.contains('fixed'));
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

        mobileToggle?.setAttribute('aria-expanded', 'false');
        lockBodyScroll(false);
    });
});

// Dropdown toggles (notifications, profile, etc.)
document.querySelectorAll('[data-dropdown-toggle]').forEach(btn => {
    const targetId = btn.getAttribute('data-dropdown-toggle');
    const menu = document.getElementById(targetId);
    if (!menu) return;

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const willOpen = menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !willOpen);
        btn.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    });

    // close on outside click
    document.addEventListener('click', (ev) => {
        if (!menu.classList.contains('hidden') && !menu.contains(ev.target) && ev.target !== btn) {
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        }
    });
});

// Modal handling
function resolveModalFromTrigger(el) {
    if (!el) return null;

    return el.closest('[role="dialog"]') || el.closest('.fixed.inset-0.z-50');
}

function openModalById(id) {
    const modal = document.getElementById(id);
    if (!modal) return;

    modal.querySelectorAll('form').forEach((form) => {
        form.hidden = false;
        form.classList.remove('hidden');
        form.style.display = '';
    });

    openAccessibleModal(modal);

    if (typeof window.initImageInputToggles === 'function') {
        window.initImageInputToggles(modal);
    }
    if (typeof window.initRoomTypeModal === 'function') {
        window.initRoomTypeModal(modal);
    }
}

function closeModal(el) {
    const modal = resolveModalFromTrigger(el);
    if (!modal) return;
    requestCloseAccessibleModal(modal);
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
        const modal = resolveModalFromTrigger(btn);
        if (modal) {
            requestCloseAccessibleModal(modal);
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

document.querySelectorAll('img:not([loading])').forEach((image) => {
    const nearTop = image.closest('header, [data-priority-image]');
    image.loading = nearTop ? 'eager' : 'lazy';
    image.decoding = 'async';
});

document.querySelectorAll('[data-amenity-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const card = button.closest('[data-amenity-card]');
        const disabled = button.dataset.disabled === '1';

        button.dataset.disabled = disabled ? '0' : '1';
        button.textContent = disabled ? 'Disable' : 'Enable';
        card?.classList.toggle('opacity-50', !disabled);
    });
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

(() => {
    const forms = Array.from(document.querySelectorAll('[data-landing-section-form]'));
    if (!forms.length) return;

    const transport = window.fetchWithoutLoader || originalFetch;

    const showToast = (message, type = 'success') => {
        let region = document.getElementById('landing-settings-toast-region');

        if (!region) {
            region = document.createElement('div');
            region.id = 'landing-settings-toast-region';
            region.className = 'fixed right-4 top-4 z-[90] flex w-[calc(100%-2rem)] max-w-sm flex-col gap-3';
            region.setAttribute('aria-live', 'polite');
            document.body.appendChild(region);
        }

        const toast = document.createElement('div');
        toast.className = `rounded-lg border px-4 py-3 text-sm shadow-lg ${
            type === 'error'
                ? 'border-red-200 bg-red-50 text-red-800'
                : 'border-emerald-200 bg-emerald-50 text-emerald-800'
        }`;
        toast.textContent = message;
        region.appendChild(toast);

        window.setTimeout(() => toast.remove(), 4200);
    };

    const clearErrors = (form) => {
        form.querySelectorAll('.form-input.error').forEach((input) => input.classList.remove('error'));
        form.querySelectorAll('[data-field-error]').forEach((error) => {
            error.textContent = '';
            error.classList.add('hidden');
        });

        const summary = form.querySelector('[data-form-summary]');
        if (summary) {
            summary.textContent = '';
            summary.classList.add('hidden');
        }
    };

    const showFieldError = (form, name, message) => {
        const field = form.querySelector(`[name="${CSS.escape(name)}"]`);
        const error = form.querySelector(`[data-field-error="${CSS.escape(name)}"]`);

        field?.classList.add('error');

        if (error) {
            error.textContent = message;
            error.classList.remove('hidden');
        }
    };

    const validateLandingForm = (form) => {
        clearErrors(form);
        const errors = [];

        form.querySelectorAll('input[name], textarea[name]').forEach((field) => {
            if (field.type === 'hidden' || field.name === '_token') return;

            const label = field.dataset.fieldLabel || field.name;
            const value = field.value.trim();
            const max = Number(field.dataset.maxlength || field.getAttribute('maxlength') || 0);

            if (max && value.length > max) {
                errors.push([field.name, `${label} must be ${max} characters or fewer.`]);
            }

            if (field.type === 'email' && value && !field.validity.valid) {
                errors.push([field.name, `${label} must be a valid email address.`]);
            }

            if (field.type === 'url' && value && !field.validity.valid) {
                errors.push([field.name, `${label} must be a valid URL.`]);
            }

            if (field.type === 'file' && field.files?.[0]) {
                const file = field.files[0];

                if (!file.type.startsWith('image/')) {
                    errors.push([field.name, `${label} must be an image file.`]);
                }

                if (file.size > 5 * 1024 * 1024) {
                    errors.push([field.name, `${label} must be 5 MB or smaller.`]);
                }
            }
        });

        errors.forEach(([name, message]) => showFieldError(form, name, message));

        const summary = form.querySelector('[data-form-summary]');
        if (summary && errors.length) {
            summary.textContent = 'Please fix the highlighted fields before saving.';
            summary.classList.remove('hidden');
        }

        return errors.length === 0;
    };

    const flattenServerErrors = (errors) => Object.entries(errors || {})
        .flatMap(([name, messages]) => (Array.isArray(messages) ? messages : [messages]).map((message) => [name, message]))
        .filter(([, message]) => message);

    const syncFormDefaults = (form) => {
        form.querySelectorAll('input[name], textarea[name]').forEach((field) => {
            if (field.type === 'file') {
                field.value = '';
                return;
            }

            if (field.dataset.imageRemoveInput !== undefined) {
                field.value = '0';
            }

            field.defaultValue = field.value;
        });

        form.querySelectorAll('[data-image-input]').forEach((input) => {
            const preview = document.getElementById(input.dataset.previewTarget || '');
            const empty = document.getElementById(input.dataset.emptyTarget || '');

            if (preview) {
                preview.dataset.initialSrc = preview.classList.contains('hidden') ? '' : preview.src;
            }

            if (empty) {
                empty.dataset.initialEmpty = empty.classList.contains('hidden') ? '0' : '1';
            }
        });
    };

    forms.forEach((form) => {
        const submit = form.querySelector('[data-landing-section-submit]');

        const markDirty = () => {
            form.dataset.dirty = '1';
            submit?.removeAttribute('disabled');
        };

        form.addEventListener('input', markDirty);
        form.addEventListener('change', markDirty);

        form.querySelectorAll('[data-image-input]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files?.[0];
                const preview = document.getElementById(input.dataset.previewTarget || '');
                const empty = document.getElementById(input.dataset.emptyTarget || '');
                const removeInput = document.getElementById(input.dataset.removeTarget || '');

                if (!file || !preview) return;

                if (removeInput) removeInput.value = '0';
                preview.src = URL.createObjectURL(file);
                preview.classList.remove('hidden');
                empty?.classList.add('hidden');
            });
        });

        form.querySelectorAll('[data-image-dropzone]').forEach((dropzone) => {
            const input = dropzone.querySelector('[data-image-input]');
            if (!input) return;

            ['dragenter', 'dragover'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.add('border-brand-primary', 'bg-brand-primary/5');
                });
            });

            ['dragleave', 'drop'].forEach((eventName) => {
                dropzone.addEventListener(eventName, (event) => {
                    event.preventDefault();
                    dropzone.classList.remove('border-brand-primary', 'bg-brand-primary/5');
                });
            });

            dropzone.addEventListener('drop', (event) => {
                const files = event.dataTransfer?.files;
                if (!files?.length) return;

                input.files = files;
                input.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });

        form.querySelectorAll('[data-image-clear]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.imageClear || '');
                const preview = document.getElementById(input?.dataset.previewTarget || '');
                const empty = document.getElementById(input?.dataset.emptyTarget || '');
                const removeInput = document.getElementById(input?.dataset.removeTarget || '');

                if (input) input.value = '';
                if (removeInput) removeInput.value = '1';
                if (preview) {
                    preview.removeAttribute('src');
                    preview.classList.add('hidden');
                }
                empty?.classList.remove('hidden');
                markDirty();
            });
        });

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (!validateLandingForm(form)) {
                return;
            }

            const modal = resolveModalFromTrigger(form);
            const card = document.querySelector(`[data-landing-section-card="${CSS.escape(form.dataset.sectionId || '')}"]`);

            form.dataset.saving = '1';
            submit?.setAttribute('disabled', 'disabled');
            card?.classList.add('opacity-60', 'pointer-events-none');
            window.VillaLoader?.show();

            try {
                const response = await transport(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    if (response.status === 422 && payload.errors) {
                        const serverErrors = flattenServerErrors(payload.errors);
                        serverErrors.forEach(([name, message]) => showFieldError(form, name, message));

                        const summary = form.querySelector('[data-form-summary]');
                        if (summary) {
                            summary.textContent = payload.message || 'Please fix the highlighted fields before saving.';
                            summary.classList.remove('hidden');
                        }
                        return;
                    }

                    throw new Error(payload.message || 'The section could not be saved.');
                }

                const preview = card?.querySelector('[data-landing-section-preview]');
                const updated = card?.querySelector('[data-landing-section-updated]');
                const status = card?.querySelector('[data-landing-section-status]');
                const thumbnail = card?.querySelector('[data-landing-section-thumbnail]');
                const thumbnailEmpty = card?.querySelector('[data-landing-section-thumbnail-empty]');
                const thumbnailOverlay = card?.querySelector('[data-landing-section-thumbnail-overlay]');

                if (preview && payload.section?.preview) {
                    preview.textContent = payload.section.preview;
                }

                if (updated && payload.section?.updated_label) {
                    updated.textContent = payload.section.updated_label;
                }

                if (status && payload.section?.status?.label) {
                    status.textContent = payload.section.status.label;
                    status.classList.toggle('border-emerald-200', payload.section.status.tone === 'success');
                    status.classList.toggle('bg-emerald-50', payload.section.status.tone === 'success');
                    status.classList.toggle('text-emerald-700', payload.section.status.tone === 'success');
                    status.classList.toggle('border-amber-200', payload.section.status.tone !== 'success');
                    status.classList.toggle('bg-amber-50', payload.section.status.tone !== 'success');
                    status.classList.toggle('text-amber-700', payload.section.status.tone !== 'success');
                }

                if (thumbnail && payload.section?.thumbnail) {
                    thumbnail.src = payload.section.thumbnail;
                    thumbnail.classList.remove('hidden');
                    thumbnailEmpty?.classList.add('hidden');
                    thumbnailOverlay?.classList.remove('hidden');
                } else if (thumbnail && payload.section?.thumbnail === '') {
                    thumbnail.removeAttribute('src');
                    thumbnail.classList.add('hidden');
                    thumbnailEmpty?.classList.remove('hidden');
                    thumbnailOverlay?.classList.add('hidden');
                }

                syncFormDefaults(form);
                form.dataset.dirty = '0';
                clearErrors(form);
                closeAccessibleModal(modal);
                showToast(payload.message || `${form.dataset.sectionTitle || 'Section'} updated successfully.`);
            } catch (error) {
                showToast(error?.message || 'The section could not be saved.', 'error');
            } finally {
                delete form.dataset.saving;
                if (form.dataset.dirty === '1') {
                    submit?.removeAttribute('disabled');
                } else {
                    submit?.setAttribute('disabled', 'disabled');
                }
                card?.classList.remove('opacity-60', 'pointer-events-none');
                window.VillaLoader?.complete();
            }
        });
    });

    window.addEventListener('beforeunload', (event) => {
        if (!forms.some((form) => form.dataset.dirty === '1' && form.dataset.saving !== '1')) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });
})();

// Global image fallback: replace broken images with a sensible default
(() => {
    const FALLBACK_IMAGE = `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1400 900" role="img" aria-label="Villa Estella room image placeholder">
            <defs>
                <linearGradient id="bg" x1="0" x2="1" y1="0" y2="1">
                    <stop offset="0" stop-color="#2f241d"/>
                    <stop offset="0.48" stop-color="#B6424F"/>
                    <stop offset="1" stop-color="#B57D59"/>
                </linearGradient>
                <linearGradient id="light" x1="0" x2="1">
                    <stop offset="0" stop-color="#fff7ed" stop-opacity="0.92"/>
                    <stop offset="1" stop-color="#ffffff" stop-opacity="0.48"/>
                </linearGradient>
            </defs>
            <rect width="1400" height="900" fill="url(#bg)"/>
            <rect x="140" y="180" width="1120" height="520" rx="38" fill="#ffffff" opacity="0.12"/>
            <rect x="210" y="260" width="430" height="300" rx="28" fill="url(#light)" opacity="0.72"/>
            <rect x="700" y="280" width="420" height="58" rx="29" fill="#ffffff" opacity="0.72"/>
            <rect x="700" y="370" width="300" height="34" rx="17" fill="#ffffff" opacity="0.42"/>
            <rect x="700" y="430" width="360" height="34" rx="17" fill="#ffffff" opacity="0.32"/>
            <text x="700" y="660" text-anchor="middle" fill="#fffaf0" font-family="Arial, sans-serif" font-size="52" font-weight="700">Villa Estella</text>
        </svg>
    `)}`;

    const applyImageFallback = (el) => {
        if (!(el instanceof HTMLImageElement)) return;
        if (el.dataset.fallbackApplied === '1') return;

        el.dataset.fallbackApplied = '1';
        el.classList.add('bg-stone-200');
        try { el.src = FALLBACK_IMAGE; } catch (e) { /* ignore */ }
    };

    // Use capture phase so we catch load errors from delegated images.
    document.addEventListener('error', (ev) => {
        applyImageFallback(ev.target);
    }, true);

    window.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('img').forEach((image) => {
            if (image.complete && image.naturalWidth === 0) {
                applyImageFallback(image);
            }
        });
    });
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

// Mobile featured carousel JS removed — using CSS scroll-snap only for indicators.

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

// Dynamic notifications and realtime updates with polling fallback.
(() => {
    const notifBtn = document.getElementById('notif-btn');
    const notifBadge = document.getElementById('notif-badge');
    const notifList = document.getElementById('notif-list');
    const notifFooter = document.getElementById('notif-footer');
    const notifMarkAll = document.getElementById('notif-mark-all');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const realtime = window.VillaRealtime || {};
    const transport = window.fetchWithoutLoader || originalFetch;

    let notificationPollTimer = null;
    let echoConnected = false;

    const updateNotificationsUI = (data) => {
        const count = data.unread_count || 0;

        if (notifBadge) {
            notifBadge.textContent = count;
            notifBadge.classList.toggle('hidden', count <= 0);
        }

        if (notifFooter) {
            notifFooter.textContent = count === 0 ? 'You\u2019re all caught up' : `${count} unread notification(s)`;
        }

        if (notifMarkAll) {
            notifMarkAll.classList.toggle('hidden', count <= 0);
        }

        if (!notifList) return;

        if (!data.notifications || data.notifications.length === 0) {
            notifList.innerHTML = '<div class="px-4 py-6 text-sm text-stone-500 text-center">No new notifications</div>';
            return;
        }

        notifList.innerHTML = '';
        data.notifications.forEach(n => {
            const isRead = n.read_at !== null;
            const div = document.createElement('div');
            div.className = `flex items-start justify-between gap-3 px-4 py-3 text-sm hover:bg-stone-50 transition ${isRead ? 'opacity-70' : 'bg-brand-primary/5 font-semibold'}`;
            div.setAttribute('data-notif-id', n.id);

            const infoDiv = document.createElement('div');
            infoDiv.className = 'flex-1 min-w-0';

            const p = document.createElement('p');
            p.className = 'text-stone-900 leading-snug';

            const link = document.createElement('a');
            link.href = n.data?.action_url || '#';
            link.textContent = n.data?.message || '';
            link.className = 'hover:underline block';
            p.appendChild(link);
            infoDiv.appendChild(p);

            const timeP = document.createElement('p');
            timeP.className = 'text-[10px] text-stone-400 mt-1 uppercase tracking-wider font-semibold';
            timeP.textContent = n.created_at_human;
            infoDiv.appendChild(timeP);

            div.appendChild(infoDiv);

            if (!isRead) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'notif-read-btn text-xs text-stone-400 hover:text-brand-primary font-bold px-1';
                btn.title = 'Mark as read';
                btn.textContent = '\u2713';
                btn.setAttribute('data-id', n.id);
                div.appendChild(btn);
            }

            notifList.appendChild(div);
        });
    };

    const pollNotifications = async () => {
        if (!notifBtn) return;

        try {
            const res = await transport('/notifications/unread', {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (res.ok) {
                updateNotificationsUI(await res.json());
            }
        } catch (e) {
            console.error('Failed to poll notifications:', e);
        }
    };

    const scheduleNotificationPolling = () => {
        if (!notifBtn) return;

        window.clearInterval(notificationPollTimer);
        notificationPollTimer = window.setInterval(pollNotifications, echoConnected ? 90000 : 15000);
    };

    const refreshRealtimeFragment = async (name) => {
        const current = document.querySelector(`[data-realtime-fragment="${name}"]`);
        if (!current) return;

        try {
            const response = await transport(window.location.href, {
                headers: {
                    Accept: 'text/html',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) return;

            const html = await response.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const fresh = doc.querySelector(`[data-realtime-fragment="${name}"]`);

            if (fresh) {
                current.replaceWith(fresh);
            }
        } catch (error) {
            console.error(`Failed to refresh realtime fragment: ${name}`, error);
        }
    };

    const handleRealtimeChange = () => {
        pollNotifications();
        refreshRealtimeFragment('guest-dashboard');
        refreshRealtimeFragment('admin-dashboard');
        refreshRealtimeFragment('admin-bookings');
    };

    const bindRealtime = () => {
        if (!realtime.userId || !import.meta.env.VITE_PUSHER_APP_KEY) {
            scheduleNotificationPolling();
            pollNotifications();
            return;
        }

        try {
            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'pusher',
                key: import.meta.env.VITE_PUSHER_APP_KEY,
                cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
                forceTLS: true,
                authEndpoint: '/broadcasting/auth',
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                    },
                },
            });

            const connection = window.Echo.connector?.pusher?.connection;
            connection?.bind('connected', () => {
                echoConnected = true;
                scheduleNotificationPolling();
                pollNotifications();
            });
            connection?.bind('disconnected', () => {
                echoConnected = false;
                scheduleNotificationPolling();
            });
            connection?.bind('error', () => {
                echoConnected = false;
                scheduleNotificationPolling();
            });

            const userChannel = window.Echo.private(`users.${realtime.userId}`);
            [
                '.notification.created',
                '.booking.created',
                '.booking.updated',
                '.booking.cancelled',
                '.booking.status.changed',
                '.booking.confirmed',
                '.payment.pending',
                '.payment.verified',
                '.payment.failed',
                '.payment.expired',
                '.payment.status.updated',
            ].forEach(eventName => userChannel.listen(eventName, handleRealtimeChange));

            if (realtime.isAdmin) {
                const adminChannel = window.Echo.private('admins');
                [
                    '.notification.created',
                    '.booking.created',
                    '.booking.updated',
                    '.booking.cancelled',
                    '.booking.status.changed',
                    '.booking.confirmed',
                    '.payment.pending',
                    '.payment.verified',
                    '.payment.failed',
                    '.payment.expired',
                    '.payment.status.updated',
                ].forEach(eventName => adminChannel.listen(eventName, handleRealtimeChange));
            }
        } catch (error) {
            console.error('Realtime setup failed; polling fallback remains active.', error);
        }

        scheduleNotificationPolling();
        pollNotifications();
    };

    window.VillaNotifications = {
        refresh: pollNotifications,
    };

    if (notifList) {
        notifList.addEventListener('click', async (e) => {
            const btn = e.target.closest('.notif-read-btn');
            if (!btn) return;
            e.stopPropagation();

            const id = btn.getAttribute('data-id');
            btn.setAttribute('disabled', 'disabled');
            btn.textContent = '...';

            try {
                const res = await transport(`/notifications/${id}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    await pollNotifications();
                }
            } catch (err) {
                console.error('Failed to mark notification as read:', err);
                btn.removeAttribute('disabled');
                btn.textContent = '\u2713';
            }
        });
    }

    if (notifMarkAll) {
        notifMarkAll.addEventListener('click', async (e) => {
            e.stopPropagation();
            notifMarkAll.setAttribute('disabled', 'disabled');
            notifMarkAll.textContent = 'Marking...';

            try {
                const res = await transport('/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (res.ok) {
                    await pollNotifications();
                }
            } catch (err) {
                console.error('Failed to mark all notifications as read:', err);
            } finally {
                notifMarkAll.removeAttribute('disabled');
                notifMarkAll.textContent = 'Mark all as read';
            }
        });
    }

    bindRealtime();
})();
